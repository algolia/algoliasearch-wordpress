
**Looking for the user documentation? [head over here](https://community.algolia.com/wordpress)!**

## Development

### WordPress plugin

Requirements:
- docker (native, no boot2docker)
- docker-compose

Launch the WordPress development environment.

**Be sure you have nothing running on port 80, and also that you are using the native version of Docker.**

```
$ dev/restart.sh
```

If everything went OK, you should be able to access the WordPress instance on `http://localhost` and the admin panel on `http://localhost/wp-admin/`.

If you get a lot of errors like the following, simply restart docker:
```
Warning: copy(/var/www/html//wp-content/themes/twentyfourteen/category.php): failed to open stream: No such file or directory in phar:///bin/wp/php/commands/core.php on line 220
```

Admin user credentials:
- login: admin
- password: admin


In the admin panel, you can now go to `plugins` and enable the `Algolia Search` plugin.

Note that the whole WordPress dir is mounted as a volume so that you can edit anything on the fly in `./wordpress`.

The `.gitignore` ensures that only the `./wordpress/wp-content/plugins/algolia` directory gets indexed.

### Docs

The user documentation is generated with MetalSmith. To build the doc simply run `make build` from inside the `./docs` directory.
This will also make the documentation available on `localhost:8080`.

To contribute, simply edit the markdown formatted files located in `docs/src/*.md`

### Release instructions

1. Update [CHANGELOG.md](https://github.com/algolia/algoliasearch-wordpress/blob/master/CHANGELOG.md)
2. Update version number in [package.json](https://github.com/algolia/algoliasearch-wordpress/blob/master/package.json)
3. Update version number in [docs/index.js](https://github.com/algolia/algoliasearch-wordpress/blob/master/docs/index.js#L25)
4. Update version number (x2) in [wordpress/wp-content/plugins/algolia/algolia.php](https://github.com/algolia/algoliasearch-wordpress/blob/master/wordpress/wp-content/plugins/algolia/algolia.php)
5. Create a PR and merge it once it has been re-checked
6. Create a release on GitHub
7. Run `$ dev/build-zip.sh` and attach the `dist/algolia.zip` to the release
8. Publish the docs `cd docs && make release`
