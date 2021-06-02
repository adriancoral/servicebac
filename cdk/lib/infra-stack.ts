import * as CDK from '@aws-cdk/core'
import * as CodeBuild from '@aws-cdk/aws-codebuild'
import * as S3 from '@aws-cdk/aws-s3'
import * as Pipelines from '@aws-cdk/pipelines';
import * as S3Deployment from '@aws-cdk/aws-s3-deployment'
import * as CodePipeline from '@aws-cdk/aws-codepipeline'
import * as CodePipelineAction from '@aws-cdk/aws-codepipeline-actions'
import * as Cloudfront from '@aws-cdk/aws-cloudfront';
import * as Logs from '@aws-cdk/aws-logs';
import * as ACM from '@aws-cdk/aws-certificatemanager';
import * as Route53 from '@aws-cdk/aws-route53';
import * as IAM from '@aws-cdk/aws-iam';
import * as ECR from '@aws-cdk/aws-ecr';
import * as EC2 from '@aws-cdk/aws-ec2';
import * as ECS from '@aws-cdk/aws-ecs';
import * as ECSPatterns from '@aws-cdk/aws-ecs-patterns';
import * as Cloudwatch from '@aws-cdk/aws-cloudwatch';
import * as ELBV2 from '@aws-cdk/aws-elasticloadbalancingv2';
import * as AutoScaling from '@aws-cdk/aws-autoscaling';

import { Duration, PhysicalName } from '@aws-cdk/core';

export interface InfraStackProps extends CDK.StackProps {
  containerImage: string,
  clusterInstanceType: string,
  certificateArn: string,
  hostzone: {
    id: string,
    name: string
  },
  vpc?: string,
}

interface SecretAndEnvsProps {
  secrets?: {
    [key: string]: ECS.Secret
  },
  environment: {
    [key: string]: string
  }
}

export class Infra extends CDK.Stack {

  vpc: EC2.Vpc
  cluster: ECS.Cluster
  securityGroup: EC2.SecurityGroup
  service: ECS.FargateService
  mainTaskDefinition: ECS.TaskDefinition

