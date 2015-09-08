<div class="tab-content" ng-show="validCredential && current_tab == 'instant'">
    <div class="content-wrapper" id="customization">
        <div class="content">
            <div class="content-item">
                <h3>Container</h3>
                <p class="help-block">Configure here the DOM selector used to inject the search results.</p>
            </div>
            <div class="content-item">
                <div>
                    <label for="instant_radio_instant_jquery_selector">DOM selector</label>
                    <input type="text" ng-model="instant_jquery_selector" id="instant_radio_instant_jquery_selector" placeholder="#content"/>  <button id="instant_jquery_selector_helper">Help me find this</button>
                    <p class="description">The jQuery selector used to inject the search results.</p>
                </div>
                <div>
                    <label for="instant_radio_instant_nb_results">Number of results by page</label>
                    <input type="number" min="0" ng-model="number_by_page" id="instant_radio_instant_nb_results">
                    <p class="description">The number of results to display on a results page.</p>
                </div>
            </div>

            <h3>Enabled Types</h3>
            <p class="help-block">
                Configure here the Wordpress types you want to search in and display on the search results page.
            </p>
            <table>
                <tr data-order="-1">
                    <th style="width: 400px;">Name</th>
                    <th></th>
                </tr>

                <tbody ui-sortable ng-model="instantTypes">
                    <tr ng-repeat="type in instantTypes">
                        <td style="width: 400px;">
                            <img width="10" src="<?php echo $move_icon_url; ?>" style="margin-top: 10px; margin-left: 5px;" />
                            {{type.name}} ({{type.count}})
                        </td>
                        <td>
                            <div style="float: right;">
                                <button ng-click="remove(instantTypes, type)">Remove</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="content-item">
                <select ng-options="item as item.label for item in types" ng-model="instant_type_selected"></select>
                <button  ng-click="add(instantTypes, instant_type_selected, 'instant_type')">Add</button>
            </div>

            <h3>Faceting</h3>
            <p class="help-block">
                Configure here the filtering options you want to display on your search results page.
            </p>
            <table>
                <tr data-order="-1">
                    <th style="width: 400px;">Name</th>
                    <th>Type</th>
                    <th>Label</th>
                    <th></th>
                </tr>

                <tbody ui-sortable ng-model="facets">
                    <tr ng-repeat="facet in facets">
                        <td style="width: 400px;">
                            <img width="10" src="<?php echo $move_icon_url; ?>" style="margin-top: 10px; margin-left: 5px;" />
                            {{facet.name}} ({{facet.group}})
                        </td>
                        <td>
                            <select ng-options="item.key as item.value for item in facetTypes" ng-model="facet.type"></select>
                        </td>
                        <td style="white-space: nowrap;">
                            <input type="text" ng-model="facet.label">
                        </td>
                        <td>
                            <div style="float: right;">
                                <button ng-click="remove(facets, facet)">Remove</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="content-item">
                <select ng-options="item as item.name group by item.group for item in attributes" ng-model="facet_selected">

                </select>
                <button  ng-click="add(facets, facet_selected, 'facet')">Add</button>
            </div>

            <h3>Sorts</h3>
            <p class="help-block">
                Configure here the different sorting options you want to offer.
            </p>
            <table>
                <tr data-order="-1">
                    <th style="width: 400px;">Name</th>
                    <th>Sort</th>
                    <th>Label</th>
                    <th></th>
                </tr>

                <tbody ui-sortable ng-model="sorts">
                    <tr ng-repeat="sort in sorts">
                        <td style="width: 400px;">
                            <img width="10" src="<?php echo $move_icon_url; ?>" style="margin-top: 10px; margin-left: 5px;" />
                            {{sort.name}} ({{sort.group}})
                        </td>
                        <td>
                            <select ng-options="item.key as item.value for item in sortTab" ng-model="sort.sort"></select>
                        </td>
                        <td style="white-space: nowrap;">
                            <input type="text" ng-model="sort.label">
                        </td>
                        <td>
                            <div style="float: right;">
                                <button ng-click="remove(sorts, sort)">Remove</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="content-item">
                <select ng-options="item as item.name group by item.group for item in attributes" ng-model="sort_selected">

                </select>
                <button  ng-click="add(sorts, sort_selected, 'sort')">Add</button>
            </div>

            <div class="content-item">
                <button ng-click="save()" class="button button-primary">Save Changes</button>
            </div>
        </div>
    </div>
</div>