# Search by Algolia plugin for WordPress

[![PHP Dependency Status](https://www.versioneye.com/php/algolia:algoliasearch-wordpress/dev-master/badge?style=flat-square)](https://www.versioneye.com/php/algolia:algoliasearch-wordpress/dev-master) [![JS dependencies Status](https://david-dm.org/algolia/algoliasearch-wordpress/status.svg)](https://david-dm.org/algolia/algoliasearch-wordpress) [![JS devDependencies Status](https://david-dm.org/algolia/algoliasearch-wordpress/dev-status.svg)](https://david-dm.org/algolia/algoliasearch-wordpress?type=dev)

## User documentation & guides

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
5. Update changelog in [README.txt](https://github.com/algolia/algoliasearch-wordpress/blob/master/README.txt)
6. Create a PR and merge it once it has been re-checked
7. Create a release on GitHub
8. Publish the docs `cd docs && make release`
9. Switch to `svn` branch and run `bin/publish.sh`
