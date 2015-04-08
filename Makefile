version = $(shell cat lib/algolia/algoliasearch.php | sed -n 's/.*const VALUE = "\(.*\).*";/\1/p')
versionesc = $(shell echo $(version) | sed 's/\./\\./g';)
TEMPDIR := $(shell mktemp -d -t XXXXX)
PWD := $(shell pwd)

all:
	@cp -R ./ $(TEMPDIR)/algoliasearch-wordpress
	@cd $(TEMPDIR)/algoliasearch-wordpress && sed -i '' 's/$(versionesc)/$(version) Wordpress/g' lib/algolia/algoliasearch.php && rm -rf .git && cd .. && zip -qr algoliasearch-wordpress algoliasearch-wordpress && cp algoliasearch-wordpress.zip $(PWD) && rm -rf $(TEMPDIR)
	@echo "DONE: $(PWD)/algoliasearch-wordpress.zip"
