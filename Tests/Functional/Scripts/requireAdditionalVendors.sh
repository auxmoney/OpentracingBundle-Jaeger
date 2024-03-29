#!/bin/bash
shopt -s extglob

cd build/testproject/
VENDOR_VERSION=""
CURRENT_REF=${GITHUB_HEAD_REF:-$GITHUB_REF}
CURRENT_BRANCH=${CURRENT_REF#refs/heads/}
if [[ $CURRENT_BRANCH -ne "master" ]]; then
    composer config minimum-stability dev
    VENDOR_VERSION=":dev-${CURRENT_BRANCH}"
fi
composer require auxmoney/opentracing-bundle-jaeger${VENDOR_VERSION}
composer dump-autoload
cd ../../
