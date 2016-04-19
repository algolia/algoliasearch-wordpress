<?php
$langDomain         = "algolia";
$algolia_registry   = \Algolia\Core\Registry::getInstance();
$template_helper    = new Algolia\Core\TemplateHelper();

$move_icon_url      = plugin_dir_url(__FILE__) . '../imgs/move.png';

$need_to_reindex    = $algolia_registry->need_to_reindex;

/**
 * Get config
 */

$excluded_types = $algolia_registry->excluded_types;
$facet_types = array_merge(array("conjunctive" => "Conjunctive", "disjunctive" => "Disjunctive", "slider" => "Slider"));
$facetTypes = array();
$templates = $template_helper->availableTemplates();

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
    if (in_array($type, $excluded_types) || $type === 'product_variation')
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
$attributes_additionals_sections = array();
$typesForAttributes = $types;

if (count(array_filter($types, function ($item) { return $item->name === 'product';})))
{
    $product_variation = new stdClass();
    $product_variation->name = 'product_variation';
    $typesForAttributes[] = $product_variation;
}


foreach ($typesForAttributes as $type)
{
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
            $attributes_additionals_sections[$elt] = $attributeObj;
        }
    }
}

$taxonomies = array_values(get_taxonomies());

foreach ($taxonomies as $taxonomy)
{
    $attributeObj = new stdClass();
    $attributeObj->name = $taxonomy;
    $attributeObj->group = 'Taxonomy';

    $attributes[$taxonomy] = $attributeObj;
    $attributes_additionals_sections[$taxonomy] = $attributeObj;
}

$attributes_additionals_sections = $attributes;

$extras = array('post_title', 'post_content', 'display_name',
    'post_author', 'post_date', 'post_excerpt', 'post_name', 'post_modified', 'post_parent', 'menu_order', 'post_type',
    'first_name', 'last_name', 'user_login', 'permalink', 'featureImage');

foreach ($extras as $extra)
{
    $attributeObj = new stdClass();
    $attributeObj->name = $extra;
    $attributeObj->group = 'Record attribute';

    $attributes[$extra] = $attributeObj;

    if ($extra == "author")
        $attributes_additionals_sections[$extra] = $attributeObj;
}

ksort($attributes);
ksort($attributes_additionals_sections);

$attributes = array_values($attributes);
$attributes_additionals_sections = array_values($attributes_additionals_sections);

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

<div>
    <div style="display: none;" id="search_input_selector_helper_wrapper">
        <iframe id="search_input_grabber" width="70%;" height="600px;" style="float: left; display: inline;"></iframe>
        <div id="possible_inputs" style="width: 25%; float: left; display: inline;"></div>
    </div>

    <div style="display: none;" id="instant_jquery_selector_helper_wrapper">
        <iframe id="instant_zone_grabber" width="70%;" height="600px;" style="float: left; display: inline;"></iframe>
        <div id="possible_divs" style="width: 25%; float: left; display: inline;"></div>
    </div>
</div>

