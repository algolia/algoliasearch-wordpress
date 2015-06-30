<?php

class AlgoliaPlugin
{
    private $algolia_registry;
    private $algolia_helper;
    private $indexer;
    private $template_helper;
    private $query_replacer;

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

        $this->query_replacer = new \Algolia\Core\QueryReplacer();

        $this->template_helper = new \Algolia\Core\TemplateHelper();

        $this->indexer = new \Algolia\Core\Indexer();

        add_action('admin_menu',                                array($this, 'add_admin_menu'));

        add_action('admin_post_update_account_info',            array($this, 'admin_post_update_account_info'));
        add_action('admin_post_update_index_name',              array($this, 'admin_post_update_index_name'));
        add_action('admin_post_update_indexable_types',         array($this, 'admin_post_update_indexable_types'));
        add_action('admin_post_update_ui',                      array($this, 'admin_post_update_ui'));
        add_action('admin_post_update_extra_meta',              array($this, 'admin_post_update_extra_meta'));
        add_action('admin_post_custom_ranking',                 array($this, 'admin_post_custom_ranking'));
        add_action('admin_post_update_searchable_attributes',   array($this, 'admin_post_update_searchable_attributes'));
        add_action('admin_post_update_sortable_attributes',     array($this, 'admin_post_update_sortable_attributes'));
        add_action('admin_post_reset_config_to_default',        array($this, 'admin_post_reset_config_to_default'));
        add_action('admin_post_export_config',                  array($this, 'admin_post_export_config'));
        add_action('admin_post_update_advanced_settings',       array($this, 'admin_post_update_advanced_settings'));

        add_action('pre_get_posts',                             array($this, 'pre_get_posts'));
        add_filter('the_posts',                                 array($this, 'get_search_result_posts'));

        add_action('admin_post_reindex',                        array($this, 'admin_post_reindex'));

        add_action('admin_enqueue_scripts',                     array($this, 'admin_scripts'));
        add_action('wp_enqueue_scripts',                        array($this, 'scripts'));

