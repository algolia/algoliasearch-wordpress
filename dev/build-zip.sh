#!/usr/bin/env bash
set -eu

rm -rf ./dist
mkdir ./dist
zip -r dist/algolia.zip . \
	--exclude=.git*  \
	--exclude=dev/*  \
	--exclude=docs/*  \
	--exclude=dist/*  \
	--exclude=.idea/*  \
	--exclude=.DS_Store


