<?php
$langDomain         = "algolia";
$algolia_registry   = \Algolia\Core\Registry::getInstance();
$template_helper    = new Algolia\Core\TemplateHelper();
$current_template   = $template_helper->get_current_template();

$move_icon_url      = plugin_dir_url(__FILE__) . '../imgs/move.png';

$need_to_reindex    = $algolia_registry->need_to_reindex;

/**
 * Get config
 */

$excluded_types = $algolia_registry->excluded_types;
$facet_types = array_merge(array("conjunctive" => "Conjunctive", "disjunctive" => "Disjunctive"), $current_template->facet_types);
$facetTypes = array();

foreach ($facet_types as $key => $value)
{
    $typeObj = new stdClass();
    $typeObj->key = $key;
    $typeObj->value = $value;

    $facetTypes[] = $typeObj;
}

/*** Get Types ***/

$types = array();

foreach (get_post_types() as $type)
{
    if (in_array($type, $excluded_types))
        continue;

    $count = wp_count_posts($type)->publish;

    if ($count == 0)
        continue;

    $typeObj = new stdClass();
    $typeObj->name = $type;
    $typeObj->count = $count;
    $typeObj->label = $type.' ('.$count.')';
    $types[] = $typeObj;
}


/**
 * Get Metas
 */
$attributes = array();

foreach ($types as $type)
{
    //if (is_array($algolia_registry->indexable_types) && in_array($type, array_keys($algolia_registry->indexable_types)))
    //{
        $type_count = floor(get_meta_key_list_count($type->name) / 1000);


        for ($offset = 0; $offset <= $type_count; $offset++)
        {
            $list = get_meta_key_list($type->name, $offset * 1000, 1000);

            foreach ($list as $elt)
            {
                $attributeObj = new stdClass();
                $attributeObj->name = $elt;
                $attributeObj->group = 'Meta: '.$type->name;

                $attributes[$elt] = $attributeObj;
            }
        }
    //}
}

$taxonomies = array_values(get_taxonomies());

foreach ($taxonomies as $taxonomy)
{
    $attributeObj = new stdClass();
    $attributeObj->name = $taxonomy;
    $attributeObj->group = 'Taxonomy';

    $attributes[] = $attributeObj;
}

$extras = array("title","h1","h2","h3","h4","h5","h6","text","content", "author");

foreach ($extras as $extra)
{
    $attributeObj = new stdClass();
    $attributeObj->name = $extra;
    $attributeObj->group = 'Record attribute';

    $attributes[] = $attributeObj;
}

ksort($attributes);

$attributes = array_values($attributes);

?>

<?php

if (function_exists('curl_version') == false)
{
?>
    <div>
        <h1>Algolia Search : Errors</h1>
        <ul>
            <li>You need to have <b>curl</b> and <b>php5-curl</b> installed</li>
        </ul>
    </div>
<?php
    return;
}

?>

<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.3/angular.min.js"></script>
<script src="https://cdn.jsdelivr.net/g/angular.ui-sortable"></script>

<div id="algolia-settings" ng-app="algoliaSettings" class="wrap" ng-controller="algoliaController">

    <a target="_blank" href="//algolia.com/dashboard" class="header-button" id="dashboard-link">Go to Algolia dashboard</a>

    <?php if ($algolia_registry->validCredential) : ?>
    <h2>
        Algolia Search
        <button type="button" class="button <?php echo (! $need_to_reindex ? "button-secondary" : "button-primary"); ?> " id="algolia_reindex" name="algolia_reindex">
            <i class="dashicons dashicons-upload"></i>
            <?php echo (! $need_to_reindex ? "Reindex data" : "Reindexing Needed"); ?>
            <span class="record-count"></span>
        </button>
        <em id='last-update' style="color: #444;font-family: 'Open Sans',sans-serif;font-size: 13px;line-height: 1.4em;">
            Last update:
            <?php if ($algolia_registry->last_update): ?>
                <?php echo date('Y-m-d H:i:s', $algolia_registry->last_update); ?>
            <?php else: ?>
                <span style="color: red">Never: please re-index your data.</span>
            <?php endif; ?>
        </em>
    </h2>

    <div class="wrapper">
        <?php if ($algolia_registry->validCredential) : ?>
        <div style="clear: both;"</div>
        <?php endif; ?>

        <div id="results-wrapper" style="display: none;">
            <div class="content">
                <div class="show-hide">

                    <div class="content-item">
                        <div>Progression</div>
                        <div style='padding: 5px;'>
                            <div id="reindex-percentage">
                            </div>
                            <div style='clear: both'></div>
                        </div>
                    </div>

                    <div class="content-item">
                        <div>Logs</div>
                        <div style='padding: 5px;'>
                            <table id="reindex-log"></table>
                        </div>
                    </div>

                    <div class="content-item">
                        <button style="display: none;" type="submit" name="submit" id="submit" class="close-results button button-primary">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <h2>
        Algolia Realtime Search
    </h2>
    <?php endif; ?>

    <div class="wrapper">
        <div class="tabs myclearfix">

            <?php if (! $algolia_registry->validCredential) : ?>
            <div data-tab="#credentials" class="title selected">Credentials</div>
            <?php else: ?>
            <div data-tab="#credentials" class="title">Credentials</div>
            <?php endif; ?>

            <?php if ($algolia_registry->validCredential) : ?>

            <div data-tab="#ui"                     class="title selected">UI</div>
            <div data-tab="#autocomplete"           class="title">Autocomplete</div>
            <div data-tab="#instant"                class="title">Instant</div>
            <div data-tab="#ranking"                class="title">Ranking</div>
            <div data-tab="#advanced"               class="title">Advanced</div>

            <?php endif; ?>
            <div style="clear:both"></div>
        </div>

        <?php include __DIR__ . '/tabs/credentials.php'; ?>

        <?php if ($algolia_registry->validCredential) : ?>

            <?php include __DIR__ . '/tabs/ui.php'; ?>
            <?php include __DIR__ . '/tabs/autocomplete.php'; ?>
            <?php include __DIR__ . '/tabs/instant.php'; ?>
            <?php include __DIR__ . '/tabs/ranking.php'; ?>
            <?php include __DIR__ . '/tabs/advanced.php'; ?>

        <?php endif; ?>
    </div>
