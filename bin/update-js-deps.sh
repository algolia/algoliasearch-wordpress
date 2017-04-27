#!/usr/bin/env bash
set -e

# Update JS dependencies.
yarn install && yarn upgrade

cp node_modules/algoliasearch/dist/algoliasearch.jquery.js assets/js/algoliasearch/
cp node_modules/algoliasearch/dist/algoliasearch.jquery.min.js assets/js/algoliasearch/

cp node_modules/autocomplete.js/dist/autocomplete.js assets/js/autocomplete.js/
cp node_modules/autocomplete.js/dist/autocomplete.min.js assets/js/autocomplete.js/

cp node_modules/instantsearch.js/dist/instantsearch-preact.js assets/js/instantsearch.js/
cp node_modules/instantsearch.js/dist/instantsearch-preact.min.js assets/js/instantsearch.js/

rm -rf node_modules