<div id="algolia-settings" ng-app="algoliaSettings" class="wrap" ng-controller="algoliaController">

    <a target="_blank" href="//algolia.com/dashboard" class="header-button" id="dashboard-link">Go to Algolia dashboard</a>

    <?php if ($algolia_registry->validCredential) : ?>
    <h2>
        Algolia Search
        <button type="button" class="button <?php echo (! $need_to_reindex ? "button-secondary" : "button-primary"); ?> " id="algolia_reindex" name="algolia_reindex">
            <i class="dashicons dashicons-upload"></i>
            Reindex data
            <span class="record-count"></span>
        </button>
        <?php if ($need_to_reindex): ?>
            <span id="algolia_reindex_hint" style="color: red; font-size: 13px;">Reindexing required</span>
        <?php endif; ?>
        <?php if ($algolia_registry->last_update): ?>
        <em id='last-update' style="color: #444; font-size: 13px;">
            Last indexing:
            <?php echo date('Y-m-d H:i:s', $algolia_registry->last_update); ?>
        </em>
        <?php endif; ?>
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

            <div ng-click="changeTab('credentials')"                            class="title {{current_tab == 'credentials' ? 'selected' :''}}">Credentials &amp; Template</div>
            <div ng-show="validCredential" ng-click="changeTab('ranking')"      class="title {{current_tab == 'ranking' ? 'selected' :''}}">Attributes &amp; Ranking</div>
            <div ng-show="validCredential" ng-click="changeTab('autocomplete')" class="title {{current_tab == 'autocomplete' ? 'selected' :''}}">Auto-complete Menu</div>
            <div ng-show="validCredential" ng-click="changeTab('instant')"      class="title {{current_tab == 'instant' ? 'selected' :''}}">Search Results Page</div>
            <div ng-show="validCredential" ng-click="changeTab('advanced')"     class="title {{current_tab == 'advanced' ? 'selected' :''}}">Advanced</div>

            <div style="clear:both"></div>
        </div>

        <?php include __DIR__ . '/tabs/credentials.php'; ?>

        <?php if ($algolia_registry->validCredential) : ?>

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
        $scope.attributes_additionals_sections  = <?php echo json_encode($attributes_additionals_sections); ?>;
        $scope.templates                        = <?php echo json_encode($templates); ?>;

        $scope.app_id                           = "<?php echo $algolia_registry->app_id; ?>";
        $scope.search_key                       = "<?php echo $algolia_registry->search_key; ?>";
        $scope.admin_key                        = "<?php echo $algolia_registry->admin_key; ?>";
        $scope.index_prefix                     = "<?php echo $algolia_registry->index_prefix; ?>";

        $scope.enable_truncating                = Boolean(<?php echo $algolia_registry->enable_truncating; ?>);
        $scope.truncate_size                    = <?php echo $algolia_registry->truncate_size; ?>;

        $scope.search_input_selector            = "<?php echo str_replace("\\", "",$algolia_registry->search_input_selector); ?>";
        $scope.template_dir                     = "<?php echo $algolia_registry->template_dir; ?>";

        $scope.number_by_page                   = <?php echo $algolia_registry->number_by_page; ?>;
        $scope.instant_jquery_selector          = "<?php echo $algolia_registry->instant_jquery_selector; ?>";


        $scope.autocompleteTypes                = <?php echo json_encode($algolia_registry->autocompleteTypes); ?>;
        $scope.autocomplete_type_selected       = null;

        $scope.additionalAttributes             = <?php echo json_encode($algolia_registry->additionalAttributes); ?>;

        $scope.additional_attribute_selected    = null;

        $scope.instantTypes                     = <?php echo json_encode($algolia_registry->instantTypes); ?>;
        $scope.instant_type_selected            = null;

        $scope.attributesToIndex                = <?php echo json_encode($algolia_registry->attributesToIndex); ?>;
        $scope.attribute_to_index_selected      = null;

        $scope.customRankings                   = <?php echo json_encode($algolia_registry->customRankings); ?>;
        $scope.custom_ranking_selected          = null;

        $scope.facets                           = <?php echo json_encode($algolia_registry->facets); ?>;
        $scope.facet_selected                   = null;

        $scope.sorts                            = <?php echo json_encode($algolia_registry->sorts); ?>;
        $scope.sort_selected                    = null;

        $scope.orderedTab   = [{key: 'ordered', value: 'Ordered'},{key: 'unordered', value: 'Unordered'}];
        $scope.sortTab      = [{key: 'asc',  value: 'Ascending'},{key: 'desc', value: 'Descending'}];
        $scope.yesNo        = [{key: true, value: 'Yes'},{key: false, value: 'No'}];
        $scope.facetTypes   = <?php echo json_encode($facetTypes); ?>;

        $scope.validCredential = Boolean(<?php echo $algolia_registry->validCredential; ?>);

        $scope.current_tab = "";
        $scope.save_message = "";

        $scope.changeTab = function (tab) {
            $scope.current_tab = tab;
            location.hash = tab;
        };

        $scope.changeTab(window.location.hash != "" ? location.hash.substring(1) : "credentials");

        $scope.add = function (tab, item, type) {
            var obj = undefined;

            if (type !== "sort" && tab.filter(function (filteredObj) { return filteredObj.name == item.name }).length > 0) {
                return;
            }

            if (type === "sort" && tab.filter(function (filteredObj) { return filteredObj.name == item.name }).length > 1) {
                return;
            }

            if (type == 'autocomplete_type') {
                obj = { name: item.name, count: item.count, nb_results_by_section: 3, label: "" };
            }

            if (type == 'attribute_to_index') {
                obj = { name: item.name, group: item.group, ordered: 'ordered', retrievable: true, searchable: true };
            }

            if (type == 'custom_ranking') {
                obj = { name: item.name, group: item.group, sort: 'asc' };
                $scope.add($scope.attributesToIndex, obj, 'attribute_to_index');
            }

            if (type == 'sort') {
                obj = { name: item.name, group: item.group, sort: 'asc', label: item.label };
                $scope.add($scope.attributesToIndex, obj, 'attribute_to_index');
            }

            if (type == 'additionnal_section') {
                obj = { name: item.name, group: item.group, nb_results_by_section: 3, label: "" };
                $scope.add($scope.facets, obj, 'facet');
            }

            if (type == 'instant_type') {
                obj = { name: item.name, count: item.count, label: ""};
            }

            if (type == 'facet') {
                console.log(item);
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

        $scope.save = function () {
            var settings_name = [
                'autocompleteTypes', 'additionalAttributes', 'instantTypes', 'attributesToIndex',
                'customRankings', 'facets', 'app_id', 'search_key', 'admin_key', 'index_prefix', 'enable_truncating',
                'truncate_size', 'search_input_selector', 'template_dir', 'number_by_page', 'instant_jquery_selector',
                'sorts'
            ];

            var newSettings = {};

            for (var i = 0; i < settings_name.length; i++)
                newSettings[settings_name[i]] = angular.copy($scope[settings_name[i]]);

            algoliaBundle.$.ajax({
                method: "POST",
                url: '<?php echo site_url(); ?>' + '/wp-admin/admin-post.php',
                data: { action: "update_settings", data: JSON.stringify(newSettings), algolia_submit_admin_form: "<?php echo wp_create_nonce('algolia_submit_admin_form'); ?>" },
                success: function (result) {
                    window.location.reload();
                }
            });
        };
    }]).value('uiSortableConfig',{})
        .directive('uiSortable', [
            'uiSortableConfig', '$timeout', '$log',
            function(uiSortableConfig, $timeout, $log) {

                return {
                    require: '?ngModel',
                    scope: {
                        ngModel: '=',
                        uiSortable: '='
                    },
                    link: function(scope, element, attrs, ngModel) {

                        var fixHelper = function(e, ui) {
                            ui.children().each(function() {
                                algoliaBundle.$(this).width(algoliaBundle.$(this).width());
                            });
                            return ui;
                        };

                        algoliaBundle.$(element).sortable({
                            containment: "parent",
                            helper: fixHelper,
                            tolerance: 'pointer',
                            update: function(event, ui) {
                                var item = ngModel.$modelValue.splice(ui.item.sortable.index, 1);

                                if (ui.item.sortable.index < ui.item.index() - 1)
                                    ngModel.$modelValue.splice(ui.item.index() - 1, 0, item[0]);
                                else
                                    ngModel.$modelValue.splice(ui.item.index(), 0, item[0]);
                            },
                            start: function(event, ui) {
                                ui.item.sortable.index = ui.item.index();
                            }
                        });
                    }
                };
            }
        ]);
</script>