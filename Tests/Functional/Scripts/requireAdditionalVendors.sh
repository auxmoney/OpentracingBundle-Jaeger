#!/bin/bash
shopt -s extglob

cd build/testproject/
rm -fr vendor/auxmoney/opentracing-bundle-jaeger/*
cp -r ../../!(build|vendor) vendor/auxmoney/opentracing-bundle-jaeger
composer dump-autoload
cd ../../
