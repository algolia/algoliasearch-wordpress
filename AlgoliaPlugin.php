<?php

class AlgoliaPlugin
{
    private $algolia_registry;
    private $algolia_helper;
    private $indexer;

    public function __construct()
    {
        $this->algolia_registry = \Algolia\Core\Registry::getInstance();

        if ($this->algolia_registry->validCredential)
        {
            $this->algolia_helper   = new \Algolia\Core\AlgoliaHelper(
                $this->algolia_registry->app_id,
                $this->algolia_registry->search_key,
                $this->algolia_registry->admin_key
            );
        }

        $this->indexer = new \Algolia\Core\Indexer();

        add_action('admin_menu',                                array($this, 'add_admin_menu'));

        add_action('admin_post_update_account_info',            array($this, 'admin_post_update_account_info'));
        add_action('admin_post_update_index_name',              array($this, 'admin_post_update_index_name'));
        add_action('admin_post_update_indexable_types',         array($this, 'admin_post_update_indexable_types'));
        add_action('admin_post_update_indexable_taxonomies',    array($this, 'admin_post_update_indexable_taxonomies'));
        add_action('admin_post_update_type_of_search',          array($this, 'admin_post_update_type_of_search'));
        add_action('admin_post_update_extra_meta',              array($this, 'admin_post_update_extra_meta'));
        add_action('admin_post_custom_ranking',                 array($this, 'admin_post_custom_ranking'));

        add_action('admin_post_reindex',                        array($this, 'admin_post_reindex'));

        add_action('admin_enqueue_scripts',                     array($this, 'admin_scripts'));
        add_action('wp_enqueue_scripts',                        array($this, 'scripts'));

        add_action('wp_footer',                                 array($this, 'wp_footer'));

        $this->addExtras();
    }

    private function addExtras()
    {
        $extras = array('type' => 'type');

        /* Avoid unncessary db call */
        if ($this->algolia_registry->extras != $extras)
            $this->algolia_registry->extras = $extras;
    }

    public function add_admin_menu()
    {
        $icon_url = plugin_dir_url(__FILE__) . 'admin/imgs/icon.png';
        add_menu_page('Algolia Settings', 'Algolia', 'manage_options', 'algolia-settings', array($this, 'admin_view'), $icon_url);
    }

    public function admin_view()
    {
        include __DIR__ . '/admin/views/admin_menu.php';
    }

    public function wp_footer()
    {
        include __DIR__ . '/themes/' . $this->algolia_registry->theme . '/templates.php';
    }

    public function scripts()
    {
        if (is_admin())
            return;

        wp_enqueue_style('jquery-ui', plugin_dir_url(__FILE__) . 'lib/jquery/jquery-ui.min.css');
        wp_enqueue_style('algolia_styles', plugin_dir_url(__FILE__) . 'themes/' . $this->algolia_registry->theme . '/styles.css');

        $scripts = array(
            'lib/jquery/jquery-ui.js',
            'lib/algolia/algoliasearch.min.js',
            'lib/hogan/hogan.js',
            'lib/typeahead/typeahead.js'
        );

        foreach ($scripts as $script) {
            wp_register_script($script, plugin_dir_url(__FILE__) . $script, array());
            wp_localize_script($script, 'settings', array());
        }

        $indexes = array();
        $facets = array();

        foreach ($this->algolia_registry->indexable_types as $type => $obj)
        {
            $indexes[] = array('index_name' => $this->algolia_registry->index_name . $type, 'name' => $obj['name'], 'order1' => 0, 'order2' => $obj['order']);

            if (isset($this->algolia_registry->metas[$type]))
                foreach ($this->algolia_registry->metas[$type] as $meta_key => $meta_value)
                    if ($meta_value['facetable'])
                        $facets[] = array('order1' => 1, 'order2' => $meta_value['order'], 'tax' => $meta_key, 'name' => $meta_value['name'] ? $meta_value['name'] : $meta_key, 'type' => $meta_value['type']);
        }

        foreach ($this->algolia_registry->indexable_tax as $tax => $obj)
            $indexes[] = array('index_name' => $this->algolia_registry->index_name . $tax, 'name' => $obj['name'], 'order1' => 1, 'order2' => $obj['order']);

        foreach ($this->algolia_registry->conjunctive_facets as $tax => $obj)
            $facets[] = array('tax' => $tax, 'name' => $obj['name'], 'order1' => 0,'order2' => $obj['order'], 'type' => 'conjunctive');

        foreach ($this->algolia_registry->disjunctive_facets as $tax => $obj)
            $facets[] = array('tax' => $tax, 'name' => $obj['name'], 'order1' => 0,'order2' => $obj['order'], 'type' => 'disjunctive');

        global $facetsLabels;

        $algoliaSettings = array(
            'app_id'                    => $this->algolia_registry->app_id,
            'search_key'                => $this->algolia_registry->search_key,
            'indexes'                   => $indexes,
            'index_name'                => $this->algolia_registry->index_name,
            'type_of_search'            => $this->algolia_registry->type_of_search,
            'instant_jquery_selector'   => str_replace("\\", "", $this->algolia_registry->instant_jquery_selector),
            'facets'                    => $facets,
            'number_by_type'            => $this->algolia_registry->number_by_type,
            'number_by_page'            => $this->algolia_registry->number_by_page,
            'search_input_selector'     => str_replace("\\", "", $this->algolia_registry->search_input_selector),
            'facetsLabels'              => $facetsLabels
        );



        wp_register_script('algolia_main.js', plugin_dir_url(__FILE__) . 'front/main.js', array_merge(array('jquery'), $scripts));
        wp_localize_script('algolia_main.js', 'algoliaSettings', $algoliaSettings);

        wp_enqueue_script('algolia_main.js');

        wp_register_script('theme.js',  plugin_dir_url(__FILE__) . 'themes/' . $this->algolia_registry->theme . '/theme.js', array(), array());
        wp_localize_script('theme.js', 'themesSettings', array());

        wp_enqueue_script('theme.js');

    }

