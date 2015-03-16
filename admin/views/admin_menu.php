<?php
    $langDomain = "algolia";
    $algolia_registry = \Algolia\Core\Registry::getInstance();
    $theme_helper = new Algolia\Core\ThemeHelper();
?>

<div id="algolia-settings" class="wrap">
    <h2>
        Algolia Search
        <a href="https://www.algolia.com/dashboard" title="Go to the Algolia dashboard" style="text-decoration: none" target="_blank"><i class="dashicons dashicons-admin-links"></i></a>
    </h2>

    <div class="wrapper">
        <?php if ($algolia_registry->validCredential) : ?>
        <button type="button" class="button button-secondary" id="algolia_reindex" name="algolia_reindex">
            <i class="dashicons dashicons-upload"></i>
            Reindex data
        </button>
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

    <div class="wrapper">
        <div class="tabs myclearfix">

            <?php if (! $algolia_registry->validCredential) : ?>
            <div data-tab="#credentials" class="title selected">Credentials</div>
            <?php else: ?>
            <div data-tab="#credentials" class="title">Credentials</div>
            <?php endif; ?>

            <?php if ($algolia_registry->validCredential) : ?>

            <div data-tab="#configuration" class="title selected">UI Configuration</div>
            <div data-tab="#indexable-types" class="title">Indices</div>
            <div data-tab="#extra-metas" class="title">Additional attributes</div>
            <div data-tab="#custom-ranking" class="title">Custom Ranking</div>
            <div data-tab="#taxonomies" class="title">Taxonomies</div>

            <?php endif; ?>
            <div style="clear:both"></div>
        </div>


        <div class="tab-content" id="credentials">
            <form action="<?php echo site_url(); ?>/wp-admin/admin-post.php" method="post">
                <input type="hidden" name="action" value="update_account_info">
                <div class="content-wrapper" id="account">
                    <div class="content">
                        <h3>Algolia account</h3>
                        <p class="help-block">Configure here your <a href="https://www.algolia.com">Algolia</a> credentials. You can find them in the "<a href="https://www.algolia.com/licensing">Credentials</a>" section of your dashboard.</p>
                        <div class="content-item">
                            <label for="algolia_app_id">Application ID</label>
                            <div><input type="text" value="<?php echo $algolia_registry->app_id ?>" name="APP_ID" id="algolia_app_id"></div>
                        </div>
                        <div class="content-item">
                            <label for="algolia_search_api_key">Search-Only API Key</label>
                            <div><input type="text" value="<?php echo $algolia_registry->search_key ?>" name="SEARCH_KEY" id="algolia_search_api_key"></div>
                        </div>
                        <div class="content-item">
                            <label for="algolia_api_key">Admin API Key</label>
                            <div><input type="password" value="<?php echo $algolia_registry->admin_key ?>" name="ADMIN_KEY" id="algolia_api_key"></div>
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
            </form>
        </div>

        <?php if ($algolia_registry->validCredential) : ?>

        <div class="tab-content" id="configuration">
            <form action="<?php echo site_url(); ?>/wp-admin/admin-post.php" method="post">
                <input type="hidden" name="action" value="update_type_of_search" />
                <div class="content-wrapper" id="type_of_search">
                    <div class="content">
                        <h3>Search bar</h3>
                        <p class="help-block">Configure here your search bar behavior.</p>
                        <div class="content-item">
                            <label for="search-input-selector">jQuery selector</label>
                            <div>
                                <input type="text" value="<?php echo str_replace("\\", "",$algolia_registry->search_input_selector); ?>" name="SEARCH_INPUT_SELECTOR" id="search-input-selector">
                                <p class="description">The DOM selector used to select your search bar.</p>
                            </div>
                        </div>
                        <div class="has-extra-content content-item">
                            <label>Search experience</label>
                            <div>
                                <input type="radio"
                                                <?php checked($algolia_registry->type_of_search == 'autocomplete'); ?>
                                                class="instant_radio"
                                                name="TYPE_OF_SEARCH"
                                                value="autocomplete"
                                                id="instant_radio_autocomplete" />
                                 <label for="instant_radio_autocomplete">Autocomplete</label>
                           </div>
                            <div class="show-hide" style="display: none;">
                                <div>
                                    <label for="instant_radio_autocomplete_nb_results">Number of results by category</label>
                                    <input type="number" min="0" value="<?php echo $algolia_registry->number_by_type; ?>" name="NUMBER_BY_TYPE" id="instant_radio_autocomplete_nb_results">
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
                            </div>
                            <div class="show-hide" style="display: none;">
                                <div>
                                    <label for="instant_radio_instant_jquery_selector">jQuery Selector</label>
                                    <input type="text"
                                           id="instant_radio_instant_jquery_selector"
                                           value="<?php echo str_replace("\\", "", $algolia_registry->instant_jquery_selector); ?>"
                                           placeholder="#content"
                                           name="JQUERY_SELECTOR"
                                           value="" />
                                </div>
                                <div>
                                    <label for="instant_radio_instant_nb_results">Number of results by page</label>
                                    <input type="number" min="0" value="<?php echo $algolia_registry->number_by_page; ?>" name="NUMBER_BY_PAGE" id="instant_radio_instant_nb_results">
                                </div>
                            </div>
                        </div>
                        <h3>Theme</h3>
                        <p class="help-block">Configure here the theme of your search results.</p>
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
                                                        <img src="<?php echo $theme->screenshot; ?>">
                                                    <?php else: ?>
                                                        <div class="no-screenshot">No screenshot</div>
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

        <div class="tab-content" id="indexable-types">
            <form action="<?php echo site_url(); ?>/wp-admin/admin-post.php" method="post">
                <input type="hidden" name="action" value="update_indexable_types">
                <div class="content-wrapper" id="customization">
                    <div class="content">
                        <p class="help-block">Configure here the indices you want create.</p>
                        <table>
                            <tr data-order="-1">
                                <th>Enabled</th>
                                <th>Name</th>
                                <th>Auto-completion menu label &amp; ordering</th>
                            </tr>
                        <?php foreach (get_post_types() as $type) : ?>
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
                                <td>
                                    <input type="checkbox"
                                           name="TYPES[<?php echo $type; ?>][SLUG]"
                                           value="<?php echo $type; ?>"
                                           <?php checked(is_array($algolia_registry->indexable_types) && in_array($type, array_keys($algolia_registry->indexable_types))) ?>
                                        >
                                </td>
                                <td>
                                    <?php echo $type; ?>
                                </td>
                                <td>
                                    <input type="text" value="<?php echo (isset($algolia_registry->indexable_types[$type]) ? $algolia_registry->indexable_types[$type]['name'] : "") ?>" name="TYPES[<?php echo $type; ?>][NAME]">
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


        <div class="tab-content" id="extra-metas">
            <form action="<?php echo site_url(); ?>/wp-admin/admin-post.php" method="post">
                <input type="hidden" name="action" value="update_extra_meta">
                <div class="content-wrapper" id="customization">
                    <div class="content">
                        <p class="help-block">Configure here the additional attributes you want to include in your Algolia records.</p>
                        <table>
                            <tr data-order="-1">
                                <th>Enabled</th>
                                <th>Name</th>
                                <th>Meta key</th>
                                <th>Facetable</th>
                                <th>Facet type</th>
                                <th>Facet label &amp; ordering</th>
                            </tr>
                            <?php $i = 0; ?>
                            <?php foreach (get_post_types() as $type) : ?>
                                <?php foreach (get_meta_key_list($type) as $meta_key) : ?>
                                    <?php if (is_array($algolia_registry->indexable_types) && in_array($type, array_keys($algolia_registry->indexable_types))) : ?>

                                        <?php
                                        $order = -1;
                                        if (isset($algolia_registry->metas[$type]) && in_array($meta_key, array_keys($algolia_registry->metas[$type])))
                                            $order = $algolia_registry->metas[$type][$meta_key]['order'];
                                        ?>
                                        <?php if ($order != -1): ?>
                                            <tr data-order="<?php echo $order; ?>">
                                        <?php else: ?>
                                            <tr data-order="<?php echo (10000 + $i); $i++ ?>">
                                        <?php endif; ?>
                                            <td>
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
                                                    <?php foreach (array("conjunctive" => "Conjunctive", "disjunctive" => "Disjunctive", "slider" => "Slider") as $key => $value): ?>
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
                                        </tr>
                                    <?php endif; ?>
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

        <div class="tab-content" id="custom-ranking">
            <form action="<?php echo site_url(); ?>/wp-admin/admin-post.php" method="post">
                <input type="hidden" name="action" value="custom_ranking">
                <div class="content-wrapper" id="customization">
                    <div class="content">
                        <p class="help-block">Configure here the <strong>customRanking</strong> setting your your Algolia indices.</p>
                        <table>
                            <tr data-order="-1">
                                <th></th>
                                <th>Name</th>
                                <th>Meta key</th>
                                <th>Enable Custom Ranking</th>
                                <th>Custom Ranking Sort</th>
                            </tr>

                            <?php $i = 0; $n = 0; ?>
                            <?php foreach (get_post_types() as $type) : ?>
                                <?php foreach (get_meta_key_list($type) as $meta_key) : ?>
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
                                            <td><img width="10" src="<?php echo plugin_dir_url(__FILE__); ?>../imgs/move.png"></td>
                                            <td><?php echo $type; ?></td>
                                            <td><?php echo $meta_key; ?></td>
                                            <td>
                                                <input type="checkbox"
                                                       name="TYPES[<?php echo $type; ?>][METAS][<?php echo $meta_key; ?>][CUSTOM_RANKING]"
                                                    <?php checked(isset($algolia_registry->metas[$type])
                                                        && in_array($meta_key, array_keys($algolia_registry->metas[$type]))
                                                        && $algolia_registry->metas[$type][$meta_key]["custom_ranking"]); ?>
                                                    />
                                            </td>
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
                                                </select>
                                            </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                            <?php if ($n == 0): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center">You first need to define additional attributes. <span onclick="selectTab('#extra-metas');" style="vertical-align: inherit;" class="button button-secondary">Click here to do it</span></td>
                                </tr>
                            <?php endif; ?>
                        </table>
                        <div class="content-item">
                            <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                        </div>
                    </div>
                </div>
            </form>
            </div>

        <div class="tab-content" id="taxonomies">
            <form action="<?php echo site_url(); ?>/wp-admin/admin-post.php" method="post">
                <input type="hidden" name="action" value="update_indexable_taxonomies">
                <div class="content-wrapper" id="customization">
                    <div class="content">
                        <p class="help-block">Configure here the taxonomies you want to include in your Algolia records.</p>
                        <table>
                            <tr data-order="-1">
                                <th>Enabled</th>
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
                                    if (is_array($algolia_registry->conjunctive_facets) && in_array($tax, array_keys($algolia_registry->conjunctive_facets)))
                                        $order = $algolia_registry->conjunctive_facets[$tax]['order'];
                                    if (is_array($algolia_registry->disjunctive_facets) && in_array($tax, array_keys($algolia_registry->disjunctive_facets)))
                                        $order = $algolia_registry->disjunctive_facets[$tax]['order'];
                                ?>
                                <?php if ($order != -1): ?>
                                <tr data-order="<?php echo $order; ?>">
                                <?php else: ?>
                                <tr data-order="<?php echo (10000 + $i); $i++; ?>">
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
                                    <td>
                                        <?php echo $tax; ?>
                                    </td>
                                    <td>
                                        <input type="checkbox"
                                               value="facetable"
                                            <?php checked((is_array($algolia_registry->conjunctive_facets) && in_array($tax, array_keys($algolia_registry->conjunctive_facets)))
                                                || (is_array($algolia_registry->disjunctive_facets) && in_array($tax, array_keys($algolia_registry->disjunctive_facets)))
                                            ) ?>
                                               name="TAX[<?php echo $tax; ?>][FACET]">
                                    </td>
                                    <td>
                                        <select name="TAX[<?php echo $tax; ?>][FACET_TYPE]">
                                                <option  value="conjunctive">Conjunctive</option>
                                            <?php if (is_array($algolia_registry->disjunctive_facets) && in_array($tax, array_keys($algolia_registry->disjunctive_facets))): ?>
                                                <option selected="selected" value="disjunctive">Disjunctive</option>
                                            <?php else: ?>
                                                <option value="disjunctive">Disjunctive</option>
                                            <?php endif; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" value="<?php echo (isset($algolia_registry->indexable_tax[$tax]) ? $algolia_registry->indexable_tax[$tax]['name'] : "") ?>" name="TAX[<?php echo $tax; ?>][NAME]">
                                        <img width="10" src="<?php echo plugin_dir_url(__FILE__); ?>../imgs/move.png">
                                    </td>
                                </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </table>

                        <div class="content-item">
                            <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                        </div>
                        </table>
                    </div>
                </div>
            </form>
        </div>
    <?php endif; ?>
    </div>
</div>