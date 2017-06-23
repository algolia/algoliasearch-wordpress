#!/bin/bash

set -eu

readonly PACKAGE_VERSION=$(< package.json grep version \
  | head -1 \
  | awk -F: '{ print $2 }' \
  | sed 's/[",]//g' \
  | tr -d '[:space:]')
  
git tag "v$PACKAGE_VERSION"
git push --tags

./bin/release.sh

echo "Pushed package to wordpress.org, and also pushed 'v$PACKAGE_VERSION' tag to git repository."
