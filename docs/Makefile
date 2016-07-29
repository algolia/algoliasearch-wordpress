build: node_modules
	node index.js

node_modules: package.json
	npm install

release: node_modules
	NODE_ENV=production node index.js
	./node_modules/.bin/gh-pages -d build -r git@github.com:algolia/wordpress.git

.PHONY: build
