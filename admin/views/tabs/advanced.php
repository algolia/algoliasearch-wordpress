<div class="tab-content" ng-show="validCredential && current_tab == 'advanced'">
    <div class="content-wrapper">
        <div class="content">
            <div class="has-extra-content content-item">
                <h3>Content Truncation</h3>
                <p class="help-block">To avoid any error with too large records the posts and pages content will be truncated by default.</p>
                <div>
                    <label for="enable_truncating">
                        <input id="enable_truncating" type="checkbox" ng-model="enable_truncating">
                        Enable Truncation
                    </label>
                    <p class="description">Enable the content truncation.</p>
                </div>
                <div ng-show="enable_truncating">
                    <div>
                        <label for="truncate_size">
                            <input id="truncate_size" type="number" min="0" ng-model="truncate_size">
                            Max. content length
                        </label>
                        <p class="description">Content will be truncated after that number of bytes.</p>
                    </div>
                </div>
            </div>
            <div class="content-item">
                <button ng-click="save()" class="button button-primary">Save Changes</button>
            </div>
        </div>
    </div>
</div>