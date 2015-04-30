TEMPDIR := $(shell mktemp -d -t XXXXX)
PWD := $(shell pwd)

all:
	@cp -R ./ $(TEMPDIR)/algoliasearch-wordpress
	@cd $(TEMPDIR)/algoliasearch-wordpress && rm -rf .git && cd .. && zip -qr algoliasearch-wordpress algoliasearch-wordpress && cp algoliasearch-wordpress.zip $(PWD) && rm -rf $(TEMPDIR)
	@echo "DONE: $(PWD)/algoliasearch-wordpress.zip"
