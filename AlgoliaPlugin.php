<?php

class AlgoliaPlugin
{
    private $algolia_registry;
    private $algolia_helper;
    private $indexer;

    public function __construct()
    {
        $this->algolia_registry = \Algolia\Core\Registry::getInstance();
        $this->algolia_helper   = new \Algolia\Core\AlgoliaHelper(
            $this->algolia_registry->app_id,
            $this->algolia_registry->search_key,
            $this->algolia_registry->admin_key
        );
        $this->indexer = new \Algolia\Core\Indexer();

        add_action('admin_menu',                                array($this, 'add_admin_menu'));

        add_action('admin_post_update_account_info',            array($this, 'admin_post_update_account_info'));
        add_action('admin_post_update_index_name',              array($this, 'admin_post_update_index_name'));
        add_action('admin_post_update_indexable_types',         array($this, 'admin_post_update_indexable_types'));
        add_action('admin_post_update_indexable_taxonomies',    array($this, 'admin_post_update_indexable_taxonomies'));
        add_action('admin_post_update_type_of_search',          array($this, 'admin_post_update_type_of_search'));

        add_action('admin_post_reindex',                        array($this, 'admin_post_reindex'));

        add_action('admin_enqueue_scripts',                     array($this, 'admin_scripts'));
        add_action('wp_enqueue_scripts',                        array($this, 'scripts'));

        $this->addExtras();
    }

    private function addExtras()
    {
        $this->algolia_registry->extras = array('type' => 'type');
    }

    public function add_admin_menu()
    {
        $icon_url = plugin_dir_url(__FILE__) . '/admin/imgs/icon.png';
        add_menu_page('Algolia Settings', 'Algolia', 'manage_options', 'algolia-settings', [$this, 'admin_view'], $icon_url);
    }

    public function admin_view()
    {
        include __DIR__ . '/admin/views/admin_menu.php';
    }

    public function scripts()
    {
        if (is_admin())
            return;

        wp_enqueue_style('algolia_styles', plugin_dir_url(__FILE__) . '/front/styles.css');

        $scripts = array('algoliasearch.min.js', 'hogan.js', 'typeahead.js');

        foreach ($scripts as $script) {
            wp_register_script($script, plugin_dir_url(__FILE__) . 'front/' . $script, array());
            wp_localize_script($script, 'settings', array());
        }

        $indexes = array();
        $facets = array();

        foreach ($this->algolia_registry->indexable_types as $type => $name)
            $indexes[] = array('index_name' => $this->algolia_registry->index_name . '_' . $type, 'name' => $name);

        foreach ($this->algolia_registry->indexable_tax as $tax => $name)
            $indexes[] = array('index_name' => $this->algolia_registry->index_name . '_' . $tax, 'name' => $name);

        foreach ($this->algolia_registry->conjunctive_facets as $tax => $name)
            $facets[] = array('tax' => $tax, 'name' => $name, 'type' => 'conjunctive');

        foreach ($this->algolia_registry->disjunctive_facets as $tax => $name)
            $facets[] = array('tax' => $tax, 'name' => $name, 'type' => 'disjunctive');

        $algoliaSettings = array(
            'app_id'                    => $this->algolia_registry->app_id,
            'search_key'                => $this->algolia_registry->search_key,
            'indexes'                   => $indexes,
            'index_name'                => $this->algolia_registry->index_name,
            'type_of_search'            => $this->algolia_registry->type_of_search,
            'instant_jquery_selector'   => $this->algolia_registry->instant_jquery_selector,
            'facets'                    => $facets
        );

        wp_register_script('algolia_main.js', plugin_dir_url(__FILE__) . 'front/main.js', array_merge(array('jquery'), $scripts));
        wp_localize_script('algolia_main.js', 'algoliaSettings', $algoliaSettings);

        wp_enqueue_script('algolia_main.js');

    }

