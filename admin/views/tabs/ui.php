<div class="tab-content" ng-show="validCredential && current_tab == 'ui'">
    <div class="content-wrapper">
        <div class="content">
            <div class="content-item">
                <h3>Search Bar</h3>
                <p class="help-block">Configure here the DOM selector used to customize your search input.</p>
                <label for="search-input-selector">Search input</label>
                <div>
                    <input type="text" ng-model="search_input_selector" placeholder="[name='s']">
                    <p class="description">The jQuery selector used to select your search bar.</p>
                </div>
            </div>

            <h3>Results template <span class="h3-info">(includes html, js and css needed to display the results for both the auto-completion menu and the instant search results page)</span></h3>
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
                    <button ng-click="save()" class="button button-primary">Save Changes</button>  <p ng-show="save_message !== ''" class="help-block">{{save_message}}</p>
                </div>
            </div>
        </div>
    </div>
</div>