        add_action('wp_footer',                                 array($this, 'wp_footer'));
    }

    public function add_admin_menu()
    {
        $icon_url = plugin_dir_url(__FILE__) . 'admin/imgs/icon.png';
        add_menu_page('Algolia Settings', 'Algolia Search', 'manage_options', 'algolia-settings', array($this, 'admin_view'), $icon_url);
    }

    public function admin_view()
    {
        include __DIR__ . '/admin/views/admin_menu.php';
    }

    public function wp_footer()
    {
        include __DIR__ . '/templates/' . $this->algolia_registry->template . '/templates.php';
    }

    private function buildSettings()
    {
        $indices = array();
        $facets = array();

        foreach ($this->algolia_registry->indexable_types as $type => $obj)
        {
            if ($obj['autocompletable'])
                $indices[] = array('index_name' => $this->algolia_registry->index_name . $type, 'name' => $obj['name'], 'order1' => 0, 'order2' => $obj['order']);

            if (isset($this->algolia_registry->metas[$type]))
                foreach ($this->algolia_registry->metas[$type] as $meta_key => $meta_value)
                    if ($meta_value['facetable'])
                        $facets[] = array('order' => $meta_value['order'], 'tax' => $meta_key, 'name' => $meta_value['name'] ? $meta_value['name'] : $meta_key, 'type' => $meta_value['type']);
        }

        if (isset($this->algolia_registry->metas['tax']))
        {
            foreach ($this->algolia_registry->metas['tax'] as $tax => $obj)
            {
                if ($obj['default_attribute'] == 0 && $obj['autocompletable'])
                    $indices[] = array('index_name' => $this->algolia_registry->index_name . $tax, 'name' => $obj['name'], 'order1' => 1, 'order2' => $obj['order']);

                if ($obj['facetable'])
                    $facets[] = array('tax' => $tax, 'name' => $obj['name'] ? $obj['name'] : $tax, 'order' => $obj['order'], 'type' => $obj['type']);
            }
        }

        $sorting_indices = array();

        foreach ($this->algolia_registry->sortable as $values)
            $sorting_indices[] = array(
                'index_name' => $this->algolia_registry->index_name.'all_'.$values['name'].'_'.$values['sort'],
                'label'      => $values['label']
            );

        $algoliaSettings = array(
            'app_id'                    => $this->algolia_registry->app_id,
            'search_key'                => $this->algolia_registry->search_key,
            'indices'                   => $indices,
            'sorting_indices'           => $sorting_indices,
            'index_name'                => $this->algolia_registry->index_name,
            'autocomplete'              => $this->algolia_registry->autocomplete,
            'instant'                   => $this->algolia_registry->instant,
            'instant_jquery_selector'   => str_replace("\\", "", $this->algolia_registry->instant_jquery_selector),
            'facets'                    => $facets,
            'number_by_type'            => $this->algolia_registry->number_by_type,
            'number_by_page'            => $this->algolia_registry->number_by_page,
            'search_input_selector'     => str_replace("\\", "", $this->algolia_registry->search_input_selector),
            'plugin_url'                => plugin_dir_url(__FILE__),
            'template'                  => $this->template_helper->get_current_template(),
            'is_search_page'            => isset($_GET['instant'])
        );

        return $algoliaSettings;
    }

    public function scripts()
    {
        if (is_admin())
            return;

        wp_enqueue_style('algolia_bundle', plugin_dir_url(__FILE__) . 'templates/' . $this->algolia_registry->template . '/bundle.css');
        wp_enqueue_style('algolia_styles', plugin_dir_url(__FILE__) . 'templates/' . $this->algolia_registry->template . '/styles.css');



        wp_register_script('lib/bundle.min.js', plugin_dir_url(__FILE__) . 'lib/bundle.min.js', array());
        wp_localize_script('lib/bundle.min.js', 'algoliaSettings', $this->buildSettings());

        wp_register_script('template.js',  plugin_dir_url(__FILE__) . 'templates/' . $this->algolia_registry->template . '/template.js', array('lib/bundle.min.js'), array());

        wp_enqueue_script('template.js');

    }

    public function admin_scripts($hook)
    {
        wp_enqueue_style('styles-admin', plugin_dir_url(__FILE__) . 'admin/styles/styles.css');
        wp_enqueue_style('algolia_bundle', plugin_dir_url(__FILE__) . 'templates/' . $this->algolia_registry->template . '/bundle.css');

        // Only load these scripts on the Algolia admin page
        if ( 'toplevel_page_algolia-settings' != $hook ) {
            return;
        }

        global $batch_count;

        $algoliaAdminSettings = array(
            'taxonomies'    => array(),
            'types'         => array(),
            'batch_count'   => $batch_count,
            'site_url'      => site_url()
        );

        foreach (get_taxonomies() as $tax)
            $algoliaAdminSettings['taxonomies'][$tax] = array('count' => wp_count_terms($tax, array('hide_empty' => false)));

        foreach ($this->algolia_registry->indexable_types as $type => $obj)
            $algoliaAdminSettings["types"][$type] = array('type' => $type, 'name' => $obj['name'], 'count' => wp_count_posts($type)->publish);




        wp_register_script('lib/bundle.min.js', plugin_dir_url(__FILE__) . 'lib/bundle.min.js', array());
        wp_register_script('admin.js', plugin_dir_url(__FILE__) . 'admin/scripts/admin.js', array('lib/bundle.min.js'));
        wp_localize_script('admin.js', 'algoliaAdminSettings', $algoliaAdminSettings);
        wp_enqueue_script('admin.js');
    }

    public function pre_get_posts($query)
    {
        return $this->query_replacer->search($query);
    }

    public function get_search_result_posts($posts)
    {
        $posts = $this->query_replacer->getOrderedPost($posts);

        return $posts;
    }

    public function admin_post_update_account_info()
    {

        if (isset($_POST['submit']) && $_POST['submit'] == 'Import'
            && isset($_FILES['import']) && isset($_FILES['import']['tmp_name']) && is_file($_FILES['import']['tmp_name']))
        {
            $content = file_get_contents($_FILES['import']['tmp_name']);

            try
            {
                $this->algolia_registry->import(json_decode($content, true));
                wp_redirect('admin.php?page=algolia-settings#credentials');
                return;
            }
            catch(\Exception $e)
            {
                echo $e->getMessage();
                echo '<pre>';
                echo $e->getTraceAsString();
                die();
            }
        }

        $app_id     = !empty($_POST['APP_ID'])      ? sanitize_text_field($_POST['APP_ID']) : '';
        $search_key = !empty($_POST['SEARCH_KEY'])  ? sanitize_text_field($_POST['SEARCH_KEY']) : '';
        $admin_key  = !empty($_POST['ADMIN_KEY'])   ? sanitize_text_field($_POST['ADMIN_KEY']) : '';
        $index_name = !empty($_POST['INDEX_NAME']) ? sanitize_text_field($_POST['INDEX_NAME']) : '';

        $algolia_helper = new \Algolia\Core\AlgoliaHelper($app_id, $search_key, $admin_key);

        $this->algolia_registry->app_id             = $app_id;
        $this->algolia_registry->search_key         = $search_key;
        $this->algolia_registry->admin_key          = $admin_key;
        $this->algolia_registry->index_name         = $index_name;

        $this->algolia_registry->need_to_reindex    = true;

        $algolia_helper->checkRights();

        wp_redirect('admin.php?page=algolia-settings#credentials');
    }

    public function admin_post_update_indexable_types()
    {
        $valid_types = get_post_types();

        $types = array();

        $instant        = false;
        $autocomplete   = false;

        $metas = $this->algolia_registry->metas;

        if (isset($metas['tax']) && is_array($metas['tax']))
        {
            foreach ($metas['tax'] as $tax)
            {
                $autocomplete = $autocomplete || $tax['autocompletable'];
            }
        }

        if (isset($_POST['TYPES']) && is_array($_POST['TYPES']))
        {
            $i = 0;

            foreach ($_POST['TYPES'] as $slug => $type)
            {
                if (in_array($slug, $valid_types))
                {
                    $autocomplete   = $autocomplete || isset($type['AUTOCOMPLETABLE']);
                    $instant        = $instant || isset($type['INSTANTABLE']);

                    $types[$slug] = array(
                        'autocompletable'       => isset($type['AUTOCOMPLETABLE']),
                        'instantable'           => isset($type['INSTANTABLE']),
                        'nb_results_by_section' => $type['NB_RESULTS_BY_SECTION'],
                        'name'                  => $type['NAME'] == '' ? $type['SLUG'] : $type['NAME'],
                        'order'                 => $i
                    );

                    $i++;
                }
            }
        }

        $this->algolia_registry->instant            = $instant;
        $this->algolia_registry->autocomplete       = $autocomplete;

        $this->algolia_registry->indexable_types    = $types;

        $this->algolia_registry->need_to_reindex      = true;

        $this->algolia_helper->handleIndexCreation();

        wp_redirect('admin.php?page=algolia-settings#indexable-types');
    }

    public function admin_post_update_searchable_attributes()
    {
        if (isset($_POST['ATTRIBUTES']) && is_array($_POST['ATTRIBUTES']))
        {
            $searchable = array();

            $i = 0;

            foreach ($_POST['ATTRIBUTES'] as $key => $values)
            {
                if (isset($values['SEARCHABLE']))
                {
                    $searchable[$key] = array();

                    $searchable[$key]["ordered"]    = $values['ORDERED'];
                    $searchable[$key]["order"]      = $i;

                    $i++;
                }
            }
            $this->algolia_registry->searchable = $searchable;


            $this->algolia_helper->handleIndexCreation();
        }

        wp_redirect('admin.php?page=algolia-settings#searchable_attributes');
    }

    public function admin_post_update_sortable_attributes()
    {
        if (isset($_POST['ATTRIBUTES']) && is_array($_POST['ATTRIBUTES']))
        {
            $sortable = array();

            foreach ($_POST['ATTRIBUTES'] as $key => $values)
            {
                if (isset($values['asc']))
                    $sortable[$key.'_asc'] = array('name' => $key, 'sort' => 'asc', 'label' => $values['LABEL_asc'], 'order' => $values['ORDER_asc']);

                if (isset($values['desc']))
                    $sortable[$key.'_desc'] = array('name' => $key, 'sort' => 'desc', 'label' => $values['LABEL_desc'], 'order' => $values['ORDER_desc']);

            }

            uasort($sortable, function ($a, $b) {
               if ($a['order'] < $b['order'])
                   return -1;
                return 1;
            });

            $this->algolia_registry->sortable = $sortable;

            $this->algolia_helper->handleIndexCreation();
        }

        $this->algolia_registry->need_to_reindex  = true;

        wp_redirect('admin.php?page=algolia-settings#sortable_attributes');
    }

    public function admin_post_update_advanced_settings()
    {
        $this->algolia_registry->enable_truncating = isset($_POST['ENABLE_TRUNCATING']);

        if (isset($_POST['TRUNCATE_SIZE']) && is_numeric($_POST['TRUNCATE_SIZE']))
            $this->algolia_registry->truncate_size = $_POST['TRUNCATE_SIZE'];

        $this->algolia_registry->need_to_reindex  = true;

        wp_redirect('admin.php?page=algolia-settings#advanced');
    }


    public function admin_post_update_ui()
    {
        if (isset($_POST['JQUERY_SELECTOR']))
            $this->algolia_registry->instant_jquery_selector = str_replace('"', '\'', $_POST['JQUERY_SELECTOR']);

        if (isset($_POST['NUMBER_BY_PAGE']) && is_numeric($_POST['NUMBER_BY_PAGE']))
            $this->algolia_registry->number_by_page = $_POST['NUMBER_BY_PAGE'];

        $search_input_selector  = !empty($_POST['SEARCH_INPUT_SELECTOR']) ? $_POST['SEARCH_INPUT_SELECTOR'] : '';
        $template                  = !empty($_POST['template']) ? $_POST['template'] : 'default';

        $this->algolia_registry->search_input_selector  = str_replace('"', '\'', $search_input_selector);
        $this->algolia_registry->template                  = $template;


        /**
         * Handle Facet types that do not exist anymore because of template changing
         */
        $new_facet_types = array_merge(array('conjunctive' => 'Conjunctive', 'disjunctive' => 'Disjunctive'), $this->template_helper->get_current_template()->facet_types);

        $metas = $this->algolia_registry->metas;

        foreach ($metas as &$types)
            foreach ($types as &$meta)
                if (isset($new_facet_types[$meta['type']]) == false)
                    $meta['type'] = 'conjunctive';

        $this->algolia_registry->metas = $metas;

        $this->algolia_helper->handleIndexCreation();

        wp_redirect('admin.php?page=algolia-settings#configuration');
    }

    public function admin_post_custom_ranking()
    {
        $indexable_types        = $this->algolia_registry->indexable_types;
        $metas                  = $this->algolia_registry->metas;

        if (isset($_POST['TYPES']) && is_array($_POST['TYPES']))
        {
            $i = 1; // keep 1 and not 0 to avoid bad condition when saving metas

            foreach ($_POST['TYPES'] as $key => $value)
            {
                if (((in_array($key, array_keys($indexable_types)) || $key == 'tax') && isset($value["METAS"]) && is_array($value["METAS"])))
                {
                    foreach ($value["METAS"] as $meta_key => $meta_value)
                    {
                        if (isset($metas[$key][$meta_key]) && $metas[$key][$meta_key]['indexable'])
                        {
                            $metas[$key][$meta_key]['custom_ranking']       = isset($meta_value['CUSTOM_RANKING']) ? 1 : 0;
                            $metas[$key][$meta_key]["custom_ranking_order"] = $meta_value["CUSTOM_RANKING_ORDER"];

                            if ($metas[$key][$meta_key]['custom_ranking'])
                                $metas[$key][$meta_key]["custom_ranking_sort"]  = $i;
                            else
                                $metas[$key][$meta_key]["custom_ranking_sort"]  = 10000;

                            $i++;
                        }
                    }
                }
            }

            $this->algolia_registry->metas = $metas;
        }

        $this->algolia_helper->handleIndexCreation();

        wp_redirect('admin.php?page=algolia-settings#custom-ranking');
    }

    public function admin_post_reset_config_to_default()
    {
        $this->algolia_registry->reset_config_to_default();

        $this->algolia_registry->need_to_reindex  = true;
    }

    public function admin_post_export_config()
    {
        header("Content-type: text/plain");
        header("Content-Disposition: attachment; filename=algolia-wordpress-config.txt");

        echo $this->algolia_registry->export();
    }

    public function admin_post_update_extra_meta()
    {
        /**
         * Handle Extra Metas
         */
        $indexable_types = $this->algolia_registry->indexable_types;

        $metas = array();

        if (isset($_POST['TYPES']) && is_array($_POST['TYPES']))
        {

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
                            $metas[$key][$meta_key]["order"]                = $meta_value["ORDER"];
                            $metas[$key][$meta_key]["custom_ranking"]       = isset($meta_value["CUSTOM_RANKING"]) && $meta_value["CUSTOM_RANKING"] ? $meta_value["CUSTOM_RANKING"] : 0;
                            $metas[$key][$meta_key]["custom_ranking_sort"]  = isset($meta_value["CUSTOM_RANKING_SORT"]) && $meta_value["CUSTOM_RANKING_SORT"] ? $meta_value["CUSTOM_RANKING_SORT"] : 10000;
                            $metas[$key][$meta_key]["custom_ranking_order"] = isset($meta_value["CUSTOM_RANKING_ORDER"]) && $meta_value["CUSTOM_RANKING_ORDER"] ? $meta_value["CUSTOM_RANKING_ORDER"] : 'asc';
                        }
                    }
                }
            }
        }

        /**
         * Handle Taxonomies + Default attributes
         */

        $valid_tax = get_taxonomies();

        $autocomplete = false;

        foreach ($indexable_types as $type)
        {
            $autocomplete = $autocomplete || $type['autocompletable'];
        }

        if (isset($_POST['TAX']) && is_array($_POST['TAX']))
        {
            foreach ($_POST['TAX'] as $tax)
            {
                if (in_array($tax['SLUG'], $valid_tax) || in_array($tax['SLUG'], array_keys($this->algolia_registry->extras)))
                {
                    $metas['tax'][$tax['SLUG']] = array();

                    $metas['tax'][$tax['SLUG']]['default_attribute']    = in_array($tax['SLUG'], array_keys($this->algolia_registry->extras)) ? 1 : 0;
                    $metas['tax'][$tax['SLUG']]['name']                 = isset($tax['NAME']) ? $tax['NAME'] : '';
                    $metas['tax'][$tax['SLUG']]['indexable']            = 1;

                    $metas['tax'][$tax['SLUG']]['facetable']            = $this->algolia_registry->instant
                                                                            && $metas['tax'][$tax['SLUG']]['indexable'] && isset($tax['FACETABLE']) ? 1 : 0;

                    $autocomplete = $autocomplete || $metas['tax'][$tax['SLUG']]['indexable'] && isset($tax['AUTOCOMPLETABLE']);

                    $metas['tax'][$tax['SLUG']]['autocompletable']      = $metas['tax'][$tax['SLUG']]['indexable'] && isset($tax['AUTOCOMPLETABLE']) ? 1 : 0;
                    $metas['tax'][$tax['SLUG']]['type']                 = $tax['FACET_TYPE'];
                    $metas['tax'][$tax['SLUG']]['order']                = $tax['ORDER'];
                    $metas['tax'][$tax['SLUG']]['custom_ranking']       = isset($tax['CUSTOM_RANKING']) && $tax['CUSTOM_RANKING'] ? $tax['CUSTOM_RANKING'] : 0;
                    $metas['tax'][$tax['SLUG']]['custom_ranking_sort']  = isset($tax['CUSTOM_RANKING_SORT']) && $tax['CUSTOM_RANKING_SORT'] ? $tax['CUSTOM_RANKING_SORT'] : 10000;
                    $metas['tax'][$tax['SLUG']]['custom_ranking_order'] = isset($tax['CUSTOM_RANKING_ORDER']) && $tax['CUSTOM_RANKING_ORDER'] ? $tax['CUSTOM_RANKING_ORDER'] : 'asc';
                }
            }
        }

        $this->algolia_registry->autocomplete   = $autocomplete;

        $this->algolia_registry->metas          = $metas;

        $this->algolia_registry->need_to_reindex  = true;

        $this->algolia_helper->handleIndexCreation();

        $this->indexer->indexTaxonomies();


        wp_redirect('admin.php?page=algolia-settings#extra-metas');
    }

    public function admin_post_reindex()
    {
        global $batch_count;

        foreach ($_POST as $post)
        {
            $subaction = explode('__', $post);

            if (count($subaction) == 1 && $subaction[0] != "reindex")
            {
                if ($subaction[0] == 'handle_index_creation')
                    $this->algolia_helper->handleIndexCreation();
                if ($subaction[0] == 'index_taxonomies')
                    $this->indexer->indexTaxonomies();
                if ($subaction[0] == 'move_indexes')
                {
                    $this->indexer->moveTempIndexes();

                    $this->algolia_registry->need_to_reindex  = false;
                }
            }

            if (count($subaction) == 3)
            {
                $this->algolia_registry->last_update = time();
                if ($subaction[0] == 'type' && in_array($subaction[1], array_keys($this->algolia_registry->indexable_types)) && is_numeric($subaction[2]))
                    $this->indexer->indexPostsTypePart($subaction[1], $batch_count, $subaction[2]);
            }
        }
    }
}
