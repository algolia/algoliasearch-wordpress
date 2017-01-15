# 1.7.0
To upgrade from 1.6.0, follow the [Upgrade instructions](https://community.algolia.com/wordpress/upgrade.html#from-1-6-0-to-1-7-0).

- Fix the condition to remove the powered by
- Use autocomplete.js in noConflict mode
- Also append Cookies to wp_remote_post test calls on indexing screen
- Update WordPress tested up to 4.7
- Ensure wp-util is always loaded before instantsearch.js or autocomplete.js
- Enable Yoast frontend hooks when indexing records
- Check if the API is reachable before executing CLI commands

# 1.6.0
- Re-index indices instead of moving them when index name prefix is changed
- Keep the synonyms configured on the Algolia dashboard when we re-index
- Add a command to the WP-CLI integration to re-index a single index
- Allow API Keys and index name prefix to be configured with constants to ease switching between environments

# 1.5.0
- Split content attribute over several records if greater than 5000 bytes
- Support changing prefix on indices having replicas
- Add a constant to disable post splitting
- Fix an issue where the index name would rely on a non existing 'label' key of the post type object
- Replace Visual Composer shortcodes in posts

# 1.4.1
- Fix the validity check for the Admin API key in the settings tab

# 1.4.0
- Introduce the `algolia_loopback_request_args` filter to override loopback args. Allows queue to work with Basic Authentication
- Allow user generated Admin API keys
- Resolve logging performances causing queue to crash before being able to move _tmp indices to their final destination
- Make sure "shortcodes" are parsed inside post excerpts
- Add support for non UTF-8 content
- Display a more explicit error when credential validation fails
- Add a filter `algolia_search_params` to be able to filter backend search parameters

# 1.3.0
- Make the plugin play nicely with the W3 Total Cache plugin
- Fix an issue where the Cache Enabler plugin would break the autocomplete when JavaScript was inlined
- Introduced a constant to hide admin help notices
- Add WP CLI process-queue & re-index-all commands
- Allow theme authors to get full control over the Algolia templates location
- Stop the queue instead of trashing failed tasks
- Allow users to delete all pending tasks
- Allow users to stop the queue when it is running

# 1.2.0
- Fix broken pagination on instant search page
- Fix conflicts with plugins also using the PHP `simple_html_dom` library
- Limit the maximum number of log entries to 50 by default
- Fix an issue where empty errors would get logged during queue processing
- Introduce class `.no-autocomplete` to disable autocomplete on search inputs
- Add support for a new constant `ALGOLIA_LOOPBACK_HTTP` that allows forcing HTTP in queue loopback

# 1.1.0
- Allow special chars n autocomplete header templates
- Do not index inlined CSS styles
- Add support for a new constant that allows forcing HTTP in loopback
- Disallow redirection in loopback and add debug information
- Add `wp_remote_post` debugging information on indexing screen

# 1.0.0
- Index `menu_order` field by default
- Resolve conflict with Post Type Switcher plugin
- Fix the asynchronous call check that happens on Indexing page

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
