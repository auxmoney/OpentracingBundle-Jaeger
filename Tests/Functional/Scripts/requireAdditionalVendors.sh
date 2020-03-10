#!/bin/bash

cd build/testproject/
composer remove auxmoney/opentracing-bundle-jaeger
composer require auxmoney/opentracing-bundle-jaeger:dev-${BRANCH}
cd ../../
