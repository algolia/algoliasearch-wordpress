<?php
    $langDomain = "algolia";
    $algolia_registry = \Algolia\Core\Registry::getInstance();
?>

<div id="algolia-settings" class="wrap">
    <h2>Algolia Settings</h2>
    <form action="/wp-admin/admin-post.php" method="post">
        <input type="hidden" name="action" value="update_account_info">
        <div class="wrapper" id="account">
            <div class="title"><?php esc_html_e('Account', $langDomain); ?></div>
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

    <?php if ($algolia_registry->isCredentialsValid) : ?>
    <form action="/wp-admin/admin-post.php" method="post">
        <input type="hidden" name="action" value="update_index_name">
        <div class="wrapper" id="customization">
            <div class="title"><?php esc_html_e('Indexes', $langDomain); ?></div>
            <div class="content">
                <div class="content-item">
                    <div>Index name</div>
                    <div>
                        <input type="text" value="<?php echo $algolia_registry->index_name ?>" name="INDEX_NAME">
                        <button style="vertical-align: middle;" type="button" class="button button-secondary" id="algolia_reindex" name="algolia_reindex">Index Content</button>
                    </div>
                </div>
                <div class="content-item">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                </div>
            </div>
        </div>
    </form>

    #DEBUG
    <form action="/wp-admin/admin-post.php" method="post">
        <input type="hidden" name="action" value="reindex">
        <input type="submit" class="button button-secondary" id="algolia_reindex" name="algolia_reindex" value="Index Content">
    </form>
    #DEBUG

    <form action="/wp-admin/admin-post.php" method="post">
        <input type="hidden" name="action" value="update_type_of_search" />
        <div class="wrapper" id="type_of_search">
            <div class="title"><?php esc_html_e('Type of search', $langDomain); ?></div>
            <div class="content">
                <div class="content-item">
                    Autocomplete <input type="radio"
                                        <?php checked($algolia_registry->type_of_search == 'autocomplete'); ?>
                                        class="instant_radio"
                                        name="TYPE_OF_SEARCH"
                                        value="autocomplete" />
                </div>
                <div class="content-item">
                    <div>
                        Instant Search <input type="radio"
                                              <?php checked($algolia_registry->type_of_search == 'instant'); ?>
                                              class="instant_radio"
                                              name="TYPE_OF_SEARCH"
                                              value="instant" />
                    </div>
                    <div style="margin-left: 50px;">
                        <div id="jquery_selector_wrapper" style="display: none">
                            Jquery Selector <input type="text"
                                                   id="jquery_selector"
                                                   value="<?php echo $algolia_registry->instant_jquery_selector ?>"
                                                   placeholder="#content"
                                                   name="JQUERY_SELECTOR"
                                                   value="" />
                        </div>
                    </div>
                </div>
                <div class="content-item">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                </div>
            </div>
        </div>
    </form>

    <form action="/wp-admin/admin-post.php" method="post">
        <input type="hidden" name="action" value="update_indexable_types">
        <div class="wrapper" id="customization">
            <div class="title"><?php esc_html_e('Types', $langDomain); ?></div>
            <div class="content">
                <?php foreach (get_post_types() as $type) : ?>
                    <div class="content-item">
                        <div>
                            <input type="checkbox"
                                   name="TYPES[<?php echo $type; ?>][SLUG]"
                                   value="<?php echo $type; ?>"
                                   <?php checked(is_array($algolia_registry->indexable_types) && in_array($type, array_keys($algolia_registry->indexable_types))) ?>
                                >
                            <?php echo $type; ?>
                            <input type="text" value="<?php echo (isset($algolia_registry->indexable_types[$type]) ? $algolia_registry->indexable_types[$type] : "") ?>" name="TYPES[<?php echo $type; ?>][NAME]">
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="content-item">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                </div>
            </div>
        </div>
    </form>

    <form action="/wp-admin/admin-post.php" method="post">
        <input type="hidden" name="action" value="update_indexable_taxonomies">
        <div class="wrapper" id="customization">
            <div class="title"><?php esc_html_e('Taxonomies (categories, tags, ...)', $langDomain); ?></div>
            <div class="content">
                <?php foreach (get_taxonomies() as $tax) : ?>
                <div class="content-item">
                    <input type="checkbox"
                           name="TAX[<?php echo $tax; ?>][SLUG]"
                           value="<?php echo $tax; ?>"
                        <?php checked(is_array($algolia_registry->indexable_tax) && in_array($tax, array_keys($algolia_registry->indexable_tax))) ?>
                        >
                    <?php echo $tax; ?>
                    <input type="text" value="<?php echo (isset($algolia_registry->indexable_tax[$tax]) ? $algolia_registry->indexable_tax[$tax] : "") ?>" name="TAX[<?php echo $tax; ?>][NAME]">
                </div>
                <?php endforeach; ?>
                <div class="content-item">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                </div>
            </div>
        </div>
    </form>
    <?php endif; ?>
</div>

<script>
    (function($) {
        $(document).ready(function () {
            $("#algolia_reindex").click(function (e) {

            });
        });
    })(jQuery);
</script>