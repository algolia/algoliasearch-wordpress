#!/usr/bin/env bash
set -e

rm -Rf ./dev/tmp
mkdir ./dev/tmp
cp -R ./wordpress/wp-content/plugins/algolia ./dev/tmp/algolia
cp -R ./wordpress/wp-content/plugins/visitsmatters ./dev/tmp/visitsmatters
rm -Rf ./wordpress
mkdir -p ./wordpress/wp-content
mv ./dev/tmp ./wordpress/wp-content/plugins

docker-compose stop
docker-compose rm --all --force
docker-compose build
docker-compose up
