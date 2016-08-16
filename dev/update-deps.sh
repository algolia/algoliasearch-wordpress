#!/usr/bin/env bash
set -e

# Update PHP dependencies.
# docker run --rm -v $(pwd):/app composer/composer update
cd wordpress/wp-content/plugins/algolia/includes
rm -rf libraries
mkdir libraries
cd libraries

git clone --depth 1 git@github.com:algolia/algoliasearch-client-php.git && rm -rf algoliasearch-client-php/.git
git clone --depth 1 git@github.com:algolia/php-dom-parser.git && rm -rf php-dom-parser/.git
git clone --depth 1 git@github.com:techcrunch/wp-async-task.git && rm -rf wp-async-task/.git

cd ../../../../../..

# Update JS dependencies.
docker run --rm -v $(pwd):/app node /bin/bash -c "cd /app; npm update"
cp node_modules/algoliasearch/dist/algoliasearch.jquery.min.js wordpress/wp-content/plugins/algolia/public/js/
cp node_modules/autocomplete.js/dist/autocomplete.jquery.min.js wordpress/wp-content/plugins/algolia/public/js/
cp node_modules/instantsearch.js/dist/instantsearch.min.js wordpress/wp-content/plugins/algolia/public/js/
cp node_modules/tether/dist/js/tether.min.js wordpress/wp-content/plugins/algolia/public/js/
rm -rf node_modules
