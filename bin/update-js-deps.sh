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

# Update JS dependencies.
yarn install && yarn upgrade

cp node_modules/algoliasearch/dist/algoliasearch.jquery.js assets/js/algoliasearch/
cp node_modules/algoliasearch/dist/algoliasearch.jquery.min.js assets/js/algoliasearch/

cp node_modules/autocomplete.js/dist/autocomplete.js assets/js/autocomplete.js/
cp node_modules/autocomplete.js/dist/autocomplete.min.js assets/js/autocomplete.js/

cp node_modules/instantsearch.js/dist/instantsearch-preact.js assets/js/instantsearch.js/
cp node_modules/instantsearch.js/dist/instantsearch-preact.min.js assets/js/instantsearch.js/

rm -rf node_modules
