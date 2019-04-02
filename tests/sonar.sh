#!/bin/bash
/usr/local/sbin/sonar/bin/sonar-scanner \
  -Dsonar.projectKey=projectmanager \
  -Dsonar.organization=utopszkij-github \
  -Dsonar.sources=./controllers,./models,./views,./js \
  -Dsonar.host.url=https://sonarcloud.io \
  -Dsonar.login=dccc26a7cf5ee7707dfdd8945e4bc32293537f7f;
  

  