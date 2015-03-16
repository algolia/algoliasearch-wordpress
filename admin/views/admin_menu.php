<?php
    $langDomain = "algolia";
    $algolia_registry = \Algolia\Core\Registry::getInstance();
    $theme_helper = new Algolia\Core\ThemeHelper();
?>

<div id="algolia-settings" class="wrap">
    <h2>Algolia Settings</h2>

    <div class="wrapper">
        <button style="vertical-align: middle;" type="button" class="button button-secondary" id="algolia_reindex" name="algolia_reindex">
            Re-index everything
        </button>

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
            <div data-tab="#account" class="title selected">Account</div>
            <?php else: ?>
            <div data-tab="#account" class="title">Account</div>
            <?php endif; ?>

            <?php if ($algolia_registry->validCredential) : ?>

            <div data-tab="#general-settings" class="title">General Settings</div>
            <div data-tab="#type-of-search" class="title selected">Type of search</div>
            <div data-tab="#indexable-types" class="title">Types</div>
            <div data-tab="#extra-metas" class="title">Extra attributes</div>
            <div data-tab="#custom-ranking" class="title">Custom Ranking</div>
            <div data-tab="#taxonomies" class="title">Taxonomies</div>

            <?php endif; ?>
            <div style="clear:both"></div>
        </div>


        <div class="tab-content" id="account">
            <form action="/wp-admin/admin-post.php" method="post">
                <input type="hidden" name="action" value="update_account_info">
                <div class="content-wrapper" id="account">
                    <div class="content">
                        <div class="content-item">
                            <div>Application ID</div>
                            <div><input type="text" value="<?php echo $algolia_registry->app_id ?>" name="APP_ID"></div>
                        </div>
                        <div class="content-item">
                            <div>Search-Only API Key</div>
                            <div><input type="text" value="<?php echo $algolia_registry->search_key ?>" name="SEARCH_KEY"></div>
                        </div>
                        <div class="content-item">
                            <div>Admin API Key</div>
                            <div><input type="text" value="<?php echo $algolia_registry->admin_key ?>" name="ADMIN_KEY"></div>
                        </div>
                        <div class="content-item">
                            <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                        </div>
                    </div>

                </div>
            </form>
        </div>

        <?php if ($algolia_registry->validCredential) : ?>
        <div class="tab-content" id="general-settings">
            <form action="/wp-admin/admin-post.php" method="post">
                <input type="hidden" name="action" value="update_index_name">
                <div class="content-wrapper" id="customization">
                    <div class="content">
                        <div class="content-item">
                            <div>Index name</div>
                            <div>
                                <div>
                                    <input type="text" value="<?php echo $algolia_registry->index_name; ?>" name="INDEX_NAME">
                                </div>
                            </div>
                        </div>
                        <div class="content-item">
                            <div>Search input jquery selector</div>
                            <div>
                                <input type="text" value="<?php echo str_replace("\\", "",$algolia_registry->search_input_selector); ?>" name="SEARCH_INPUT_SELECTOR">
                            </div>
                        </div>
                        <div class="content-item">
                            <div>Theme</div>
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



        <div class="tab-content" id="type-of-search">
            <form action="/wp-admin/admin-post.php" method="post">
                <input type="hidden" name="action" value="update_type_of_search" />
                <div class="content-wrapper" id="type_of_search">
                    <div class="content">
                        <div class="has-extra-content content-item">
                            <div>
                                Autocomplete <input type="radio"
                                                <?php checked($algolia_registry->type_of_search == 'autocomplete'); ?>
                                                class="instant_radio"
                                                name="TYPE_OF_SEARCH"
                                                value="autocomplete" />
                            </div>
                            <div class="show-hide" style="display: none;">
                                <div>
                                    Number of results by category
                                    <input type="number" min="0" value="<?php echo $algolia_registry->number_by_type; ?>" name="NUMBER_BY_TYPE">
                                </div>
                            </div>
                        </div>
                        <div class="has-extra-content content-item">
                            <div>
                                Instant Search <input type="radio"
                                                      <?php checked($algolia_registry->type_of_search == 'instant'); ?>
                                                      class="instant_radio"
                                                      name="TYPE_OF_SEARCH"
                                                      value="instant" />
                            </div>
                            <div class="show-hide" style="display: none;">
                                <div>
                                    Jquery Selector <input type="text"
                                                           id="jquery_selector"
                                                           value="<?php echo str_replace("\\", "", $algolia_registry->instant_jquery_selector); ?>"
                                                           placeholder="#content"
                                                           name="JQUERY_SELECTOR"
                                                           value="" />
                                </div>
                                <div>
                                    Number of results by page
                                    <input type="number" min="0" value="<?php echo $algolia_registry->number_by_page; ?>" name="NUMBER_BY_PAGE">
                                </div>
                            </div>
                        </div>
                        <div class="content-item">
                            <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="tab-content" id="indexable-types">
            <form action="/wp-admin/admin-post.php" method="post">
                <input type="hidden" name="action" value="update_indexable_types">
                <div class="content-wrapper" id="customization">
                    <div class="content">
                        <table style="text-align: center; width: 100%;">
                            <tr data-order="-1">
                                <th></th>
                                <th>Indexable</th>
                                <th>Name</th>
                                <th>Label</th>
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
                                    <img width="10" src="<?php echo plugin_dir_url(__FILE__); ?>../imgs/move.png">
                                </td>
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
            <form action="/wp-admin/admin-post.php" method="post">
                <input type="hidden" name="action" value="update_extra_meta">
                <div class="content-wrapper" id="customization">
                    <div class="content">
                        <table style="text-align: center; width: 100%;">
                            <tr data-order="-1">
                                <th></th>
                                <th>Indexable</th>
                                <th>Facetable</th>
                                <th>Name</th>
                                <th>Meta key</th>
                                <th>Label</th>
                                <th>Type of facet</th>
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
                                            <td><img width="10" src="<?php echo plugin_dir_url(__FILE__); ?>../imgs/move.png"></td>
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
                                            <td>
                                                <input type="checkbox"
                                                       name="TYPES[<?php echo $type; ?>][METAS][<?php echo $meta_key; ?>][FACETABLE]"
                                                       value="1"
                                                    <?php checked(isset($algolia_registry->metas[$type])
                                                        && in_array($meta_key, array_keys($algolia_registry->metas[$type]))
                                                        && $algolia_registry->metas[$type][$meta_key]["facetable"]); ?>
                                                    >
                                            </td>
                                            <td><?php echo $type; ?></td>
                                            <td><?php echo $meta_key; ?></td>
                                            <td>
                                                <input type="text"
                                                       value="<?php echo (isset($algolia_registry->metas[$type][$meta_key]) ? $algolia_registry->metas[$type][$meta_key]["name"] : "") ?>" name="TYPES[<?php echo $type; ?>][METAS][<?php echo $meta_key; ?>][NAME]">
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
            <form action="/wp-admin/admin-post.php" method="post">
                <input type="hidden" name="action" value="custom_ranking">
                <div class="content-wrapper" id="customization">
                    <div class="content">
                        <div class="warning">
                            <div>You have to put extra-attributes as indexable first <span onclick="selectTab('#extra-metas');" style="vertical-align: inherit;" class="button button-secondary">Click here to do it</span></div>
                        </div>
                        <table style="text-align: center; width: 100%;">
                            <tr data-order="-1">
                                <th></th>
                                <th>Name</th>
                                <th>Meta key</th>
                                <th>Enable Custom Ranking</th>
                                <th>Custom Ranking Sort</th>
                            </tr>

                            <?php $i = 0; ?>
                            <?php foreach (get_post_types() as $type) : ?>
                                <?php foreach (get_meta_key_list($type) as $meta_key) : ?>
                                    <?php if (is_array($algolia_registry->indexable_types) && in_array($type, array_keys($algolia_registry->indexable_types))) : ?>
                                        <?php if (isset($algolia_registry->metas[$type])
                                            && in_array($meta_key, array_keys($algolia_registry->metas[$type]))
                                            && $algolia_registry->metas[$type][$meta_key]["indexable"]): ?>

                                            <?php
                                            $order = -1;
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
                        </table>
                        <div class="content-item">
                            <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                        </div>
                    </div>
                </div>
            </form>
            </div>

        <div class="tab-content" id="taxonomies">
            <form action="/wp-admin/admin-post.php" method="post">
                <input type="hidden" name="action" value="update_indexable_taxonomies">
                <div class="content-wrapper" id="customization">
                    <div class="content">
                        <table style="text-align: center; width: 100%;">
                            <tr data-order="-1">
                                <th></th>
                                <th>Indexable</th>
                                <th>Facetable</th>
                                <th>Name</th>
                                <th>Print Name</th>
                                <th>Type of facet</th>
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
                                        <img width="10" src="<?php echo plugin_dir_url(__FILE__); ?>../imgs/move.png">
                                    </td>
                                    <td>
                                        <?php if (in_array($tax, $algolia_registry->extras) == false): ?>
                                        <input type="checkbox"
                                               name="TAX[<?php echo $tax; ?>][SLUG]"
                                               value="<?php echo $tax; ?>"
                                            <?php checked(is_array($algolia_registry->indexable_tax) && in_array($tax, array_keys($algolia_registry->indexable_tax))); ?>
                                            >
                                        <?php else: ?>
                                            <input type="hidden" name="TAX[<?php echo $tax; ?>][SLUG]" value="<?php echo $tax; ?>">
                                        <?php endif; ?>
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
                                        <?php echo $tax; ?>
                                    </td>
                                    <td>
                                        <input type="text" value="<?php echo (isset($algolia_registry->indexable_tax[$tax]) ? $algolia_registry->indexable_tax[$tax]['name'] : "") ?>" name="TAX[<?php echo $tax; ?>][NAME]">
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