# 0.6.2
- Powered by can now be removed with the `ALGOLIA_POWERED_BY` constant
- Avoid spamming a notice on every admin page about non indexed searchable posts index
- Updated JavaScript dependencies

# 0.6.1
- Let users disable the Algolia powered by on the Settings page of the plugin
- Raise the default batch size up to 50 for every index

# 0.6.0
- Index all taxonomies even custom ones
- Made autocomplete & search page easily customizable by copy pasting a folder in the active theme
- Fix an issue where you would get 500 errors once if API credentials were not yet set
- Push all image sizes along with there urls to ease frontend template customization
- Fix issues were the queue would stop being processed because we attempted to load an item that was no longer existing
- Fix deletion syncing with Algolia
- Lower the autocomplete dropdown min width to 200px so that it fits on mobile display
- A lot of Docs improvements
- Allow to use dev version of the js scripts by using constant 'SCRIPT_DEBUG'
- Allow to avoid automatic queue processing by using 'ALGOLIA_AUTO_PROCESS_QUEUE' constant
- Fix facet counts on search results page
- Add support for replicas

# 0.5.0
- Fix a bug where autocomplete dropdown would not have the correct width after window resizing
- Exclude post content contained inside `<script>` tags by default
- Display facet counts which are now accurate thanks to the use of `facetingAfterDistinct`
- Ship instantsearch.js built on preact which highly reduces the size of the library
- Catch eventual PHP API Client exception raised when requirements were unmet
- Improved instantsearch.js default UI experience

# 0.4.0
- Add instantsearch.js integration as plug & play feature
- Improve autocomplete suggestion template
- Make sure we index everything even when WP_Query filters are introduced by plugins like WPML
- Index categories as a tree for usage in instantsearch.js hierarchicalMenu widget

# 0.3.0
- Cleanup the codebase
- Support install through composer

# 0.2.8
- Make the autocomplete dropdown match the width of the search input
- Display the snippet of content that matched in the autocomplete dropdown

# 0.2.7
- Fix the user agent so that the analytics API can detect required updates
- Add more contextual help if we detect incorrect configuration

# 0.2.6
- Make sure we detect custom post types before loading indices
- Log errors even when logging is turned off
- Add some contextual help in the admin UI

# 0.2.5
- Mainly wording adjustments

# 0.2.4
- Add 'post_date_formatted' to post records in Algolia

# 0.2.3
- Only forward WordPress cookie entries in async calls, resolves most of the queue being stopped issues

# 0.2.2
- Fix header size overflow due to cookies that made the queue stop at every few task
- Add a notice on indexing screen if wp_remote_post is not usable
- Log failed credentials validation

# 0.2.1
- Allow indexing of custom post types
- Scope logging disabled notice to logs page
- Fix the queue status display on indexing page for simple tasks
- Display notices in admin for every unmet requirement (cURL, mbstring)

# 0.2.0
- Implement retry strategy for tasks processing
- Allow to (en|dis)able logging from admin Logs page

# 0.0.1
- Initial Stable Release
