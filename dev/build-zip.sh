#!/usr/bin/env bash
set -eu

rm -rf ./dist
mkdir ./dist

cp -R ./wordpress/wp-content/plugins/algolia ./dist/algolia

cd ./dist
zip -r algolia.zip algolia
rm -rf ./algolia
cd ..
