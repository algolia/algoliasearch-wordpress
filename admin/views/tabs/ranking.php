<div class="tab-content" ng-show="validCredential && current_tab == 'ranking'">
    <div class="content-wrapper" id="customization">
        <div class="content">
            <h3>Attributes To index</h3>
            <p class="help-block">
                Attributes To index
            </p>
            <table>
                <tr data-order="-1">
                    <th style="width: 400px;">Name</th>
                    <th>Ordered</th>
                    <th></th>
                </tr>

                <tr ng-repeat="attribute in attributesToIndex">
                    <td style="width: 400px;">
                        {{attribute.name}} ({{attribute.group}})
                    </td>
                    <td>
                        <select ng-options="item.key as item.value for item in orderedTab" ng-model="attribute.ordered"></select>
                    </td>
                    <td>
                        <button ng-click="up(attributesToIndex, attribute)">&#8593;</button> <button ng-click="down(attributesToIndex, attribute)">&#8595;</button>
                        <button ng-show="isRemovable(attribute)" ng-click="remove(attributesToIndex, attribute)">x</button>
                    </td>
                </tr>
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

                <tr ng-repeat="attribute in customRankings">
                    <td style="width: 400px;">
                        {{attribute.name}} ({{attribute.group}})
                    </td>
                    <td>
                        <select ng-options="item.key as item.value for item in sortTab" ng-model="attribute.sort"></select>
                    </td>
                    <td>
                        <button ng-click="up(customRankings, attribute)">&#8593;</button> <button ng-click="down(customRankings, attribute)">&#8595;</button>
                        <button ng-click="remove(customRankings, attribute)">x</button>
                    </td>
                </tr>
            </table>
            <div class="content-item">
                <select ng-options="item as item.name group by item.group for item in attributes" ng-model="custom_ranking_selected">

                </select>
                <button  ng-click="add(customRankings, custom_ranking_selected, 'custom_ranking')">Add</button>
            </div>

            <div class="content-item">
                <button ng-click="save()" class="button button-primary">Save Changes</button>  <p ng-show="save_message !== ''" class="help-block">{{save_message}}</p>
            </div>
        </div>
    </div>
</div>