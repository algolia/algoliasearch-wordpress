<?php namespace Algolia\Core;

class Registry
{
    private static $instance;
    private $options = [];
    private static $setting_key = 'algolia';

    private $attributes = [
        'isCredentialsValid',
        'app_id',
        'search_key',
        'admin_key',
        'index_name',
        'indexable_types',
        'indexable_tax',
        'type_of_search',
        'instant_jquery_selector'
    ];

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
        if (in_array($name, $this->attributes))
        {
            if (isset($this->options[$name]))
                return $this->options[$name];
            else
                return '';
        }

        throw new \Exception("Unknown attribute: ".$name);
    }

    public function __set($name, $value)
    {
        if (in_array($name, $this->attributes))
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