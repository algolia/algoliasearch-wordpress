<?php namespace Algolia\Core;

class Registry
{
    private static $instance;
    private $options = array();
    private static $setting_key = 'algolia';

    private $attributes = array(
        'validCredential'           => false,
        'app_id'                    => '',
        'search_key'                => '',
        'admin_key'                 => '',
        'index_name'                => '',
        'indexable_types'           => array('post' => array('name' => 'Articles','order' => 0),'page' => array('name' => 'Pages','order' => 1)),
        'indexable_tax'             => array(),
        'searchable'                => array(),
        'type_of_search'            => 'autocomplete',
        'conjunctive_facets'        => array(),
        'disjunctive_facets'        => array(),
        'instant_jquery_selector'   => '#content',
        'extras'                    => array(),
        'metas'                     => array(),
        'number_by_page'            => 3,
        'number_by_type'            => 2,
        'search_input_selector'     => "[name='s']",
        'theme'                     => 'default',
        'date_custom_ranking'       => array('order' => 10000, 'enabled' => true, 'sort' => 'desc')
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

        throw new \Exception("Unknown attribute: ".$name);
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
            throw new \Exception("Unknown attribute: ".$name);
        }
    }

    private function save()
    {
        if (get_option(static::$setting_key) !== false)
            update_option(static::$setting_key, $this->options);
        else
            add_option(static::$setting_key, $this->options);
    }


}