    public function admin_scripts()
    {
        global $batch_count;

        $algoliaAdminSettings = array(
            "types" => array(),
            "batch_count" => $batch_count,
            "site_url" => site_url()
        );

        foreach ($this->algolia_registry->indexable_types as $type => $obj)
            $algoliaAdminSettings["types"][] = array('type' => $type, 'name' => $obj['name'], 'count' => wp_count_posts($type)->publish);

        wp_register_script('jquery-ui', plugin_dir_url(__FILE__) . 'lib/jquery/jquery-ui.js', array_merge(array('jquery')));
        wp_localize_script('jquery-ui', 'algoliaAdminSettings', $algoliaAdminSettings);
        wp_enqueue_script('jquery-ui');

        wp_register_script('admin.js', plugin_dir_url(__FILE__) . 'admin/scripts/admin.js', array_merge(array('jquery')));
        wp_localize_script('admin.js', 'algoliaAdminSettings', $algoliaAdminSettings);
        wp_enqueue_script('admin.js');

        wp_enqueue_style('styles-admin', plugin_dir_url(__FILE__) . 'admin/styles/styles.css');
        wp_enqueue_style('jquery-ui', plugin_dir_url(__FILE__) . 'lib/jquery/jquery-ui.min.css');
    }

    public function admin_post_update_account_info()
    {
        $app_id     = !empty($_POST['APP_ID'])      ? sanitize_text_field($_POST['APP_ID']) : '';
        $search_key = !empty($_POST['SEARCH_KEY'])  ? sanitize_text_field($_POST['SEARCH_KEY']) : '';
        $admin_key  = !empty($_POST['ADMIN_KEY'])   ? sanitize_text_field($_POST['ADMIN_KEY']) : '';
        $index_name = !empty($_POST['INDEX_NAME']) ? sanitize_text_field($_POST['INDEX_NAME']) : '';

        $algolia_helper = new \Algolia\Core\AlgoliaHelper($app_id, $search_key, $admin_key);

        $this->algolia_registry->app_id     = $app_id;
        $this->algolia_registry->search_key = $search_key;
        $this->algolia_registry->admin_key  = $admin_key;
        $this->algolia_registry->index_name = $index_name;

        $algolia_helper->checkRights();

        wp_redirect('admin.php?page=algolia-settings#credentials');
    }

