---
title: Getting Started
description: Step by step guide to setup the Search by Algolia plugin for WordPress.
layout: page.html
---
## Algolia Credentials

When connected to the WordPress admin panel, search for the following Algolia Search entry in the left sidebar, and click on it.

![Algolia Search menu entry](img/configuration/algolia-search-menu-entry.png)

You will face the following form asking for your Algolia credentials.

![credentials form](img/configuration/credentials-form.png)

If you have no Algolia account yet, you can create one in a few seconds for free [over here](https://www.algolia.com/users/sign_up).

Once you have created your account and you have signed in, access your Algolia API keys by following [this link](https://www.algolia.com/api-keys).

![API keys](img/configuration/api-keys.png)

From that screen you will be able to copy/paste the following necessary keys from the Algolia dashboard to your WordPress admin form:
- Application ID
- Search-Only API Key
- Admin API Key

<div class="alert alert-info">Copy/pasting these keys manually is very error prone, fortunately there is a convenient little copy button at the right side of each key that will make this easy.</div>

Once you have filled in the 3 required keys, click on the <span class="wp-btn">Save Changes</span> button at the bottom of the form.

If you provided the correct keys, a green success message will appear, otherwise you will get an error message. If you do get an error message, please ensure that you have correctly copy pasted the right key in the right WordPress admin field. Also be sure to use the copy button as showed earlier.

<div class="alert alert-warning">For security reasons, the Search-Only API key should only have the `search` ACL has it will be display publicly in your html templates.</div>


## Indexing

Once you have correctly provided WordPress with your Algolia keys as explained in [the previous step](#algolia-credentials), you will now see that the Algolia Search menu entry has now expanded:

![expanded menu](img/configuration/menu-exanded.png)

For now, lets head to the `Indexing` entry where you will be able to tell WordPress which indices you want to synchronize with Algolia.

You should now be facing a list of checkboxes like:

![indices form](img/configuration/indices-form.png)

<div class="alert alert-warning">Please note that if you have already installed third party plugins to your WordPress installation, you might have more entries than in the above screenshot which is perfectly fine.</div>

The Algolia Search plugin uses WordPress core features such as **Post Types**, **Taxonomies** or **Users** to find potential content types to index. Any plugin creating new *Post Types* or *Taxonomies* will automatically be listed here an eligible for synchronization with Algolia.

Let's proceed by choosing the indices you want to make searchable on your website, for example **Posts**, **Pages** and **Users**. Simply check the corresponding checkboxes and click on <span class="wp-btn">Save Changes</span>.

Green validation messages should confirm that the selected indices are about to be synchronized with Algolia.

![indexing validation messages](img/configuration/indexing-validation.png)

Your data will now be pushed asynchronously behind the scenes, and it might take a few seconds. You can track what is happening by navigating to the *Logs* page of the plugin.

<div class="alert alert-warning">For performances reasons, logging will only occur if `WP_DEBUG` is defined to `true`.</div>

On the same page you will also see an text field called **Index name prefix** which defaults to `wp_`.

![Index name prefix field](img/configuration/index-name-prefix.png)

By changing this you will update all the synchronized indices prefixes. This feature comes in handy when you want for example to use a same Algolia account for staging and production.

In that case you would use for example `wp_staging_` and `wp_production_` for respectively staging and production environments.

<div class="alert alert-warning">Changing the index name prefix will automatically queue a move task to make existing indices adopt the new convention. No need to re-index everything after this kind of change.</div>


## Verify Synchronization

At this stage indices you selected in the [previous step](#indexing) should now be available in Algolia.

If you want to make sure the synchronization has succeeded you could go to your Algolia dashboard, on the [indices page](https://www.algolia.com/explorer).
From the dropdown near the title, you should see the 3 indices we chose to push from WordPress to Algolia.

![indices dropdown](img/configuration/indices-dropdown.png)

If your indices do not appear yet, you may want to check your logs. [Read more about logs here](logs.html).

<div class="alert alert-info">Note that there can be more than 1 record per Post entry. To understand why, please read [our section about Indexing Schemas](index-schema.html).</div>


## Autocomplete

Now that Algolia knows about your content, it is time to offer your users a nice *find-as-you-type* search experience.
First of all, here is the search experience you will get out of the box at the end of this section:

![autocomplete preview](img/configuration/autocomplete-preview.png)

To enable the Autocomplete feature, navigate to the **Autocomplete** admin page from the Algolia Search admin menu entry:

![autocomplete menu entry](img/configuration/autocomplete-menu.png)

You should see the following:

![autocomplete form](img/configuration/autocomplete-form.png)

You are able to enable or disable the autocomplete feature at any time by simply checking or un-checking the checkbox.
For now, check the box!

Once enabled, you should now see that the form has expanded as follow:

![autocomplete form expanded](img/configuration/autocomplete-form-expanded.png)

From here there are a couple of settings that you can customize.

|Setting&nbsp;name    |Description
|----------------|-----------
|**Enable**          |Make search results for this index available through the autocomplete.
|**Index**           |The name of the index, this is just here for you to know what each row is about.
|**Max.&nbsp;Suggestions**|The maximum number of suggestions to display for this index. Only the `n` most relevant search results will be displayed for this index, and the others will be discarded.
|**Position**        |Determines the display position of suggestions in relation to the other autocomplete enabled indices. The bigger the position, the lower the section will be in the display.

Let's keep the default settings and enable all 3 content types for the *find-as-you-type* autocomplete experience. Do not forget to hit the <span class="wp-btn">Save Changes</span> buttons once your finished.

<div class="alert alert-info">You should now be able to use the Algolia powered autocomplete on your website.</div>

## Search Page

WordPress has a built-in search engine relying on SQL queries. The problem is that it does not support the features of the Algolia search engine like typo tolerance, custom ranking, and a lot more that you can check out on the https://www.algolia.com/ website.

This is the very reason this plugin comes with a feature that lets you override WordPress's native search feature with Algolia.

<div class="alert alert-warning">Replacing the WordPress search feature ensures you get the same results than what you see in the *autocomplete* feature.</div>

Once you click on the **Search Page** entry under the **Algolia Search** menu, here is what you will see:

![native search form](img/configuration/native-search-form.png)

You have 2 options to override WordPress's default search feature:

### Use Algolia in the backend

With this option WordPress search will be powered by Algolia behind the scenes.

<div class="alert alert-info">This option does not support filtering and displaying instant search results but has the advantage to play nicely with any theme.</div>

![backend search page](img/configuration/backend-search-page.png)

### Use Algolia with Instantsearch.js

With this option WordPress search will be powered by the [instantsearch.js](https://community.algolia.com/instantsearch.js/) library. It replaces your search results page with an instant (JavaScript-based) search experience.

<div class="alert alert-warning">Please note that the plugin is shipped with some sensible default styling rules but it could require some CSS adjustments to provide an optimal search experience.</div>

![instant search page](img/configuration/instant-search-page.gif)

## From Here On Out

You want to understand more in depth what Algolia is and how you can benefit from it? Please consult our getting started: https://www.algolia.com/doc/getting-started

<div class="alert alert-warning">Struggling with something? Go to the [Q&A section](frequently-asked-questions.html).</div>






