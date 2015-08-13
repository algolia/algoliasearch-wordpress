<?php namespace Algolia\Core;

class Registry
{
    private static $instance;
    private $options = array();
    private static $setting_key = 'algolia 1.0';

    private $attributes = array(
        'validCredential'               => false,
        'app_id'                        => '',
        'search_key'                    => '',
        'admin_key'                     => '',
        'index_prefix'                  => 'wordpress_',

        'instant_jquery_selector'       => '#content',
        'number_by_page'                => 10,

        'search_input_selector'         => "[name='s']",
        'template_dir'                  => 'default',

        'excluded_types'                => array('revision', 'nav_menu_item', 'acf', 'product_variation', 'shop_order', 'shop_order_refund', 'shop_coupon', 'shop_webhook', 'wooframework'),

        'enable_truncating'             => true,
        'truncate_size'                 => 9000,

        'last_update'                   => '',
        'need_to_reindex'               => true,

        'autocompleteTypes'             => [],
        'additionalAttributes'          => [],
        'instantTypes'                  => [],
        'attributesToIndex'             => [],
        'customRankings'                => [],
        'facets'                        => [],
        'sorts'                         => []
    );

    public static function getInstance()
    {
        if (! isset(static::$instance))
            static::$instance = new self();

        return static::$instance;
    }

    private function __construct()
    {
        $import_options = get_option(static::$setting_key);
        $this->options = $import_options == false ? array() : $import_options;
    }

    public function __get($name)
    {
        if (isset($this->attributes[$name]))
        {
            if (isset($this->options[$name]))
                return $this->options[$name];
            else
                return $this->attributes[$name];
        }

        try
        {
            throw new \Exception("Unknown attribute: ".$name);
        }
        catch(\Exception $e)
        {
            echo '<pre>';
            echo $e->getMessage().'<br>';
            echo $e->getTraceAsString();
            die('lol');
        }
    }

    public function __set($name, $value)
    {
        if (isset($this->attributes[$name]))
        {
            $this->options[$name] = $value;
            $this->save();
        }
        else
        {
            try
            {
                throw new \Exception("Unknown attribute: ".$name);
            }
            catch(\Exception $e)
            {
                echo '<pre>';
                echo $e->getMessage();
                echo $e->getTraceAsString();
                die();
            }
        }
    }

    public function resetAttribute($name)
    {
        $this->$name = $this->attributes[$name];
    }

    private function save()
    {
        if (get_option(static::$setting_key) !== false)
            update_option(static::$setting_key, $this->options);
        else
            add_option(static::$setting_key, $this->options);
    }

    public function export()
    {
        return json_encode($this->options);
    }

    public function import($attributes)
    {
        if (is_array($attributes))
            foreach ($attributes as $key => $value)
                if (isset($this->attributes[$key]))
                    $this->options[$key] = $value;

        $this->save();
    }

    public function reset_config_to_default()
    {
       foreach ($this->attributes as $key => $value)
           if (in_array($key, array('validCredential', 'app_id', 'search_key', 'admin_key', 'index_name')) == false)
               $this->options[$key] = $value;

       $this->save();
    }
}