<div class="tab-content" ng-show="!validCredential || current_tab == 'credentials'">
    <div class="content-wrapper" id="account">
        <div class="content">
            <div style="float: left;">
                <div style="padding: 0px 10px;">
                    <h3>Algolia Account</h3>
                    <p class="help-block">Configure here your <a href="https://www.algolia.com">Algolia</a> credentials. You can find them in the "<a href="https://www.algolia.com/licensing">Credentials</a>" section of your dashboard. Don't have an Algolia account yet? <a href="http://www.algolia.com/users/sign_up" target="_blank">Create one here</a>.</p>
                    <?php if ($algolia_registry->validCredential == false && ($algolia_registry->app_id || $algolia_registry->search_key || $algolia_registry->admin_key)) : ?>
                        <p class="warning">Your credentials are not valid</p>
                    <?php endif; ?>
                    <div class="content-item">
                        <label for="algolia_app_id">Application ID</label>
                        <div>
                            <input type="text" ng-model="app_id">
                        </div>
                        <p class="description">Your Algolia APPLICATION ID.</p>
                    </div>
                    <div class="content-item">
                        <label for="algolia_search_api_key">Search-Only API Key</label>
                        <div>
                            <input type="text" ng-model="search_key">
                        </div>
                        <p class="description">Your Algolia search-only API KEY (public).</p>
                    </div>
                    <div class="content-item">
                        <label for="algolia_api_key">Admin API Key</label>
                        <div>
                            <input type="password" ng-model="admin_key">
                        </div>
                        <p class="description">Your Algolia ADMIN API KEY.</p>
                    </div>
                    <div class="content-item">
                        <label for="algolia_index_name">Index names prefix</label>
                        <div>
                            <input type="text" ng-model="index_prefix" placeholder="wordpress_">
                        </div>
                        <p class="description">This value will prepend all the index names.</p>
                    </div>
                </div>

                <div class="content-item">
                    <button ng-click="save()" class="button button-primary">Save Changes</button>
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

            <div style="padding: 0px 10px;">
                <h3>Template <span class="h3-info">(includes html, js and css needed to display the results for both the auto-completion menu and the instant search results page)</span></h3>
                <p class="help-block">Select the template you want to use to display the search results. You can either use one of the 2 sample templates or build your own.</p>
                <div class="content-item">
                    <div class="theme-browser">
                        <div class="themes">
                            <div class="theme active" ng-repeat="template in templates">
                                <label for="{{template.dir}}">
                                    <div class="theme-screenshot">
                                            <img class="screenshot instant" ng-show="template.screenshot" ng-src="{{template.screenshot}}">
                                            <div class="no-screenshot screenshot instant" ng-hide="template.screenshot">No screenshot</div>
                                    </div>
                                    <div class="theme-name">
                                        {{template.name}}
                                        <input type="radio" id="{{template.dir}}" value="{{template.dir}}" ng-model="$parent.template_dir" name='template'/>
                                    </div>
                                    <div>{{template.description}}</div>
                                </label>
                            </div>
                        </div>
                        <div style="clear: both"></div>
                    </div>
                    <div class="content-item">
                        <button ng-click="save()" class="button button-primary">Save Changes</button>
                    </div>
                </div>
            </div>

            <div style="padding: 0px 10px;">
                <h3>Administration</h3>
                <p class="help-block">Use this section to export, import or reset your configuration.</p>
                <div class="content-item" style="float: left; margin-right: 30px;">
                    <div data-value="export_config" name="submit" id="export" class="do-submit button button-primary">Export configuration</div>
                </div>
                <h4 style="float: left; margin-right: 30px;">or</h4>
                <div class="content-item" style="float: left; margin-right: 30px;">
                    <form action="<?php echo site_url(); ?>/wp-admin/admin-post.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_settings">
                        <input type="submit" name="submit" id="submit" class="button button-primary" value="Import configuration" />
                        <input type="file" name="import" />
                    </form>
                </div>
                <div class="content-item" style="float: right">
                    <div data-form="<?php echo site_url(); ?>/wp-admin/admin-post.php" data-value="reset_config_to_default" name="submit" id="reset-config" class="button button-primary">Reset configuration</div>
                </div>
            </div>

            <div style="clear: both;"></div>
        </div>
    </div>
</div>