    public function admin_scripts()
    {
        wp_register_script('admin.js', plugin_dir_url(__FILE__) . 'admin/scripts/admin.js', array_merge(array('jquery')));
        wp_localize_script('admin.js', 'algoliaAdminSettings', array());
        wp_enqueue_script('admin.js');

        wp_enqueue_style('AlgoliaSettings', plugin_dir_url(__FILE__) . '/admin/styles/styles.css');
    }

    public function admin_post_update_account_info()
    {
        $app_id     = !empty($_POST['APP_ID'])      ? sanitize_text_field($_POST['APP_ID']) : '';
        $search_key = !empty($_POST['SEARCH_KEY'])  ? sanitize_text_field($_POST['SEARCH_KEY']) : '';
        $admin_key  = !empty($_POST['ADMIN_KEY'])   ? sanitize_text_field($_POST['ADMIN_KEY']) : '';

        $algolia_helper = new \Algolia\Core\AlgoliaHelper($app_id, $search_key, $admin_key);

        $this->algolia_registry->app_id     = $app_id;
        $this->algolia_registry->search_key = $search_key;
        $this->algolia_registry->admin_key  = $admin_key;

        $this->algolia_registry->isCredentialsValid = $algolia_helper->validCredential();

        wp_redirect('admin.php?page=algolia-settings');
    }

    public function admin_post_update_index_name()
    {
        $index_name = !empty($_POST['INDEX_NAME']) ? sanitize_text_field($_POST['INDEX_NAME']) : '';

        $this->algolia_registry->index_name = $index_name;

        wp_redirect('admin.php?page=algolia-settings');
    }

    /**
     *
     */
    public function admin_post_update_indexable_taxonomies()
    {
        $valid_tax = get_taxonomies();

        $taxonomies = [];
        $conjunctive_facets = [];
        $disjunctive_facets = [];

        if (isset($_POST['TAX']) && is_array($_POST['TAX']))
        {
            foreach ($_POST['TAX'] as $tax)
            {
                if (in_array($tax['SLUG'], $valid_tax) || in_array($tax['SLUG'], array_keys($this->algolia_registry->extras)))
                    $taxonomies[$tax['SLUG']] = $tax['NAME'] == '' ? $tax['SLUG'] : $tax['NAME'];

                if (isset($tax['FACET']))
                {
                    if ($tax['FACET_TYPE'] == 'conjunctive')
                        $conjunctive_facets[$tax["SLUG"]] = $tax["NAME"];
                    else
                        $disjunctive_facets[$tax["SLUG"]] = $tax["NAME"];
                }
            }
        }

        $this->algolia_registry->indexable_tax = $taxonomies;
        $this->algolia_registry->conjunctive_facets = $conjunctive_facets;
        $this->algolia_registry->disjunctive_facets = $disjunctive_facets;

        $this->algolia_helper->handleIndexCreation();

        $this->indexer->indexAlltax();

        wp_redirect('admin.php?page=algolia-settings');
    }

    public function admin_post_update_indexable_types()
    {
        $valid_types = get_post_types();

        $types = [];

        if (isset($_POST['TYPES']) && is_array($_POST['TYPES']))
        {
            foreach ($_POST['TYPES'] as $type)
                if (in_array($type['SLUG'], $valid_types))
                    $types[$type['SLUG']] = $type['NAME'] == '' ? $type['SLUG'] : $type['NAME'];
        }

        $this->algolia_registry->indexable_types = $types;

        $this->algolia_helper->handleIndexCreation();

        wp_redirect('admin.php?page=algolia-settings');
    }

    public function admin_post_update_type_of_search()
    {
        if (isset($_POST['TYPE_OF_SEARCH']) && in_array($_POST['TYPE_OF_SEARCH'], array('instant', 'autocomplete')))
            $this->algolia_registry->type_of_search = $_POST['TYPE_OF_SEARCH'];

        if (isset($_POST['JQUERY_SELECTOR']))
            $this->algolia_registry->instant_jquery_selector = $_POST['JQUERY_SELECTOR'];

        wp_redirect('admin.php?page=algolia-settings');
    }

    public function admin_post_reindex()
    {
        $this->algolia_helper->handleIndexCreation();

        $this->indexer->index();
    }
}