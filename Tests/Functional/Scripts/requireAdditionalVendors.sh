#!/bin/bash

cd build/testproject/
composer remove auxmoney/opentracing-bundle-jaeger
composer config repositories.origin vcs https://github.com/${PR_ORIGIN}
composer require auxmoney/opentracing-bundle-jaeger:dev-${BRANCH}
cd ../../
