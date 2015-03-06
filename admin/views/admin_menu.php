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
        <input type="hidden" name="action" value="update_indexable_types">
        <div class="wrapper" id="customization">
            <div class="title"><?php esc_html_e('Types', $langDomain); ?></div>
            <div class="content">
                <?php foreach (get_post_types() as $type) : ?>
                    <div class="content-item">
                        <div>
                            <input type="checkbox"
                                   name="TYPES[]"
                                   value="<?php echo $type; ?>"
                                   <?php checked(is_array($algolia_registry->indexable_types) && in_array($type, $algolia_registry->indexable_types)) ?>
                                >
                            <?php echo $type; ?>
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
                           name="TAX[]"
                           value="<?php echo $tax; ?>"
                        <?php checked(is_array($algolia_registry->indexable_tax) && in_array($tax, $algolia_registry->indexable_tax)) ?>
                        >
                    <?php echo $tax; ?>
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