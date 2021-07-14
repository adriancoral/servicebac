#!/usr/bin/env node
import 'source-map-support/register';
import * as cdk from '@aws-cdk/core';
import { Pipeline } from '../lib/deploy-stack';
import { Infra } from '../lib/infra-stack';

const app = new cdk.App();

const infraStack = new Infra(app, 'services-stack', {
  containerImage: 'latest',
  clusterInstanceType: 't3a.nano',
  certificateArn: 'arn:aws:acm:us-east-1:352725560891:certificate/0f32f657-dc1e-4e6f-84ae-167d52c07b96',
  hostzone: {
    id: 'Z04944581I6URJMSLZPD2',
    name: 'bookacorner.io'
  },
  rdsIdentifier: 'services',
  rdsSecretArn: 'arn:aws:secretsmanager:us-east-1:352725560891:secret:prod/services/DATABASE_CREDENTIALS-ERoRW6',
  vpc: 'vpc-022e061111161ce04',
  env: {
    account: '352725560891',
    region: 'us-east-1'
  },
});


new Pipeline(app, 'services-pipeline', {
  github: {
    owner: 'Bookacorner',
    repository: 'bac-services-api',
    branch: 'master'
  },
  infraStack
});
