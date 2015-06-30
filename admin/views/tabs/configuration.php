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
                    <h3>Instant Search</h3>
                    <p class="help-block">Configure here settings for instant search.</p>
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
                <h3>Results template <span class="h3-info">(includes html, js and css needed to display the results for both autocomplete and instant search)</span></h3>
                <p class="help-block">Select the theme you want to use to display the search results. You can either use one of the 2 sample themes or build your own.</p>
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