<div class="tab-content" ng-show="validCredential && current_tab == 'ranking'">
    <div class="content-wrapper" id="customization">
        <div class="content">
            <h3>Attributes To index</h3>
            <p class="help-block">
                Attributes
            </p>
            <table>
                <tr data-order="-1">
                    <th style="width: 400px;">Name</th>
                    <th>Searchable</th>
                    <th>Ordered</th>
                    <th>Retrievable</th>
                    <th></th>
                </tr>

                <tbody ui-sortable ng-model="attributes">
                    <tr ng-repeat="attribute in attributesToIndex">
                        <td style="width: 400px;">
                            {{attribute.name}} ({{attribute.group}})
                        </td>
                        <td>
                            <select ng-options="item.key as item.value for item in yesNo" ng-model="attribute.searchable"></select>
                        </td>
                        <td>
                            <select ng-show="attribute.searchable" ng-options="item.key as item.value for item in orderedTab" ng-model="attribute.ordered"></select>
                        </td>
                        <td>
                            <select ng-options="item.key as item.value for item in yesNo" ng-model="attribute.retrievable"></select>
                        </td>
                        <td>
                            <div style="float: right;">
                                <button ng-click="remove(attributesToIndex, attribute)">Remove</button>
                                <img width="10" src="<?php echo $move_icon_url; ?>" style="margin-top: 10px; margin-left: 5px;" />
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="content-item">
                <select ng-options="item as item.name group by item.group for item in attributes" ng-model="attribute_to_index_selected">

                </select>
                <button  ng-click="add(attributesToIndex, attribute_to_index_selected, 'attribute_to_index')">Add</button>
            </div>

            <h3>Custom Ranking</h3>
            <p class="help-block">
                Custom Ranking
            </p>
            <table>
                <tr data-order="-1">
                    <th style="width: 400px;">Name</th>
                    <th>Sort Order</th>
                    <th></th>
                </tr>

                <tbody ui-sortable ng-model="customRankings">
                    <tr ng-repeat="attribute in customRankings">
                        <td style="width: 400px;">
                            {{attribute.name}} ({{attribute.group}})
                        </td>
                        <td>
                            <select ng-options="item.key as item.value for item in sortTab" ng-model="attribute.sort"></select>
                        </td>
                        <td>
                            <div style="float: right;">
                                <button ng-click="remove(customRankings, attribute)">Remove</button>
                                <img width="10" src="<?php echo $move_icon_url; ?>" style="margin-top: 10px; margin-left: 5px;" />
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="content-item">
                <select ng-options="item as item.name group by item.group for item in attributes" ng-model="custom_ranking_selected">

                </select>
                <button  ng-click="add(customRankings, custom_ranking_selected, 'custom_ranking')">Add</button>
            </div>

            <div class="content-item">
                <button ng-click="save()" class="button button-primary">Save Changes</button>
            </div>
        </div>
    </div>
</div>