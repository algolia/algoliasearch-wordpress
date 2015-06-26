#!/usr/bin/env bash
set -e # exit when error
set -x # debug messages

npm install algoliasearch-extensions-bundle@latest --save --save-exact
cp node_modules/algoliasearch-extensions-bundle/bundle.min.js ../../lib/bundle.min.js
cp node_modules/algoliasearch-extensions-bundle/bundle.css ../../themes/default/bundle.css
cp node_modules/algoliasearch-extensions-bundle/bundle.css ../../themes/woo/bundle.css