  constructor(scope: CDK.App, id: string, props: InfraStackProps) {
    super(scope, id, props);

    const domainName = 'bac-services.bookacorner.io';

    const vpc = props.vpc ? (
      EC2.Vpc.fromLookup(this, `prev-vpc`, {
        vpcId: props.vpc,
        isDefault: false,
        subnetGroupNameTag: `prev-subnetgroup`
      })
    ) : (
      new EC2.Vpc(this, `newvpc`, {
        maxAzs: 2,
      })
    );

    const appRepository = ECR.Repository.fromRepositoryName(this, 'RepositoryApp', 'bac/bac-services');

    const appImageContainer = ECS.ContainerImage.fromEcrRepository(appRepository, props.containerImage);

    const myHostedZone = Route53.HostedZone.fromHostedZoneAttributes(this, 'prev-hosted-zone', {
      hostedZoneId: props.hostzone.id,
      zoneName: props.hostzone.name
    });

    const cluster = new ECS.Cluster(this, 'Cluster', { vpc, clusterName: PhysicalName.GENERATE_IF_NEEDED });

    const autoScalingGroup = new AutoScaling.AutoScalingGroup(this, 'ASG', {
      vpc,
      instanceType: new EC2.InstanceType(props.clusterInstanceType),
      machineImage: ECS.EcsOptimizedImage.amazonLinux2(),
      vpcSubnets: { subnetType: EC2.SubnetType.PUBLIC }
    });

    const capacityProvider = new ECS.AsgCapacityProvider(this, 'AsgCapacityProvider', {
      autoScalingGroup,
    });
    cluster.addAsgCapacityProvider(capacityProvider)
    // exporting cluster
    this.cluster = cluster;

    const mySecurityGroup = new EC2.SecurityGroup(this, 'SecurityGruop-services', {
      vpc,
      description: 'Allow ssh access to ec2. access via AWS SSM',
      allowAllOutbound: true,
    });

    // exporting security group
    this.securityGroup = mySecurityGroup;


    /** ROLEs */
    const role = new IAM.Role(this, 'RoleBig', {
      assumedBy: new IAM.ServicePrincipal('ses.amazonaws.com'),
    });

    const roleS3 = new IAM.Role(this, 'RoleS3', {
      assumedBy: new IAM.ServicePrincipal('s3.amazonaws.com'),
    });

    /** Policys */
    const S3Policy = new IAM.PolicyStatement({
      effect: IAM.Effect.ALLOW,
      actions: ['s3:PutObject', 's3:DeleteObject'],
      resources: ['*'],
    })

    const SESPolicy = new IAM.PolicyStatement({
      effect: IAM.Effect.ALLOW,
      actions: ['ses:SendEmail', 'ses:SendRawEmail'],
      resources: ['*'],
    })

    const policySes = new IAM.Policy(this, 'policySES', {
      statements: [SESPolicy],
      roles: [role]
    })

    const policyS3 = new IAM.Policy(this, 'policyS3', {
      statements: [S3Policy],
      roles: [roleS3]
    })

    // check in https://docs.aws.amazon.com/AmazonECS/latest/developerguide/application_architecture.html
    // to separate tasks in production
    const taskDefinition = new ECS.FargateTaskDefinition(this, 'ServicesTD', {
      memoryLimitMiB: 8192,
      cpu: 4096
    });

    /** Permissions */
    taskDefinition.taskRole.attachInlinePolicy(policySes);
    taskDefinition.taskRole.attachInlinePolicy(policyS3);

    const secretsAndEnvs : SecretAndEnvsProps = {
      environment: {
        'DB_CONNECTION': 'sqlite',
        /*'DB_HOST': mySQLinstance.dbInstanceEndpointAddress,
        'DB_PORT': '3306',
        'DB_DATABASE': databaseName,
        'DB_USERNAME': databaseUser,
        'DB_PASSWORD': ''
        */
        'APP_ENV': 'production',
        'COMPOSE_HTTP_TIMEOUT': '180',
        // TODO: Importante.
        'MAIL_DRIVER': 'ses',
        'MAIL_MAILER': 'ses',
        'MAIL_PORT': '2525',
        'MAIL_FROM_ADDRESS': '',
        'MAIL_FROM_NAME': '',
        'LOG_CHANNEL': 'stack',
        'ACCESS_TOKEN_EXP_MINS': '120',
        'CACHE_DRIVER': 'file',
        'QUEUE_CONNECTION': 'database',
        'SESSION_DRIVER': 'file',
        'SESSION_LIFETIME': '120',
      },
    }

    taskDefinition
      .addContainer('app', {
        logging: new ECS.AwsLogDriver({
          streamPrefix: 'bac-app',
          logRetention: Logs.RetentionDays.FIVE_DAYS
        }),
        memoryLimitMiB: 1024,
        cpu: 2048,
        ...secretsAndEnvs,
        image: appImageContainer,
        healthCheck: {//#TECHDEBT health check
          command: ['CMD-SHELL', `curl -f https://${domainName}/api/healthcheck || exit 1`],
          interval: Duration.seconds(30),
          retries: 2,
          startPeriod: Duration.minutes(2),
          timeout: Duration.seconds(10)
        }
      })
      .addPortMappings({
        containerPort: 80,
        protocol: ECS.Protocol.TCP
      })

    /** Certificate **/
    const certificate = ACM.Certificate
      .fromCertificateArn(this, 'CertificatePrevCreated', props.certificateArn);

    const alb = new ECSPatterns.ApplicationLoadBalancedFargateService(this, 'LBServices', {
      taskDefinition,
      cluster,
      //certificate,
      securityGroups: [mySecurityGroup],
      //desiredCount: 2,
      serviceName: 'BAC-Services',
      assignPublicIp: true,
      publicLoadBalancer: true,
      protocol: ELBV2.ApplicationProtocol.HTTPS,
      redirectHTTP: true,
      domainZone: myHostedZone,
      domainName,
      //#TECHDEBT base / return 200
      healthCheckGracePeriod: Duration.hours(15)
    })

    this.service = alb.service;

  }
}