</div>

<script>
    angular.module('algoliaSettings', []).controller('algoliaController', ['$scope', function($scope) {
        $scope.types                            = <?php echo json_encode($types); ?>;
        $scope.attributes                       = <?php echo json_encode($attributes); ?>;

        $scope.app_id                           = "";
        $scope.search_key                       = "";
        $scope.admin_key                        = "";
        $scope.index_prefix                     = "";

        $scope.search_input_selector            = "";
        $scope.template                         = "";

        $scope.number_by_page                   = "";
        $scope.instant_jquery_selector          = "";

        $scope.autocompleteTypes                = [];
        $scope.autocomplete_type_selected       = null;

        $scope.additionalAttributes             = [];
        $scope.additional_attribute_selected    = null;

        $scope.instantTypes                     = [];
        $scope.instant_type_selected            = null;

        $scope.attributesToIndex                = [];
        $scope.attribute_to_index_selected      = null;

        $scope.customRankings                   = [];
        $scope.custom_ranking_selected          = null;

        $scope.facet_selected                   = null;
        $scope.facets                           = [];

        $scope.orderedTab   = [{key: 'ordered',value: 'Ordered'},{key: 'unordered',value: 'Unordered'}];
        $scope.sortTab      = [{key: 'asc',value: 'Ascending'},{key: 'desc',value: 'Descending'}];
        $scope.facetTypes   = <?php echo json_encode($facetTypes); ?>;

        $scope.add = function (tab, item, type) {
            var obj = undefined;

            if (tab.filter(function (filteredObj) { return filteredObj.name == item.name }).length > 0) {
                return;
            }

            if (type == 'autocomplete_type') {
                obj = { name: item.name, count: item.count, nb_results_by_section: 3, label: "" };
            }

            if (type == 'attribute_to_index') {
                obj = { name: item.name, group: item.group, ordered: 'ordered' };
            }

            if (type == 'custom_ranking') {
                obj = { name: item.name, group: item.group, sort: 'asc' };
                $scope.add($scope.attributesToIndex, obj, 'attribute_to_index');
            }

            if (type == 'additionnal_section') {
                obj = { name: item.name, group: item.group, nb_results_by_section: 3, label: "" };
            }

            if (type == 'instant_type') {
                obj = { name: item.name, count: item.count, nb_results_by_section: 3, label: ""};
            }

            if (type == 'facet') {
                obj = { name: item.name, group: item.group, type: "conjunctive", label: ""};
                $scope.add($scope.attributesToIndex, obj, 'attribute_to_index');
            }

            tab.push(obj);
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

        $scope.isRemovable = function (attribute) {
            return attribute.group !== "Record attribute";
        };

        $scope.save = function () {
            console.log(angular.toJson($scope.autocompleteTypes));
            console.log(angular.toJson($scope.additionalAttributes));
            console.log(angular.toJson($scope.instantTypes));
            console.log(angular.toJson($scope.attributesToIndex));
            console.log(angular.toJson($scope.customRankings));
            console.log(angular.toJson($scope.facets));
        };
    }]);
</script>