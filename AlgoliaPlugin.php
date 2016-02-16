<?php

/*
 * This class specifies all admin & frontend hooks
 */
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

        if ($this->algolia_registry->validCredential && $this->algolia_registry->app_id && $this->algolia_registry->admin_key && $this->algolia_registry->search_key)
        {
            $this->algolia_helper   = new \Algolia\Core\AlgoliaHelper(
                $this->algolia_registry->app_id,
                $this->algolia_registry->search_key,
                $this->algolia_registry->admin_key
            );

            $this->indexer = new \Algolia\Core\Indexer();
        }

        $this->query_replacer = new \Algolia\Core\QueryReplacer();

        $this->template_helper = new \Algolia\Core\TemplateHelper();

        add_action('wp_head',                                   array($this, 'polyfill'));

        // WP administration menu
        add_action('admin_menu',                                array($this, 'add_admin_menu'));

        // Custom hooks (administration purpose)
        add_action('admin_post_update_settings',                array($this, 'admin_post_update_settings'));
        add_action('admin_post_reset_config_to_default',        array($this, 'admin_post_reset_config_to_default'));
        add_action('admin_post_export_config',                  array($this, 'admin_post_export_config'));

        // WP hooks (SEO purpose)
        add_action('pre_get_posts',                             array($this, 'pre_get_posts'));
        add_filter('the_posts',                                 array($this, 'get_search_result_posts'));

        // Custom hook handling the full reindexing
        add_action('admin_post_reindex',                        array($this, 'admin_post_reindex'));

        // WP hooks (look & feel purpose)
        add_action('admin_enqueue_scripts',                     array($this, 'admin_scripts'));
        add_action('wp_enqueue_scripts',                        array($this, 'styles'));
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
        include __DIR__ . '/templates/' . $this->algolia_registry->template_dir . '/templates.php';
    }

    private function buildSettings()
    {
        $settings_name = [
            'autocompleteTypes', 'additionalAttributes', 'instantTypes', 'facets', 'app_id', 'search_key',
            'index_prefix', 'search_input_selector', 'number_by_page', 'instant_jquery_selector', 'sorts'
        ];

        $settings = array();

        foreach ($settings_name as $name)
            $settings[$name] = str_replace("\\", "", $this->algolia_registry->{$name});

        $algoliaConfig = array_merge($settings, array(
            'template'                  => $this->template_helper->getTemplate($this->algolia_registry->template_dir),
            'is_search_page'            => isset($_GET['instant']),
            'plugin_url'                => plugin_dir_url(__FILE__)
        ));

        return $algoliaConfig;
    }

    public function polyfill()
    {
        echo <<<EOT
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<!--[if lte IE 9]>
  <script src="https://cdn.polyfill.io/v2/polyfill.min.js"></script>
<![endif]-->
EOT;
    }

    public function styles() {
        if (is_admin())
            return;

        wp_enqueue_style('algolia_styles', plugin_dir_url(__FILE__) . 'templates/' . $this->algolia_registry->template_dir . '/styles.css');
    }

    public function scripts()
    {
        if (is_admin())
            return;

        wp_register_script('lib/helper.js', plugin_dir_url(__FILE__) . 'lib/helper.js', array(), null, true);
        wp_register_script('lib/algoliaBundle.min.js', plugin_dir_url(__FILE__) . 'lib/algoliaBundle.min.js', array(), null, true);
        wp_localize_script('lib/algoliaBundle.min.js', 'algoliaConfig', $this->buildSettings());
        wp_register_script('template.js',  plugin_dir_url(__FILE__) . 'templates/' . $this->algolia_registry->template_dir . '/template.js', array('lib/algoliaBundle.min.js', 'lib/helper.js'), array(), null, true);

        wp_enqueue_script('template.js', null, null, null, true);

    }

    public function admin_scripts($hook)
    {
        wp_enqueue_style('styles-admin', plugin_dir_url(__FILE__) . 'admin/styles/styles.css');

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

        foreach ($this->algolia_registry->autocompleteTypes as $value)
            $algoliaAdminSettings["types"][$value['name']] = array('type' => $value['name'], 'count' => wp_count_posts($value['name'])->publish);

        foreach ($this->algolia_registry->instantTypes as $value)
            if (! isset($algoliaAdminSettings['types'][$value['name']]))
                $algoliaAdminSettings["types"][$value['name']] = array('type' => $value['name'], 'count' => wp_count_posts($value['name'])->publish);

        foreach (get_taxonomies() as $tax)
            $algoliaAdminSettings['taxonomies'][$tax] = array('count' => wp_count_terms($tax, array('hide_empty' => false)));

        wp_register_script('lib/algoliaBundle.min.js', plugin_dir_url(__FILE__) . 'lib/algoliaBundle.min.js', array());
        wp_register_script('angular.min.js', plugin_dir_url(__FILE__) . 'admin/scripts/angular.min.js', array());
        wp_register_script('admin.js', plugin_dir_url(__FILE__) . 'admin/scripts/admin.js', array('lib/algoliaBundle.min.js', 'angular.min.js'));
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

    public function admin_post_update_settings()
    {
        // CSRF protection
        if (isset($_POST['algolia_submit_admin_form']) === false || wp_verify_nonce($_POST['algolia_submit_admin_form'], 'algolia_submit_admin_form') === false)
        {
            return;
        }


        if (isset($_POST['submit']) && $_POST['submit'] == 'Import configuration')
        {
            if (isset($_FILES['import']) && isset($_FILES['import']['tmp_name']) && is_uploaded_file($_FILES['import']['tmp_name']) && is_file($_FILES['import']['tmp_name']))
            {
                $content = file_get_contents($_FILES['import']['tmp_name']);

                try
                {
                    $this->algolia_registry->import(json_decode($content, true));
                }
                catch(\Exception $e)
                {
                    echo $e->getMessage();
                    echo '<pre>';
                    echo $e->getTraceAsString();
                    die();
                }
            }
            wp_redirect('admin.php?page=algolia-settings#credentials');
            return;
        }

        $datas = isset($_POST['data']) ? json_decode(str_replace("\\", "", $_POST['data']), true) : array();

        foreach ($datas as $name => $data)
        {
            if (is_array($data))
                foreach ($data as $key => &$value)
                    if (is_array($value))
                        foreach ($value as $sub_key => &$sub_value)
                            $sub_value = \Algolia\Core\WordpressFetcher::try_cast($sub_value);

            $this->algolia_registry->{$name} = $data;
        }

        $algolia_helper = new \Algolia\Core\AlgoliaHelper($this->algolia_registry->app_id, $this->algolia_registry->search_key, $this->algolia_registry->admin_key);
        $algolia_helper->checkRights();

        if ($this->algolia_registry->validCredential)
            $algolia_helper->handleIndexCreation();

        $this->algolia_registry->need_to_reindex    = true;

        die();
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
                if ($subaction[0] == 'type' && is_numeric($subaction[2]))
                    $this->indexer->indexPostsTypePart($subaction[1], $batch_count, $subaction[2]);
            }
        }
    }
}
