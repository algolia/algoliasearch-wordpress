# Contributing

When contributing to this repository, please first discuss the change you wish to make via issue,
email, or any other method with the owners of this repository before making a change.

Please note we have a code of conduct, please follow it in all your interactions with the project.

## Setting up a development environment

For setting up a wordpress development environment we recommend using [Varying-Vagrant-Vagrants](https://github.com/Varying-Vagrant-Vagrants/VVV).

Follow the [installation steps](https://varyingvagrantvagrants.org/).

Once installed, run a `vagrant up` to provision the default WordPress instances on your disk.

Then clone this repository in the `plugins` folder:

```bash
$ cd ${PATH_TO_VVV}/www/wordpress-default/public_html/wp-content/plugins
$ git clone git@github.com:algolia/algoliasearch-wordpress.git
```

You should now be able to reach your Wordpress installation by visiting [local.wordpress.dev](http://local.wordpress.dev)

You can login to Wordpress admin dashboard please do that through the [login](http://local.wordpress.dev/wp-login) page using the following

**LOGIN CREDENTIALS :**
**username** : admin
**password** : password

### Need Help?

* Let us have it! Don't hesitate to open a new issue on GitHub if you run into trouble or have any tips that we need to know and please be as specific as you can be when describing your issue so that we will have the needed information to reproduce and resolve the issue. **Please keep in mind that the more information about the issue you give us, the faster we will be at resolving it.**
