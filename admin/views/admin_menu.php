<?php
    $langDomain         = "algolia";
    $algolia_registry   = \Algolia\Core\Registry::getInstance();
    $theme_helper       = new Algolia\Core\ThemeHelper();
    $current_theme      = $theme_helper->get_current_theme();

    global $external_attrs;
    global $attributesToIndex;
?>

<div id="algolia-settings" class="wrap">

    <a target="_blank" href="//algolia.com/dashboard" class="header-button" id="dashboard-link">Go to Algolia dashboard</a>

    <?php if ($algolia_registry->validCredential) : ?>
    <h2>
        Algolia Realtime Search
        <button type="button" class="button button-primary " id="algolia_reindex" name="algolia_reindex">
            <i class="dashicons dashicons-upload"></i>
            Reindex data
        </button>
        <em id='last-update' style="color: #444;font-family: 'Open Sans',sans-serif;font-size: 13px;line-height: 1.4em;">
            Last update:
            <?php if ($algolia_registry->last_update): ?>
                <?php echo date('Y-m-d H:i:s', $algolia_registry->last_update); ?>
            <?php else: ?>
                N/A
            <?php endif; ?>
        </em>
    </h2>

    <div class="wrapper">
        <?php if ($algolia_registry->validCredential) : ?>
        <div style="clear: both;"</div>
        <?php endif; ?>

        <div id="results-wrapper" style="display: none;">
            <div class="content">
                <div class="show-hide">

                    <div class="content-item">
                        <div>Progression</div>
                        <div style='padding: 5px;'>
                            <div id="reindex-percentage">
                            </div>
                            <div style='clear: both'></div>
                        </div>
                    </div>

                    <div class="content-item">
                        <div>Logs</div>
                        <div style='padding: 5px;'>
                            <table id="reindex-log"></table>
                        </div>
                    </div>

                    <div class="content-item">
                        <button style="display: none;" type="submit" name="submit" id="submit" class="close-results button button-primary">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <h2>
        Algolia Realtime Search
    </h2>
    <?php endif; ?>

    <div class="wrapper">
        <div class="tabs myclearfix">

            <?php if (! $algolia_registry->validCredential) : ?>
            <div data-tab="#credentials" class="title selected">Credentials</div>
            <?php else: ?>
            <div data-tab="#credentials" class="title">Credentials</div>
            <?php endif; ?>

            <?php if ($algolia_registry->validCredential) : ?>

            <div data-tab="#configuration"          class="title selected">UI Configuration</div>
            <div data-tab="#indexable-types"        class="title">Types</div>
            <div data-tab="#extra-metas"            class="title">Attributes</div>
            <div data-tab="#searchable_attributes"  class="title">Search Configuration</div>
            <div data-tab="#custom-ranking"         class="title">Ranking Configuration</div>
            <div data-tab="#sortable_attributes"    class="title">Sorting Configuration</div>

            <?php endif; ?>
            <div style="clear:both"></div>
        </div>


        <div class="tab-content" id="_credentials">
            <form action="<?php echo site_url(); ?>/wp-admin/admin-post.php" method="post">
                <input type="hidden" name="action" value="update_account_info">
                <div class="content-wrapper" id="account">
                    <div class="content">
                        <div style="float: left; width: 50%;">
                            <div style="padding: 0px 10px;">
                                <h3>Algolia account</h3>
                                <p class="help-block">Configure here your <a href="https://www.algolia.com">Algolia</a> credentials. You can find them in the "<a href="https://www.algolia.com/licensing">Credentials</a>" section of your dashboard. Don't have one? <a href="http://www.algolia.com/users/sign_up" target="_blank">Create one here</a>.</p>
                                <?php if ($algolia_registry->validCredential == false && ($algolia_registry->app_id || $algolia_registry->search_key || $algolia_registry->admin_key)) : ?>
                                    <p class="warning">Your credentials are not valid</p>
                                <?php endif; ?>
                                <div class="content-item">
                                    <label for="algolia_app_id">Application ID</label>
                                    <div><input type="text" value="<?php echo $algolia_registry->app_id ?>" name="APP_ID" id="algolia_app_id"></div>
                                    <p class="description">Your Algolia APPLICATION ID.</p>
                                </div>
                                <div class="content-item">
                                    <label for="algolia_search_api_key">Search-Only API Key</label>
                                    <div><input type="text" value="<?php echo $algolia_registry->search_key ?>" name="SEARCH_KEY" id="algolia_search_api_key"></div>
                                    <p class="description">Your Algolia search-only API KEY (public).</p>
                                </div>
                                <div class="content-item">
                                    <label for="algolia_api_key">Admin API Key</label>
                                    <div><input type="password" value="<?php echo $algolia_registry->admin_key ?>" name="ADMIN_KEY" id="algolia_api_key"></div>
                                    <p class="description">Your Algolia ADMIN API KEY.</p>
                                </div>
                                <div class="content-item">
                                    <label for="algolia_index_name">Index names prefix</label>
                                    <div><input type="text" value="<?php echo $algolia_registry->index_name; ?>" name="INDEX_NAME" id="algolia_index_name" placeholder="wordpress_"></div>
                                    <p class="description">This value will prepend all the index names.</p>
                                </div>
                                <div class="content-item">
                                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                                </div>
                            </div>
                        </div>
                        <!--<div style="float: left; width: 50%; box-sizing:border-box; border-left: solid 1px #DDDDDD">
                            <div style="padding: 0px 10px;">
                                <h3>How to configure and customize your search</h3>
                                <div>
                                    <iframe style="width: 100%;" height="315" src="https://www.youtube.com/embed/8OBfr46Y0cQ" frameborder="0" allowfullscreen></iframe>
                                </div>
                                <h3>Add-on ressources</h3>
                                <div>
                                    <div>
                                        <div><b>Tutorials</b></div>
                                        <div>
                                            <ul>
                                                <li><a href="#">How to customize the autocomplete result box</a></li>
                                                <li><a href="#">How to create a custom Search result page</a></li>
                                                <li><a href="#">Show more</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div style="margin-top: 20px;">
                                        <div><b>Documentation (for Developers)</b></div>
                                        <div>
                                            <ul>
                                                <li><a href="#">http://www.algolia.com/doc</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>-->
                        <div style="clear: both;"></div>
                    </div>
                </div>
            </form>
        </div>

        <?php if ($algolia_registry->validCredential) : ?>

        <div class="tab-content" id="_configuration">
            <form action="<?php echo site_url(); ?>/wp-admin/admin-post.php" method="post">
                <input type="hidden" name="action" value="update_type_of_search" />
                <div class="content-wrapper" id="type_of_search">
                    <div class="content">
                        <h3>Configure your Search experience</h3>
                        <div class="content-item">
                            <label for="search-input-selector">1 - Select the search input used</label>
                            <div>
                                <input type="text" value="<?php echo str_replace("\\", "",$algolia_registry->search_input_selector); ?>" name="SEARCH_INPUT_SELECTOR" id="search-input-selector">
                                <p class="description">The jQuery selector used to select your search bar.</p>
                            </div>
                        </div>
                        <div class="has-extra-content content-item">
                            <label>2 - Select your search experience</label>
                            <div>
                                <input type="radio"
                                                <?php checked($algolia_registry->type_of_search == 'autocomplete'); ?>
                                                class="instant_radio"
                                                name="TYPE_OF_SEARCH"
                                                value="autocomplete"
                                                id="instant_radio_autocomplete" />
                                 <label for="instant_radio_autocomplete">Autocomplete</label>
                                 <p class="description">Add an auto-completion menu to your search bar.</p>
                           </div>
                            <div class="show-hide" style="display: none;">
                                <div>
                                    <label for="instant_radio_autocomplete_nb_results">Results by section</label>
                                    <input type="number" min="0" value="<?php echo $algolia_registry->number_by_type; ?>" name="NUMBER_BY_TYPE" id="instant_radio_autocomplete_nb_results">
                                    <p class="description">The number of results per section in the dropdown menu.</p>
                                </div>
                            </div>
                        </div>
                        <div class="has-extra-content content-item">
                            <div>
                                <input type="radio"
                                                      <?php checked($algolia_registry->type_of_search == 'instant'); ?>
                                                      class="instant_radio"
                                                      name="TYPE_OF_SEARCH"
                                                      value="instant"
                                                      id="instant_radio_instant" />
                                <label for="instant_radio_instant">Instant-search results page</label>
                                <p class="description">Refresh the whole results page as you type.</p>
                            </div>
                            <div class="show-hide" style="display: none;">
                                <div>
                                    <label for="instant_radio_instant_jquery_selector">DOM selector</label>
                                    <input type="text"
                                           id="instant_radio_instant_jquery_selector"
                                           value="<?php echo str_replace("\\", "", $algolia_registry->instant_jquery_selector); ?>"
                                           placeholder="#content"
                                           name="JQUERY_SELECTOR"
                                           value="" />
                                    <p class="description">The jQuery selector used to inject the search results.</p>
                                </div>
                                <div>
                                    <label for="instant_radio_instant_nb_results">Number of results by page</label>
                                    <input type="number" min="0" value="<?php echo $algolia_registry->number_by_page; ?>" name="NUMBER_BY_PAGE" id="instant_radio_instant_nb_results">
                                    <p class="description">The number of results to display on a results page.</p>
                                </div>
                                <div>
                                    <label for="instant_radio_content_nb_snippet">Number of words on the content snippet</label>
                                    <input type="number" min="0" value="<?php echo $algolia_registry->number_of_word_for_content; ?>" name="NUMBER_OF_WORD_FOR_CONTENT" id="instant_radio_content_nb_snippet">
                                    <p class="description">The number of results to display on a results page.</p>
                                </div>
                            </div>
                        </div>
                        <h3>Theme</h3>
                        <div style="padding-left: 5px; padding-bottom: 10px;">
                            Select the theme you want to use to display the search results.<br>
                            You can either use one of the 2 samples themes, or display results in your own build. <a href="#">Learn how to build a theme</a>
                        </div>
                        <div class="content-item">
                            <div class="theme-browser">
                                <div class="themes">
                                    <?php foreach ($theme_helper->available_themes() as $theme): ?>
                                    <?php if ($theme->dir == $algolia_registry->theme): ?>
                                    <div class="theme active">
                                        <?php else: ?>
                                        <div class="theme">
                                            <?php endif; ?>
                                            <label for="<?php echo $theme->dir; ?>">
                                                <div class="theme-screenshot">
                                                    <?php if ($theme->screenshot): ?>
                                                        <img class="screenshot instant" src="<?php echo $theme->screenshot; ?>">
                                                    <?php else: ?>
                                                        <div class="no-screenshot screenshot instant">No screenshot</div>
                                                    <?php endif; ?>
                                                    <?php if ($theme->screenshot_autocomplete): ?>
                                                        <img class="screenshot autocomplete" src="<?php echo $theme->screenshot_autocomplete; ?>">
                                                    <?php else: ?>
                                                        <div class="no-screenshot autocomplete instant">No screenshot</div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="theme-name">
                                                    <?php echo $theme->name; ?>
                                                    <input type="radio"
                                                           id="<?php echo $theme->dir; ?>"
                                                        <?php checked($theme->dir == $algolia_registry->theme); ?>
                                                           name='THEME'
                                                           value="<?php echo $theme->dir; ?>"/>
                                                </div>
                                                <div><?php echo $theme->description; ?></div>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div style="clear: both"></div>
                            </div>
                        <div class="content-item">
                            <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                        </div>
                    </div>
                </div>
            </form>
        </div>



        <div class="tab-content" id="_indexable-types">
            <form action="<?php echo site_url(); ?>/wp-admin/admin-post.php" method="post">
                <input type="hidden" name="action" value="update_indexable_types">
                <div class="content-wrapper" id="customization">
                    <div class="content">
                        <p class="help-block">Configure here the types you want index.</p>
                        <table>
                            <tr data-order="-1">
                                <th class="table-col-enabled">Enabled</th>
                                <th>Name</th>
                                <?php if ($algolia_registry->type_of_search == 'autocomplete'): ?>
                                <th>Auto-completion menu label &amp; ordering</th>
                                <?php endif; ?>
                            </tr>
                        <?php foreach (get_post_types() as $type) : ?>
                            <?php $count = wp_count_posts($type)->publish; ?>
                            <?php if ($count == 0 || in_array($type, array('revision', 'nav_menu_item', 'acf', 'product_variation', 'shop_order', 'shop_order_refund', 'shop_coupon', 'shop_webhook', 'wooframework'))) { continue; } ?>
                            <?php
                            $order = -1;
                            if (is_array($algolia_registry->indexable_types) && in_array($type, array_keys($algolia_registry->indexable_types)))
                                $order = $algolia_registry->indexable_types[$type]['order'];
                            ?>
                            <?php if ($order != -1): ?>
                                <tr data-order="<?php echo $order; ?>">
                            <?php else: ?>
                                <tr data-order="<?php echo (10000 + $i); $i++ ?>">
                            <?php endif; ?>
                                <td class="table-col-enabled">
                                    <input type="checkbox"
                                           name="TYPES[<?php echo $type; ?>][SLUG]"
                                           value="<?php echo $type; ?>"
                                           <?php checked(is_array($algolia_registry->indexable_types) && in_array($type, array_keys($algolia_registry->indexable_types))) ?>
                                        >
                                </td>
                                <td>
                                    <?php echo $type; ?> (<?php echo $count; ?>)
                                </td>
                                <?php if ($algolia_registry->type_of_search == 'autocomplete'): ?>
                                <td>
                                    <input type="text" value="<?php echo (isset($algolia_registry->indexable_types[$type]) ? $algolia_registry->indexable_types[$type]['name'] : "") ?>" name="TYPES[<?php echo $type; ?>][NAME]">
                                    <img width="10" src="<?php echo plugin_dir_url(__FILE__); ?>../imgs/move.png">
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                        </table>
                        <div class="content-item">
                            <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="tab-content" id="_searchable_attributes">
            <form action="<?php echo site_url(); ?>/wp-admin/admin-post.php" method="post">
                <input type="hidden" name="action" value="update_searchable_attributes">
                <div class="content-wrapper" id="customization">
                    <div class="content">
                        <p class="help-block">Configure here the attributes you want to be able to search in.</p>
                        <table>
                            <tr data-order="-1">
                                <th class="table-col-enabled">Enabled</th>
                                <th>Name</th>
                                <th>Attribute ordering</th>
                            </tr>
                            <?php
                            $searchable = $attributesToIndex;

                            foreach (array_keys($algolia_registry->indexable_tax) as $tax)
                                if ($tax != 'type')
                                    $searchable[] = $tax;

                            foreach (get_post_types() as $type)
                            {
                                $metas = get_meta_key_list($type);

                                if (isset($external_attrs[$type.'_attrs']))
                                    $metas = array_merge(get_meta_key_list($type), $external_attrs[$type.'_attrs']);


                                foreach ($metas as $meta_key)
                                    if (is_array($algolia_registry->indexable_types) && in_array($type, array_keys($algolia_registry->indexable_types)))
                                        if (isset($algolia_registry->metas[$type])
                                            && in_array($meta_key, array_keys($algolia_registry->metas[$type]))
                                            && $algolia_registry->metas[$type][$meta_key]["indexable"])
                                            $searchable[] = $meta_key;
                            }

                            if (isset($algolia_registry->date_custom_ranking['enabled']) && $algolia_registry->date_custom_ranking['enabled'])
                                $searchable[] = 'date';

                            ?>
                            <?php foreach ($searchable as $searchItem): ?>
                            <?php
                            $order = -1;
                            if (isset($algolia_registry->searchable[$searchItem]))
                                $order = $algolia_registry->searchable[$searchItem]['order'];
                            ?>
                            <?php if ($order != -1): ?>
                                <tr data-order="<?php echo $order; ?>">
                            <?php else: ?>
                                <tr data-order="<?php echo (10000 + $i); $i++ ?>">
                            <?php endif; ?>
                                <td class="table-col-enabled"><input <?php checked(isset($algolia_registry->searchable[$searchItem])); ?> type="checkbox" name="ATTRIBUTES[<?php echo $searchItem; ?>][SEARCHABLE]"></td>
                                <td>
                                    <?php echo $searchItem; ?>
                                </td>
                                <td>
                                    <select name="ATTRIBUTES[<?php echo $searchItem; ?>][ORDERED]">
                                    <?php foreach (array("ordered" => "Ordered", "unordered" => "Unordered") as $key => $value): ?>
                                        <?php if (isset($algolia_registry->searchable[$searchItem]) && $algolia_registry->searchable[$searchItem]['ordered'] == $key): ?>
                                            <option selected value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                        <?php else: ?>
                                            <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    </select>
                                    <img width="10" src="<?php echo plugin_dir_url(__FILE__); ?>../imgs/move.png">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </table>
                        <div class="content-item">
                            <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="tab-content" id="_sortable_attributes">
            <form action="<?php echo site_url(); ?>/wp-admin/admin-post.php" method="post">
                <input type="hidden" name="action" value="update_sortable_attributes">
                <div class="content-wrapper" id="customization">
                    <div class="content">
                        <p class="help-block">By default results are sorted by text relevance &amp; your ranking criteria. Configure here the attributes you want to use for the additional sorts (by price, by date, etc...).</p>
                        <table>
                            <tr data-order="-1">
                                <th class="table-col-enabled">Enabled</th>
                                <th>Name</th>
                                <th>Sort</th>
                                <th>Label</th>
                            </tr>
                            <?php
                            $sortable = array();

                            foreach (array_keys($algolia_registry->indexable_tax) as $tax)
                                if ($tax != 'type')
                                    $sortable[] = $tax;

                            foreach (get_post_types() as $type)
                            {
                                $metas = get_meta_key_list($type);

                                if (isset($external_attrs[$type.'_attrs']))
                                    $metas = array_merge(get_meta_key_list($type), $external_attrs[$type.'_attrs']);


                                foreach ($metas as $meta_key)
                                    if (is_array($algolia_registry->indexable_types) && in_array($type, array_keys($algolia_registry->indexable_types)))
                                        if (isset($algolia_registry->metas[$type])
                                            && in_array($meta_key, array_keys($algolia_registry->metas[$type]))
                                            && $algolia_registry->metas[$type][$meta_key]["indexable"])
                                            $sortable[] = $meta_key;
                            }

                            if (isset($algolia_registry->date_custom_ranking['enabled']) && $algolia_registry->date_custom_ranking['enabled'])
                                $sortable[] = 'date';

                            ?>
                            <?php foreach ($sortable as $sortItem): ?>
                                <?php foreach (array('asc', 'desc') as $sort): ?>
                                <tr>
                                    <td class="table-col-enabled">
                                        <input <?php checked(isset($algolia_registry->sortable[$sortItem.'_'.$sort])); ?> type="checkbox" name="ATTRIBUTES[<?php echo $sortItem; ?>][<?php echo $sort; ?>]">
                                    </td>
                                    <td>
                                        <?php echo $sortItem; ?>
                                    </td>
                                    <td>
                                        <span class="dashicons dashicons-arrow-<?php echo($sort == 'asc' ? 'up' : 'down'); ?>-alt"></span>
                                        <?php echo($sort == 'asc' ? 'Ascending' : 'Descending'); ?>
                                    </td>
                                    <td>
                                        <input type="text"
                                               value="<?php echo (isset($algolia_registry->sortable[$sortItem.'_'.$sort]) ? $algolia_registry->sortable[$sortItem.'_'.$sort]["label"] : "") ?>" name="ATTRIBUTES[<?php echo $sortItem; ?>][LABEL_<?php echo $sort; ?>]">
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </table>
                        <div class="content-item">
                            <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="tab-content" id="_extra-metas">
            <form id="extra-metas-form" action="<?php echo site_url(); ?>/wp-admin/admin-post.php" method="post">
                <input type="hidden" name="action" value="update_extra_meta">
                <div class="content-wrapper" id="customization">
                    <div class="content">
                        <p class="help-block">
                            Configure here the additional attributes you want to include in your Algolia records.
                            <br>
                            Default attributes : objectID, authorId, author, author_login, permalink, date, content, content_stripped, title, slug, modified, parent, menu_order, type
                        </p>

                        <table id="extra-meta-and-taxonomies">
                            <tr data-order="-1">
                                <th class="table-col-enabled">Enabled</th>
                                <th>Type</th>
                                <th>Name</th>
                                <th>Facetable</th>
                                <th>Facet type</th>
                                <th>Facet label &amp; ordering</th>
                            </tr>
                        </table>

                        <div>
                            <div data-tab="#extra-metas-attributes" class="title selected">Extra Attributes</div>
                            <div data-tab="#taxonomies"             class="title">Taxonomies</div>
                        </div>

                        <div class="sub-tab-content" id="extra-metas-attributes">
                            <table>
                                <tr data-order="-1">
                                    <th class="table-col-enabled">Enabled</th>
                                    <th>Type</th>
                                    <th>Meta key</th>
                                    <th>Facetable</th>
                                    <th>Facet type</th>
                                    <th>Facet label &amp; ordering</th>
                                </tr>
                                <?php

                                $i = 0;

                                $facet_types = array_merge(array("conjunctive" => "Conjunctive", "disjunctive" => "Disjunctive"), $current_theme->facet_types);

                                ?>
                                <?php foreach (get_post_types() as $type) : ?>
                                    <?php if (is_array($algolia_registry->indexable_types) && in_array($type, array_keys($algolia_registry->indexable_types))) : ?>
                                        <?php
                                        $metas = get_meta_key_list($type);

                                        if (isset($external_attrs[$type.'_attrs']))
                                            $metas = array_merge(get_meta_key_list($type), $external_attrs[$type.'_attrs']);
                                    ?>
                                        <?php foreach ($metas as $meta_key) : ?>
                                            <?php
                                            $order = -1;
                                            if (isset($algolia_registry->metas[$type]) && in_array($meta_key, array_keys($algolia_registry->metas[$type])))
                                                $order = $algolia_registry->metas[$type][$meta_key]['order'];
                                            ?>
                                            <?php if ($order != -1): ?>
                                                <tr data-type="extra-meta" data-order="<?php echo $order; ?>">
                                            <?php else: ?>
                                          <tr data-type="extra-meta" data-order="<?php echo (10000 + $i); $i++ ?>">
                                            <?php endif; ?>
                                                <td class="table-col-enabled">
                                                    <!-- PREVENT FROM ERASING CUSTOM RANKING -->
                                                    <?php $customs = array('custom_ranking' => 'CUSTOM_RANKING', 'custom_ranking_order' => 'CUSTOM_RANKING_ORDER', 'custom_ranking_sort' => 'CUSTOM_RANKING_SORT'); ?>
                                                    <?php foreach($customs as $custom_key => $custom_value): ?>
                                                        <?php if (isset($algolia_registry->metas[$type])
                                                            && in_array($meta_key, array_keys($algolia_registry->metas[$type]))
                                                            && $algolia_registry->metas[$type][$meta_key][$custom_key]): ?>
                                                        <input type="hidden"
                                                               name="TYPES[<?php echo $type; ?>][METAS][<?php echo $meta_key; ?>][<?php echo $custom_value; ?>]"
                                                               value="<?php echo $algolia_registry->metas[$type][$meta_key][$custom_key]; ?>"
                                                            >
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                    <!-- /////// PREVENT FROM ERASING CUSTOM RANKING -->

                                                    <input type="checkbox"
                                                           name="TYPES[<?php echo $type; ?>][METAS][<?php echo $meta_key; ?>][INDEXABLE]"
                                                           value="<?php echo $type; ?>"
                                                        <?php checked(isset($algolia_registry->metas[$type])
                                                            && in_array($meta_key, array_keys($algolia_registry->metas[$type]))
                                                            && $algolia_registry->metas[$type][$meta_key]["indexable"]); ?>
                                                        >
                                                </td>
                                                <td><?php echo $type; ?></td>
                                                <td><?php echo $meta_key; ?></td>
                                                <td>
                                                    <input type="checkbox"
                                                           name="TYPES[<?php echo $type; ?>][METAS][<?php echo $meta_key; ?>][FACETABLE]"
                                                           value="1"
                                                        <?php checked(isset($algolia_registry->metas[$type])
                                                            && in_array($meta_key, array_keys($algolia_registry->metas[$type]))
                                                            && $algolia_registry->metas[$type][$meta_key]["facetable"]); ?>
                                                        >
                                                </td>
                                                <td>
                                                    <select name="TYPES[<?php echo $type; ?>][METAS][<?php echo $meta_key; ?>][TYPE]">
                                                        <?php foreach ($facet_types as $key => $value): ?>
                                                            <?php if (checked(isset($algolia_registry->metas[$type])
                                                                && in_array($meta_key, array_keys($algolia_registry->metas[$type]))
                                                                && $algolia_registry->metas[$type][$meta_key]["type"] == $key)) : ?>
                                                            <option selected="selected" value="<?php echo $key ?>"><?php echo $value; ?></option>
                                                            <?php else : ?>
                                                            <option value="<?php echo $key ?>"><?php echo $value; ?></option>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text"
                                                           value="<?php echo (isset($algolia_registry->metas[$type][$meta_key]) ? $algolia_registry->metas[$type][$meta_key]["name"] : "") ?>" name="TYPES[<?php echo $type; ?>][METAS][<?php echo $meta_key; ?>][NAME]">
                                                    <img width="10" src="<?php echo plugin_dir_url(__FILE__); ?>../imgs/move.png">
                                                </td>
                                                <input type="hidden" name="TYPES[<?php echo $type; ?>][METAS][<?php echo $meta_key; ?>][ORDER]" class="order" />
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                <?php endforeach; ?>
                            </table>
                        </div>
                        <div class="sub-tab-content" id="taxonomies">
                            <table>
                                <tr data-order="-1">
                                    <th>Enabled</th>
                                    <th></th>
                                    <th>Name</th>
                                    <th>Facetable</th>
                                    <th>Facet type</th>
                                    <th>Facet label &amp; ordering</th>
                                </tr>

                                <?php $i = 0; ?>
                                <?php foreach (array_merge($algolia_registry->extras, get_taxonomies()) as $tax) : ?>
                                    <?php $count = wp_count_terms($tax, array('hide_empty' => false)); ?>
                                    <?php if (in_array($tax, $algolia_registry->extras) || (is_numeric($count) && intval($count) > 0)): ?>

                                        <?php
                                        $order = -1;
                                        if (is_array($algolia_registry->indexable_tax) && isset($algolia_registry->indexable_tax[$tax]))
                                            $order = $algolia_registry->indexable_tax[$tax]['order'];
                                        ?>
                                        <?php if ($order != -1): ?>
                                            <tr data-type="taxonomy" data-order="<?php echo $order; ?>">
                                        <?php else: ?>
                                            <tr data-type="taxonomy" data-order="<?php echo (10000 + $i); $i++; ?>">
                                        <?php endif; ?>
                                        <td>
                                            <?php if (in_array($tax, $algolia_registry->extras) == false): ?>
                                                <input type="checkbox"
                                                       name="TAX[<?php echo $tax; ?>][SLUG]"
                                                       value="<?php echo $tax; ?>"
                                                    <?php checked(is_array($algolia_registry->indexable_tax) && in_array($tax, array_keys($algolia_registry->indexable_tax))); ?>
                                                    >
                                            <?php else: ?>
                                                <i class="dashicons dashicons-yes"></i>
                                                <input type="hidden" name="TAX[<?php echo $tax; ?>][SLUG]" value="<?php echo $tax; ?>">
                                            <?php endif; ?>
                                        </td>
                                        <td></td>
                                        <td>
                                            <?php echo $tax; ?>
                                        </td>
                                        <td>
                                            <input type="checkbox"
                                                   value="facetable"
                                                <?php checked(is_array($algolia_registry->indexable_tax) && isset($algolia_registry->indexable_tax[$tax])
                                                    && $algolia_registry->indexable_tax[$tax]['facetable'])
                                                ?>
                                                   name="TAX[<?php echo $tax; ?>][FACETABLE]">
                                        </td>
                                        <td>
                                            <select name="TAX[<?php echo $tax; ?>][FACET_TYPE]">
                                                <?php foreach ($facet_types as $key => $value): ?>
                                                    <?php if (checked(isset($algolia_registry->indexable_tax[$tax])
                                                        && $algolia_registry->indexable_tax[$tax]["type"] == $key)) : ?>
                                                        <?php echo("o"); ?>
                                                        <option selected="selected" value="<?php echo $key ?>"><?php echo $value; ?></option>
                                                    <?php else : ?>
                                                        <option value="<?php echo $key ?>"><?php echo $value; ?></option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" value="<?php echo (isset($algolia_registry->indexable_tax[$tax]) ? $algolia_registry->indexable_tax[$tax]['name'] : "") ?>" name="TAX[<?php echo $tax; ?>][NAME]">
                                            <img width="10" src="<?php echo plugin_dir_url(__FILE__); ?>../imgs/move.png">
                                        </td>
                                        <input type="hidden" name="TAX[<?php echo $tax; ?>][ORDER]" class="order" />
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </table>
                        </div>
                        <div class="content-item">
                            <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <?php if ($algolia_registry->type_of_search == 'autocomplete') : ?>
            <style>
                #algolia-settings #extra-meta-and-taxonomies tr td:nth-child(n+4),
                #algolia-settings #extra-meta-and-taxonomies tr th:nth-child(n+4)
                {
                    display: none;
                }
            </style>
        <?php endif; ?>

        <div class="tab-content" id="_custom-ranking">
            <form action="<?php echo site_url(); ?>/wp-admin/admin-post.php" method="post">
                <input type="hidden" name="action" value="custom_ranking">
                <div class="content-wrapper" id="customization">
                    <div class="content">
                        <p class="help-block">Configure here the attributes used to reflect the popularity of your records (number of likes, number of views, number of sales...).</p>
                        <table>
                            <tr data-order="-1">
                                <th>Enabled</th>
                                <th>Meta key</th>
                                <th>Sort order</th>
                            </tr>

                            <tr data-order="<?php echo $algolia_registry->date_custom_ranking['sort']; ?>">
                                <td>
                                    <input type="checkbox"
                                           name="TYPES[date][METAS][date][CUSTOM_RANKING]"
                                        <?php checked($algolia_registry->date_custom_ranking['enabled']); ?>
                                        />
                                </td>
                                <td>date</td>
                                <td>
                                    <select name="TYPES[date][METAS][date][CUSTOM_RANKING_ORDER]">
                                        <?php foreach (array('asc' => 'ASC', 'desc' => 'DESC') as $key => $value): ?>
                                            <?php if ($algolia_registry->date_custom_ranking['order'] == $key): ?>
                                                <option selected value=<?php echo $key; ?>><?php echo $value; ?></option>
                                            <?php else : ?>
                                                <option value=<?php echo $key; ?>><?php echo $value; ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select><img width="10" src="<?php echo plugin_dir_url(__FILE__); ?>../imgs/move.png">
                                </td>
                            </tr>
                            <?php $i = 0; $n = 0; ?>
                            <?php foreach (get_post_types() as $type) : ?>
                                <?php
                                    $metas = get_meta_key_list($type);

                                    if (isset($external_attrs[$type.'_attrs']))
                                        $metas = array_merge(get_meta_key_list($type), $external_attrs[$type.'_attrs']);
                                ?>
                                <?php foreach ($metas as $meta_key) : ?>
                                    <?php if (is_array($algolia_registry->indexable_types) && in_array($type, array_keys($algolia_registry->indexable_types))) : ?>
                                        <?php if (isset($algolia_registry->metas[$type])
                                            && in_array($meta_key, array_keys($algolia_registry->metas[$type]))
                                            && $algolia_registry->metas[$type][$meta_key]["indexable"]): ?>

                                            <?php
                                            $order = -1;
                                            ++$n;
                                            if (isset($algolia_registry->metas[$type]) && in_array($meta_key, array_keys($algolia_registry->metas[$type])))
                                                $order = $algolia_registry->metas[$type][$meta_key]['custom_ranking_sort'];
                                            ?>
                                            <?php if ($order != -1): ?>
                                                <tr data-order="<?php echo $order; ?>">
                                            <?php else: ?>
                                                <tr data-order="<?php echo (10000 + $i); $i++ ?>">
                                            <?php endif; ?>
                                            <td>
                                                <input type="checkbox"
                                                       name="TYPES[<?php echo $type; ?>][METAS][<?php echo $meta_key; ?>][CUSTOM_RANKING]"
                                                    <?php checked(isset($algolia_registry->metas[$type])
                                                        && in_array($meta_key, array_keys($algolia_registry->metas[$type]))
                                                        && $algolia_registry->metas[$type][$meta_key]["custom_ranking"]); ?>
                                                    />
                                            </td>
                                            <td data-type="<?php echo $type; ?>"><?php echo $meta_key; ?></td>
                                            <td>
                                                <select name="TYPES[<?php echo $type; ?>][METAS][<?php echo $meta_key; ?>][CUSTOM_RANKING_ORDER]">
                                                    <?php foreach (array('asc' => 'ASC', 'desc' => 'DESC') as $key => $value): ?>
                                                        <?php if (isset($algolia_registry->metas[$type])
                                                            && in_array($meta_key, array_keys($algolia_registry->metas[$type]))
                                                            && $algolia_registry->metas[$type][$meta_key]["custom_ranking_order"] == $key): ?>
                                                            <option selected value=<?php echo $key; ?>><?php echo $value; ?></option>
                                                        <?php else : ?>
                                                            <option value=<?php echo $key; ?>><?php echo $value; ?></option>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </select><img width="10" src="<?php echo plugin_dir_url(__FILE__); ?>../imgs/move.png">
                                            </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </table>
                        <div class="_content-item">
                            <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                        </div>
                    </div>
                </div>
            </form>
            </div>
    <?php endif; ?>
    </div>
</div>