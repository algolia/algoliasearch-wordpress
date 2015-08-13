<div class="tab-content" ng-show="validCredential && current_tab == 'autocomplete'">
    <div class="content-wrapper" id="customization">
        <div class="content">
            <h3>Autocomplete Types</h3>
            <p class="help-block">
                Configure here the Wordpress types you want autocomplete in. The order of this setting reflects the order of the sections in the auto-completion menu.
            </p>
            <table>
                <tr data-order="-1">
                    <th style="width: 400px;">Name</th>
                    <th>Auto-completion suggestions</th>
                    <th>Auto-completion menu label</th>
                    <th></th>
                </tr>

                <tr ng-repeat="type in autocompleteTypes">
                    <td style="width: 400px;">
                        {{type.name}} ({{type.count}})
                    </td>
                    <td>
                        <input type="number" ng-model="type.nb_results_by_section">
                    </td>
                    <td style="white-space: nowrap;">
                        <input type="text" ng-model="type.label">
                    </td>
                    <td>
                        <button ng-click="up(autocompleteTypes, type)">&#8593;</button> <button ng-click="down(autocompleteTypes, type)">&#8595;</button>
                        <button ng-click="remove(autocompleteTypes, type)">x</button>
                    </td>
                </tr>
            </table>
            <div class="content-item">
                <select ng-options="item as item.label for item in types" ng-model="autocomplete_type_selected">

                </select>
                <button  ng-click="add(autocompleteTypes, autocomplete_type_selected, 'autocomplete_type')">Add</button>
            </div>

            <h3>Additionnal autocomplete sections</h3>
            <p class="help-block">
                Configure here attributes you want as a autocomplete section. The order of this setting reflects the order of the sections in the auto-completion menu.
            </p>
            <table>
                <tr data-order="-1">
                    <th style="width: 400px;">Name</th>
                    <th>Auto-completion suggestions</th>
                    <th>Auto-completion menu label</th>
                    <th></th>
                </tr>

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
                        <button ng-click="up(additionalAttributes, attribute)">&#8593;</button> <button ng-click="down(additionalAttributes, attribute)">&#8595;</button>
                        <button ng-click="remove(additionalAttributes, attribute)">x</button>
                    </td>
                </tr>
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