    /**
     *
     */
    public function admin_post_update_indexable_taxonomies()
    {
        $valid_tax = get_taxonomies();

        $taxonomies = array();
        $conjunctive_facets = array();
        $disjunctive_facets = array();

        $i = 0;
        $j = 0;

        if (isset($_POST['TAX']) && is_array($_POST['TAX']))
        {
            foreach ($_POST['TAX'] as $tax)
            {
                if (in_array($tax['SLUG'], $valid_tax) || in_array($tax['SLUG'], array_keys($this->algolia_registry->extras)))
                {
                    $taxonomies[$tax['SLUG']] = array(
                        'name' => $tax['NAME'] == '' ? $tax['SLUG'] : $tax['NAME'],
                        'order' => $i
                    );
                    $i++;
                }


                if (isset($tax['FACET']))
                {
                    if ($tax['FACET_TYPE'] == 'conjunctive')
                        $conjunctive_facets[$tax["SLUG"]] = array('order' => $j, 'name' => $tax["NAME"]);
                    else
                        $disjunctive_facets[$tax["SLUG"]] = array('order' => $j, 'name' => $tax["NAME"]);

                    $j++;
                }
            }
        }


        $this->algolia_registry->indexable_tax = $taxonomies;
        $this->algolia_registry->conjunctive_facets = $conjunctive_facets;
        $this->algolia_registry->disjunctive_facets = $disjunctive_facets;

        $this->algolia_helper->handleIndexCreation();

        $this->indexer->indexTaxonomies();

        wp_redirect('admin.php?page=algolia-settings#taxonomies');
    }

    public function admin_post_update_indexable_types()
    {
        $valid_types = get_post_types();

        $types = array();

        if (isset($_POST['TYPES']) && is_array($_POST['TYPES']))
        {
            $i = 0;

            foreach ($_POST['TYPES'] as $type)
            {
                if (in_array($type['SLUG'], $valid_types))
                {
                    $types[$type['SLUG']] = array(
                        'name' => $type['NAME'] == '' ? $type['SLUG'] : $type['NAME'],
                        'order' => $i
                    );

                    $i++;
                }
            }
        }

        $this->algolia_registry->indexable_types = $types;

        $this->algolia_helper->handleIndexCreation();

        wp_redirect('admin.php?page=algolia-settings#indexable-types');
    }

    public function admin_post_update_type_of_search()
    {
        if (isset($_POST['TYPE_OF_SEARCH']) && in_array($_POST['TYPE_OF_SEARCH'], array('instant', 'autocomplete')))
            $this->algolia_registry->type_of_search = $_POST['TYPE_OF_SEARCH'];

        if (isset($_POST['JQUERY_SELECTOR']))
            $this->algolia_registry->instant_jquery_selector = str_replace('"', '\'', $_POST['JQUERY_SELECTOR']);

        if (isset($_POST['NUMBER_BY_PAGE']) && is_numeric($_POST['NUMBER_BY_PAGE']))
            $this->algolia_registry->number_by_page = $_POST['NUMBER_BY_PAGE'];

        if (isset($_POST['NUMBER_BY_TYPE']) && is_numeric($_POST['NUMBER_BY_TYPE']))
            $this->algolia_registry->number_by_type = $_POST['NUMBER_BY_TYPE'];

        $search_input_selector  = !empty($_POST['SEARCH_INPUT_SELECTOR']) ? $_POST['SEARCH_INPUT_SELECTOR'] : '';
        $theme                  = !empty($_POST['THEME']) ? $_POST['THEME'] : 'default';

        $this->algolia_registry->search_input_selector  = str_replace('"', '\'', $search_input_selector);
        $this->algolia_registry->theme                  = $theme;

        wp_redirect('admin.php?page=algolia-settings#configuration');
    }

