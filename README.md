
**Looking for the user documentation? [head over here](https://community.algolia.com/wordpress)!**

## Development

### Docs

The user documentation is generated with MetalSmith. To build the doc simply run `make build` from inside the `./docs` directory.
This will also make the documentation available on `localhost:8080`.

To contribute, simply edit the markdown formatted files located in `docs/src/*.md`

### Release instructions

1. Update [CHANGELOG.md](https://github.com/algolia/algoliasearch-wordpress/blob/master/CHANGELOG.md)
2. Update version number in [package.json](https://github.com/algolia/algoliasearch-wordpress/blob/master/package.json)
3. Update version number in [docs/index.js](https://github.com/algolia/algoliasearch-wordpress/blob/master/docs/index.js#L25)
4. Update version number (x2) in [algolia.php](https://github.com/algolia/algoliasearch-wordpress/blob/master/algolia.php)
5. Create a PR and merge it once it has been re-checked
6. Create a release on GitHub
7. Run `$ bin/build-zip.sh` and attach the `dist/algolia.zip` to the release
8. Publish the docs `cd docs && make release`
