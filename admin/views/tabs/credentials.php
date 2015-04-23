<div class="tab-content" id="_credentials">
    <form action="<?php echo site_url(); ?>/wp-admin/admin-post.php" method="post">
        <input type="hidden" name="action" value="update_account_info">
        <div class="content-wrapper" id="account">
            <div class="content">
                <div style="float: left;">
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

                <div style="float: left;">
                    <div style="padding: 0px 10px;">
                        <h3>Reset configuration to default</h3>
                        <p class="help-block">
                            This will set the config back to default except api keys
                        </p>
                        <div class="content-item">
                            <div data-form="<?php echo site_url(); ?>/wp-admin/admin-post.php" data-value="reset_config_to_default" name="submit" id="reset-config" class="button button-danger">Reset</div>
                        </div>
                    </div>
                </div>

                <div style="clear: both;"></div>
            </div>
        </div>
    </form>
</div>