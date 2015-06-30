<div class="tab-content" id="_configuration">
    <form action="<?php echo site_url(); ?>/wp-admin/admin-post.php" method="post">
        <input type="hidden" name="action" value="update_ui" />
        <div class="content-wrapper" id="ui">
            <div class="content">
                <div class="content-item">
                    <h3>Search Bar</h3>
                    <p class="help-block">Configure here the DOM selector used to customize your search input.</p>
                    <label for="search-input-selector">Search input</label>
                    <div>
                        <input type="text" value="<?php echo str_replace("\\", "",$algolia_registry->search_input_selector); ?>" name="SEARCH_INPUT_SELECTOR" id="search-input-selector" placeholder="[name='s']">
                        <p class="description">The jQuery selector used to select your search bar.</p>
                    </div>
                </div>
                <div class="content-item">
                    <h3>Search Results Page</h3>
                    <p class="help-block">Configure here your instant search results page.</p>
                </div>
                <div class="content-item">
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
                </div>
                <h3>Results template <span class="h3-info">(includes html, js and css needed to display the results for both the auto-completion menu and the instant search results page)</span></h3>
                <p class="help-block">Select the template you want to use to display the search results. You can either use one of the 2 sample templates or build your own.</p>
                <div class="content-item">
                    <div class="theme-browser">
                        <div class="themes">
                            <?php foreach ($template_helper->available_templates() as $template): ?>
                            <?php if ($template->dir == $algolia_registry->template): ?>
                            <div class="theme active">
                                <?php else: ?>
                                <div class="theme">
                                    <?php endif; ?>
                                    <label for="<?php echo $template->dir; ?>">
                                        <div class="theme-screenshot">
                                            <?php if ($template->screenshot): ?>
                                                <img class="screenshot instant" src="<?php echo $template->screenshot; ?>">
                                            <?php else: ?>
                                                <div class="no-screenshot screenshot instant">No screenshot</div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="theme-name">
                                            <?php echo $template->name; ?>
                                            <input type="radio"
                                                   id="<?php echo $template->dir; ?>"
                                                <?php checked($template->dir == $algolia_registry->template); ?>
                                                   name='template'
                                                   value="<?php echo $template->dir; ?>"/>
                                        </div>
                                        <div><?php echo $template->description; ?></div>
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