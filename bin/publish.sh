#!/bin/bash

set -eu

git tag "v$PACKAGE_VERSION"
git push --tags

./bin/release.sh

echo "Pushed package to wordpress.org, and also pushed 'v$PACKAGE_VERSION' tag to git repository."
