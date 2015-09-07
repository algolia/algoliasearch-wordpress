<div class="tab-content" ng-show="validCredential && current_tab == 'autocomplete'">
    <div class="content-wrapper" id="customization">
        <div class="content">
            <h3>Search Bar</h3>
            <p class="help-block">Configure here the DOM selector used to select your search input.</p>
            <label for="search-input-selector">Search input</label>
            <div>
                <input type="text" ng-model="search_input_selector" placeholder="[name='s']"> <button id="search_input_selector_helper">Help me find this</button>
                <p class="description">The jQuery selector used to select your search bar.</p>
            </div>

            <h3>Enabled Sections</h3>
            <p class="help-block">
                Configure here the Wordpress types you want to display in the autocomplete menu. The order of this setting reflects the order of the sections in the dropdown menu.
            </p>
            <table>
                <tr data-order="-1">
                    <th style="width: 400px;">Name</th>
                    <th>Auto-completion suggestions</th>
                    <th>Auto-completion menu label</th>
                    <th></th>
                </tr>

                <tbody ui-sortable ng-model="autocompleteTypes">
                    <tr ng-repeat="type in autocompleteTypes">
                        <td style="width: 400px;">
                            <img width="10" src="<?php echo $move_icon_url; ?>" style="margin-top: 10px; margin-left: 5px;" />
                            {{type.name}} ({{type.count}})
                        </td>
                        <td>
                            <input type="number" ng-model="type.nb_results_by_section">
                        </td>
                        <td style="white-space: nowrap;">
                            <input type="text" ng-model="type.label">
                        </td>
                        <td>
                            <div style="float: right;">
                                <button ng-click="remove(autocompleteTypes, type)">Remove</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="content-item">
                <select ng-options="item as item.label for item in types" ng-model="autocomplete_type_selected">

                </select>
                <button  ng-click="add(autocompleteTypes, autocomplete_type_selected, 'autocomplete_type')">Add</button>
            </div>

            <h3>Additional Sections</h3>
            <p class="help-block">
                Configure here the additional attributes you want to use as autocomplete sections.
            </p>
            <table>
                <tr data-order="-1">
                    <th style="width: 400px;">Name</th>
                    <th>Auto-completion suggestions</th>
                    <th>Auto-completion menu label</th>
                    <th></th>
                </tr>

                <tbody ui-sortable ng-model="additionalAttributes">
                    <tr ng-repeat="attribute in additionalAttributes">
                        <td style="width: 400px;">
                            {{attribute.name}} ({{attribute.group}})
                        </td>
                        <td>
                            <input type="number" ng-model="attribute.nb_results_by_section">
                        </td>
                        <td style="white-space: nowrap;">
                            <input type="text" ng-model="attribute.label">
                        </td>
                        <td>
                            <div style="float: right;">
                                <button ng-click="remove(additionalAttributes, attribute)">Remove</button>
                                <img width="10" src="<?php echo $move_icon_url; ?>" style="margin-top: 10px; margin-left: 5px;" />
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="content-item">
                <select ng-options="item as item.name group by item.group for item in attributes_additionals_sections" ng-model="additional_attribute_selected">

                </select>
                <button  ng-click="add(additionalAttributes, additional_attribute_selected, 'additionnal_section')">Add</button>
            </div>

            <div class="content-item">
                <button ng-click="save()" class="button button-primary">Save Changes</button>
            </div>
        </div>
    </div>
</div>