#!/usr/bin/env bash
set -e

# Update PHP dependencies.
# docker run --rm -v $(pwd):/app composer/composer update
cd includes
rm -rf libraries
mkdir libraries
cd libraries

git clone --depth 1 git@github.com:algolia/algoliasearch-client-php.git && rm -rf algoliasearch-client-php/.git
git clone --depth 1 git@github.com:algolia/php-dom-parser.git && rm -rf php-dom-parser/.git
git clone --depth 1 git@github.com:techcrunch/wp-async-task.git && rm -rf wp-async-task/.git

cd ../..
