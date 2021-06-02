import * as CDK from '@aws-cdk/core'
import * as CodeBuild from '@aws-cdk/aws-codebuild'
import * as CodePipeline from '@aws-cdk/aws-codepipeline'
import * as CodePipelineAction from '@aws-cdk/aws-codepipeline-actions'
import * as IAM from '@aws-cdk/aws-iam';
import * as ECS from '@aws-cdk/aws-ecs';
import { BuildEnvironmentVariableType } from '@aws-cdk/aws-codebuild'
import { Infra } from './infra-stack';

export interface DeployStackProps extends CDK.StackProps {
  github: {
    owner: string
    repository: string,
    branch: string
  },
  infraStack: Infra
}

export class Pipeline extends CDK.Stack {
  constructor(scope: CDK.App, id: string, props: DeployStackProps) {
    super(scope, id, props)
    
    const imagesDetailArtifact = new CodePipeline.Artifact('ImagesDetail');
    
    // AWS CodePipeline pipeline
    const pipeline = new CodePipeline.Pipeline(this, 'PipelineBacServices', {
      pipelineName: 'BacServices',
      restartExecutionOnUpdate: true,
    })

    const role = IAM.Role.fromRoleArn(this, 'Role', 'arn:aws:iam::352725560891:role/EC2ContainerRegistryRole', {
      // Set 'mutable' to 'false' to use the role as-is and prevent adding new
      // policies to it. The default is 'true', which means the role may be
      // modified as part of the deployment.
      mutable: false,
    });
    

    const outputSources = new CodePipeline.Artifact();
    /**
     * Env variables dev
     */
    // AWS CodePipeline stage to clone sources from GitHub repository
    pipeline.addStage({
      stageName: 'Source',
      actions: [
        new CodePipelineAction.GitHubSourceAction({
          actionName: 'Checkout',
          owner: props.github.owner,
          repo: props.github.repository,
          branch: props.github.branch,
          oauthToken: CDK.SecretValue.secretsManager('ENV/GITHUB_TOKEN/FRONTEND', {
            jsonField: 'GITHUB_TOKEN_FRONTEND'
          }),
          output: outputSources,
          trigger: CodePipelineAction.GitHubTrigger.WEBHOOK,
        }),
        
      ],
    })

    pipeline.addStage({
      stageName: 'BuildImage',
      actions: [
        new CodePipelineAction.CodeBuildAction({
          actionName: 'DockerBuild',
          input: outputSources,
          outputs: [imagesDetailArtifact],
          project: new CodeBuild.PipelineProject(this, 'DockerBuild', {
            role,
            environment: {
              buildImage: CodeBuild.LinuxBuildImage.STANDARD_4_0,
              privileged: true
            },
            environmentVariables: {
              BACKEND_TAG: { type: BuildEnvironmentVariableType.PLAINTEXT, value: props.github.branch }
            },
            buildSpec: CodeBuild.BuildSpec.fromSourceFilename('./cdk/buildspec.yml')
          })
        })
      ],
    });

    pipeline.addStage({
      stageName: 'Deploy',
      actions: [
        new CodePipelineAction.EcsDeployAction({
          actionName: 'DeployApiService',
          input: imagesDetailArtifact,
          service: ECS.FargateService.fromFargateServiceAttributes(this, 'MainService', {
            serviceName: props.infraStack.service.serviceName,
            cluster: ECS.Cluster.fromClusterAttributes(this, 'ApiServicesCluster', {
              clusterName: props.infraStack.service.cluster.clusterName,
              clusterArn: props.infraStack.service.cluster.clusterArn,
              vpc: props.infraStack.vpc,
              securityGroups: [props.infraStack.securityGroup]
            })
          })
        })
      ]
    })

    
  }
}

