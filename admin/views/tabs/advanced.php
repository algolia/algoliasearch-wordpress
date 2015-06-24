<div class="tab-content" id="_advanced">
    <form action="<?php echo site_url(); ?>/wp-admin/admin-post.php" method="post">
        <input type="hidden" name="action" value="update_advanced_settings" />
        <div class="content-wrapper">
            <div class="content">
                <div class="has-extra-content content-item">
                    <h3>Content Truncation</h3>
                    <p class="help-block">To avoid any error with too large records the posts and pages content will be truncated by default.</p>
                    <div>
                        <input type="checkbox" <?php checked($algolia_registry->enable_truncating); ?> name="ENABLE_TRUNCATING" id="search-input-selector">
                        <label for="enable_truncated">Enable Truncation</label>
                        <p class="description">Enable the content truncation.</p>
                    </div>
                    <div class="show-hide" style="display: none;">
                        <div>
                            <label for="instant_radio_autocomplete_nb_results">Max. content length</label>
                            <input type="number" min="0" value="<?php echo $algolia_registry->truncate_size; ?>" name="TRUNCATE_SIZE">
                            <p class="description">Content will be truncated after that number of bytes.</p>
                        </div>
                    </div>
                </div>
                <div class="content-item">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                </div>
            </div>
    </form>
</div>