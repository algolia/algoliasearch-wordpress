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
        add_action('admin_enqueue_scripts',                     array($this, 'admin_scripts'));
        add_action('admin_post_update_account_info',            array($this, 'admin_post_update_account_info'));
        add_action('admin_post_update_index_name',              array($this, 'admin_post_update_index_name'));
        add_action('admin_post_update_indexable_types',         array($this, 'admin_post_update_indexable_types'));
        add_action('admin_post_update_indexable_taxonomies',    array($this, 'admin_post_update_indexable_taxonomies'));

        add_action('admin_post_reindex', array($this, 'admin_post_reindex'));
    }

    public function add_admin_menu()
    {
        $icon_url = plugin_dir_url(__FILE__) . "/admin/imgs/icon.png";
        add_menu_page("Algolia Settings", "Algolia", "manage_options", "algolia-settings", [$this, 'admin_view'], $icon_url);
    }

    public function admin_view()
    {
        include __DIR__ . "/admin/views/admin_menu.php";
    }

    public function admin_scripts()
    {
        wp_enqueue_style('AlgoliaSettings', plugin_dir_url(__FILE__) . "/admin/styles/styles.css");
    }

    public function admin_post_update_account_info()
    {
        $app_id     = !empty($_POST["APP_ID"])      ? sanitize_text_field($_POST["APP_ID"]) : '';
        $search_key = !empty($_POST["SEARCH_KEY"])  ? sanitize_text_field($_POST["SEARCH_KEY"]) : '';
        $admin_key  = !empty($_POST["ADMIN_KEY"])   ? sanitize_text_field($_POST["ADMIN_KEY"]) : '';

        $algolia_helper = new \Algolia\Core\AlgoliaHelper($app_id, $search_key, $admin_key);

        $this->algolia_registry->app_id     = $app_id;
        $this->algolia_registry->search_key = $search_key;
        $this->algolia_registry->admin_key  = $admin_key;

        $this->algolia_registry->isCredentialsValid = $algolia_helper->validCredential();

        wp_redirect("admin.php?page=algolia-settings");
    }

    public function admin_post_update_index_name()
    {
        $index_name = !empty($_POST["INDEX_NAME"]) ? sanitize_text_field($_POST["INDEX_NAME"]) : '';

        $this->algolia_registry->index_name = $index_name;

        wp_redirect("admin.php?page=algolia-settings");
    }

    public function admin_post_update_indexable_taxonomies()
    {
        $valid_tax = get_taxonomies();

        $taxonomies = [];

        if (isset($_POST["TAX"]) && is_array($_POST["TAX"]))
        {
            foreach ($_POST["TAX"] as $tax)
                if (in_array($tax, $valid_tax))
                    $taxonomies[] = $tax;
        }

        $this->algolia_registry->indexable_tax = $taxonomies;
        $this->algolia_helper->handleIndexCreation();

        wp_redirect("admin.php?page=algolia-settings");
    }

    public function admin_post_update_indexable_types()
    {
        $valid_types = get_post_types();

        $types = [];

        if (isset($_POST["TYPES"]) && is_array($_POST["TYPES"]))
        {
            foreach ($_POST["TYPES"] as $type)
                if (in_array($type, $valid_types))
                    $types[] = $type;
        }

        $this->algolia_registry->indexable_types = $types;

        wp_redirect("admin.php?page=algolia-settings");
    }

    public function admin_post_reindex()
    {
        $this->indexer->index();
    }
}