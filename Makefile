version = $(shell cat lib/algolia/algoliasearch.php | sed -n 's/.*const VALUE = "\(.*\).*";/\1/p')
versionesc = $(shell echo $(version) | sed 's/\./\\./g';)
all:
	echo $(versionesc)
	cp -r ./ ../algoliasearch-wordpress
	cd ../algoliasearch-wordpress && sed -i '' 's/$(versionesc)/$(version) Wordpress/g' lib/algolia/algoliasearch.php && cd ../algoliasearch-wordpress && rm -rf .git && cd .. && zip -r algoliasearch-wordpress algoliasearch-wordpress && rm -rf algoliasearch-wordpress/ && cd algolia
