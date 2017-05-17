#!/usr/bin/env bash

set -eu

generate_temporary_directory_name () {
  echo "$(pwd)/tmp/$(date +%Y-%m-%d)"
}

readonly TMP_DIR=$(generate_temporary_directory_name)
readonly SYNC_SCRIPT_TAG="v0.3.0"
readonly PLUGIN_SLUG="search-by-algolia-instant-relevant-results"
readonly PLUGIN_GITHUB_REPOSITORY="https://github.com/algolia/algoliasearch-wordpress"
readonly SVN_USER="algolia"
readonly ASSETS_DIRECTORY="assets"

create_temporary_directory () {
  mkdir -p "$TMP_DIR"
}

download_sync_script () {
  git clone git@github.com:rayrutjes/wp-plugin-git-svn-sync.git "$TMP_DIR"
  cd "$TMP_DIR"
  git checkout tags/$SYNC_SCRIPT_TAG
}

sync_plugin_with_wordpress_org () {
  cd "$TMP_DIR"
  ./sync.sh \
    --plugin-name=$PLUGIN_SLUG \
    --git-repo=$PLUGIN_GITHUB_REPOSITORY \
    --svn-user=$SVN_USER \
    --assets-dir=$ASSETS_DIRECTORY
}

create_temporary_directory
download_sync_script
sync_plugin_with_wordpress_org

echo "Plugin Git repository is now in sync with WordPress.org."
exit 0
