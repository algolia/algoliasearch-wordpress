#!/usr/bin/env bash
set -e

# Update JS dependencies.
yarn install && yarn upgrade

cp node_modules/algoliasearch/dist/algoliasearch.jquery.js js/algoliasearch/
cp node_modules/algoliasearch/dist/algoliasearch.jquery.min.js js/algoliasearch/

cp node_modules/autocomplete.js/dist/autocomplete.js js/autocomplete.js/
cp node_modules/autocomplete.js/dist/autocomplete.min.js js/autocomplete.js/

cp node_modules/instantsearch.js/dist/instantsearch-preact.js js/instantsearch.js/
cp node_modules/instantsearch.js/dist/instantsearch-preact.min.js js/instantsearch.js/

rm -rf node_modules
