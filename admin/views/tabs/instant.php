<div class="tab-content" id="_instant">
    <div class="content-wrapper" id="customization">
        <div class="content">
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

            <h3>Instant Types</h3>
            <p class="help-block">
                Configure here the Wordpress types you want autocomplete in. The order of this setting reflects the order of the sections in the auto-completion menu.
            </p>
            <table>
                <tr data-order="-1">
                    <th style="width: 400px;">Name</th>
                    <th></th>
                </tr>

                <tr ng-repeat="type in instantTypes">
                    <td style="width: 400px;">
                        {{type.name}} ({{type.count}})
                    </td>
                    <td>
                        <button ng-click="up(instantTypes, type)">&#8593;</button> <button ng-click="down(instantTypes, type)">&#8595;</button>
                        <button ng-click="remove(instantTypes, type)">x</button>
                    </td>
                </tr>
            </table>
            <div class="content-item">
                <select ng-options="item as item.label for item in types" ng-model="instant_type_selected"></select>
                <button  ng-click="add(instantTypes, instant_type_selected, 'instant_type')">Add</button>
            </div>

            <h3>Facets</h3>
            <p class="help-block">
                Configure here the Facets
            </p>
            <table>
                <tr data-order="-1">
                    <th style="width: 400px;">Name</th>
                    <th>Facet type</th>
                    <th>Label</th>
                    <th></th>
                </tr>

                <tr ng-repeat="facet in facets">
                    <td style="width: 400px;">
                        {{facet.name}} ({{facet.count}})
                    </td>
                    <td>
                        <select ng-options="item.key as item.value for item in facetTypes" ng-model="facet.type"></select>
                    </td>
                    <td style="white-space: nowrap;">
                        <input type="text" ng-model="facet.label">
                    </td>
                    <td>
                        <button ng-click="up(facets, facet)">&#8593;</button> <button ng-click="down(facets, facet)">&#8595;</button>
                        <button ng-click="remove(facets, facet)">x</button>
                    </td>
                </tr>
            </table>
            <div class="content-item">
                <select ng-options="item as item.name group by item.group for item in attributes" ng-model="facet_selected">

                </select>
                <button  ng-click="add(facets, facet_selected, 'facet')">Add</button>
            </div>

            <div class="content-item">
                <button ng-click="save()" class="button button-primary">Save Changes</button>
            </div>
        </div>
    </div>
</div>