    public function admin_post_custom_ranking()
    {
        $indexable_types        = $this->algolia_registry->indexable_types;
        $metas                  = $this->algolia_registry->metas;
        $date_custom_ranking    = array();

        if (isset($_POST['TYPES']) && is_array($_POST['TYPES']))
        {
            $i = 1;

            foreach ($_POST['TYPES'] as $key => $value)
            {
                if ($key == 'date' || (in_array($key, array_keys($indexable_types)) && isset($value["METAS"]) && is_array($value["METAS"])))
                {
                    foreach ($value["METAS"] as $meta_key => $meta_value)
                    {
                        if ($meta_key != "date" && isset($metas[$key][$meta_key]) && $metas[$key][$meta_key]['indexable'])
                        {
                            $metas[$key][$meta_key]['custom_ranking']       = isset($meta_value['CUSTOM_RANKING']) ? 1 : 0;
                            $metas[$key][$meta_key]["custom_ranking_order"] = $meta_value["CUSTOM_RANKING_ORDER"];

                            if ($metas[$key][$meta_key]['custom_ranking'])
                                $metas[$key][$meta_key]["custom_ranking_sort"]  = $i;
                            else
                                $metas[$key][$meta_key]["custom_ranking_sort"]  = 10000;

                            $i++;
                        }

                        if ($meta_key == "date")
                        {
                            $date_custom_ranking['enabled'] = isset($meta_value['CUSTOM_RANKING']) ? 1 : 0;
                            $date_custom_ranking['order'] = $meta_value["CUSTOM_RANKING_ORDER"];

                            if ($date_custom_ranking['enabled'])
                                $date_custom_ranking["sort"]  = $i;
                            else
                                $date_custom_ranking["sort"]  = 10000;

                            $i++;
                        }
                    }
                }
            }

            $this->algolia_registry->date_custom_ranking = $date_custom_ranking;
            $this->algolia_registry->metas = $metas;
        }

        $this->algolia_helper->handleIndexCreation();

        wp_redirect('admin.php?page=algolia-settings#custom-ranking');
    }

    public function admin_post_update_extra_meta()
    {
        $indexable_types = $this->algolia_registry->indexable_types;

        if (isset($_POST['TYPES']) && is_array($_POST['TYPES']))
        {
            $metas = array();

            $i = 0;

            foreach ($_POST['TYPES'] as $key => $value)
            {
                if (in_array($key, array_keys($indexable_types)) && isset($value["METAS"]) && is_array($value["METAS"]))
                {
                    $metas[$key] = array();

                    foreach ($value["METAS"] as $meta_key => $meta_value)
                    {
                        if ((isset ($meta_value["NAME"]) && $meta_value["NAME"]) || isset($meta_value["INDEXABLE"]) || isset($meta_value["INDEXABLE"]))
                        {
                            $metas[$key][$meta_key] = array();
                            $metas[$key][$meta_key]["name"]                 = $meta_value["NAME"];
                            $metas[$key][$meta_key]["indexable"]            = isset($meta_value["INDEXABLE"]) ? 1 : 0;
                            $metas[$key][$meta_key]["facetable"]            = $metas[$key][$meta_key]["indexable"] && isset($meta_value["FACETABLE"]) ? 1 : 0;
                            $metas[$key][$meta_key]["type"]                 = $meta_value["TYPE"];
                            $metas[$key][$meta_key]["order"]                = $i;
                            $metas[$key][$meta_key]["custom_ranking"]       = isset($meta_value["CUSTOM_RANKING"]) && $meta_value["CUSTOM_RANKING"] ? $meta_value["CUSTOM_RANKING"] : 0;
                            $metas[$key][$meta_key]["custom_ranking_sort"]  = isset($meta_value["CUSTOM_RANKING_SORT"]) && $meta_value["CUSTOM_RANKING_SORT"] ? $meta_value["CUSTOM_RANKING_SORT"] : 10000;
                            $metas[$key][$meta_key]["custom_ranking_order"] = isset($meta_value["CUSTOM_RANKING_ORDER"]) && $meta_value["CUSTOM_RANKING_ORDER"] ? $meta_value["CUSTOM_RANKING_ORDER"] : 'asc';

                            $i++;
                        }
                    }
                }
            }

            $this->algolia_registry->metas = $metas;
        }

        $this->algolia_helper->handleIndexCreation();

        wp_redirect('admin.php?page=algolia-settings#extra-metas');
    }

    public function admin_post_reindex()
    {
        global $batch_count;

        foreach ($_POST as $post)
        {
            $subaction = explode("__", $post);

            if (count($subaction) == 1 && $subaction[0] != "reindex")
            {
                if ($subaction[0] == 'handle_index_creation')
                    $this->algolia_helper->handleIndexCreation();
                if ($subaction[0] == 'index_taxonomies')
                    $this->indexer->indexTaxonomies();
                if ($subaction[0] == 'move_indexes')
                    $this->indexer->moveTempIndexes();
            }

            if (count($subaction) == 3)
            {
                if ($subaction[0] == 'type' && in_array($subaction[1], array_keys($this->algolia_registry->indexable_types)) && is_numeric($subaction[2]))
                    $this->indexer->indexPostsTypePart($subaction[1], $batch_count, $subaction[2]);
            }
        }
    }
}