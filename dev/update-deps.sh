#!/usr/bin/env bash
set -e

# Update PHP dependencies.
docker run --rm -v $(pwd):/app composer/composer update

# Update JS dependencies.
docker run --rm -v $(pwd):/app node /bin/bash -c "cd /app; npm update"
cp node_modules/algoliasearch/dist/algoliasearch.jquery.min.js wordpress/wp-content/plugins/algolia/public/js/
cp node_modules/autocomplete.js/dist/autocomplete.jquery.min.js wordpress/wp-content/plugins/algolia/public/js/
cp node_modules/instantsearch.js/dist/instantsearch.min.js wordpress/wp-content/plugins/algolia/public/js/
cp node_modules/tether/dist/js/tether.min.js wordpress/wp-content/plugins/algolia/public/js/
