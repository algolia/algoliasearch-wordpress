#!/bin/bash

set -eu

readonly PACKAGE_VERSION=$(< package.json grep version \
  | head -1 \
  | awk -F: '{ print $2 }' \
  | sed 's/[",]//g' \
  | tr -d '[:space:]')

git tag "$PACKAGE_VERSION"
git push --tags

./bin/release.sh

echo "Pushed package to wordpress.org, and also pushed '$PACKAGE_VERSION' tag to git repository."
