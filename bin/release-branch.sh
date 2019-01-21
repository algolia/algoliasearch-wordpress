#!/bin/bash

set -eu

readonly CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
if [ "$CURRENT_BRANCH" != master ]; then
  echo "You must be on 'master' branch to release, aborting..."
  exit 1
fi

if ! git diff-index --quiet HEAD --; then
  echo "Working tree is not clean, aborting..."
  exit 1
fi

rm -rf tmp

yarn

yarn run changelog:unreleased

# Only update the package.json version
# We need to update changelog before tagging
# And publishing.
yarn version --no-git-tag-version

if ! yarn run changelog; then
  echo "Failed to update changelog, aborting..."
  exit 1
fi

readonly PACKAGE_VERSION=$(< package.json grep version \
  | head -1 \
  | awk -F: '{ print $2 }' \
  | gsed 's/[",]//g' \
  | tr -d '[:space:]')

# Here we need to update versions in files
readonly SEMVER_REGEX=[[:digit:]]*\.[[:digit:]]*\.[[:digit:]]*
gsed -i "s/\(Version:           \)$SEMVER_REGEX/\1$PACKAGE_VERSION/g" ./algolia.php
gsed -i "s/\('ALGOLIA_VERSION', '\)$SEMVER_REGEX/\1$PACKAGE_VERSION/g" ./algolia.php
gsed -i "s/\(Stable tag: \)$SEMVER_REGEX/\1$PACKAGE_VERSION/" ./readme.txt
gsed -i "s/\(version: '\)$SEMVER_REGEX/\1$PACKAGE_VERSION/" ./docs/index.js

if ! grunt; then
  echo "Failed to build dist files, aborting..."
  exit 1
fi

git checkout -b "chore/release-$PACKAGE_VERSION"

# Gives user a chance to review and eventually abort.
git add --patch

git commit --message="chore(release): v${PACKAGE_VERSION}"

git push origin HEAD

echo "Your release branch 'chore/release-$PACKAGE_VERSION' is now ready for review..."

git open > /dev/null 2>&1 || echo "Install https://github.com/paulirish/git-open so that next times it opens the browser at the repository URL."
