<div class="tab-content" id="_autocomplete" ng-controller="autocompleteController">
        <input type="hidden" name="action" value="update_indexable_types">
        <div class="content-wrapper" id="customization">
            <div class="content">
                <h3>Autocomplete Types</h3>
                <p class="help-block">
                    Configure here the Wordpress types you want autocomplete in. The order of this setting reflects the order of the sections in the auto-completion menu.
                </p>
                <table ui-sortable ng-model="autocompleteTypes">
                    <tr data-order="-1">
                        <th>Name</th>
                        <th class="table-col-enabled">Auto-completion suggestions</th>
                        <th>Auto-completion menu label</th>
                        <th></th>
                    </tr>

                    <tr ng-repeat="type in autocompleteTypes">
                        <td>
                            {{type.name}} ({{type.count}})
                        </td>
                        <td class="table-col-enabled">
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
                    <button  ng-click="add()">Add</button>
                </div>
                <div class="content-item">
                    <button ng-click="save()" class="button button-primary">Save Changes</button>
                </div>
            </div>
        </div>
</div>

<script>
    angular.module('algoliaSettings', [])
        .controller('autocompleteController', ['$scope', function($scope) {
            $scope.types = <?php echo json_encode($types); ?>;
            $scope.autocompleteTypes = [];
            $scope.autocomplete_type_selected = null;

            $scope.add = function (tab, type) {
                var obj = undefined;

                if (type == 'autocomplete_type')
                {
                    obj = {
                        name: $scope.autocomplete_type_selected.name,
                        count: $scope.autocomplete_type_selected.count,
                        nb_results_by_section: 3,
                        label: ""
                    };
                }

                $scope.autocompleteTypes.push(obj);
            };

            $scope.remove = function (tab, item) {
                tab.splice(tab.indexOf(item), 1);
            };

            $scope.up = function (tab, item) {
                var current_index = tab.indexOf(item);

                if (current_index > 0) {
                    tab.splice(current_index, 1);
                    tab.splice(current_index - 1, 0, item);
                }
            };

            $scope.down = function (tab, item) {
                var current_index = tab.indexOf(item);

                if (current_index < tab.length - 1) {
                    tab.splice(current_index, 1);
                    tab.splice(current_index + 1, 0, item);
                }
            };

            $scope.save = function () {
                angular.toJson($scope.autocompleteTypes);
            };
        }]);
</script>