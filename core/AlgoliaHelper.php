<?php namespace Algolia\Core;

use AlgoliaSearch\AlgoliaException;

class AlgoliaHelper
{
    private $algolia_client;
    private $algolia_registry;

    private $app_id;
    private $search_key;
    private $admin_key;

    public function __construct($app_id, $search_key, $admin_key)
    {
        if (function_exists('curl_version') == false)
            return;

        $this->algolia_client   = new \AlgoliaSearch\Client($app_id, $admin_key);
        $this->algolia_registry = \Algolia\Core\Registry::getInstance();

        $this->app_id           = $app_id;
        $this->admin_key        = $admin_key;
        $this->search_key       = $search_key;
    }

    public function checkRights()
    {
        try
        {
            /* Check app_id && admin_key => Exception thrown if not working */
            $this->algolia_client->listIndexes();

            /* Check search_key_rights */
            $keys_values = $this->algolia_client->getUserKeyACL($this->search_key);

            if ( ! ($keys_values && isset($keys_values['acl']) && in_array('search', $keys_values['acl'])))
                throw new \Exception("Search key does not have search right");

            $this->algolia_registry->validCredential = true;
        }
        catch(\Exception $e)
        {
            $this->algolia_registry->validCredential = false;
        }
    }

    public function search($query, $options, $index_name)
    {
        $index = $this->algolia_client->initIndex($index_name);

        return $index->search($query, $options);
    }

    public function setSettings($index_name, $settings)
    {
        $index = $this->algolia_client->initIndex($index_name);
        $index->setSettings($settings);
    }

    public function getSettings($index_name)
    {
        $index = $this->algolia_client->initIndex($index_name);

        try
        {
            $settings = $index->getSettings();

            return $settings;
        }
        catch (\Exception $e)
        {

        }

        return array();
    }

    public function mergeSettings($index_name, $settings)
    {
        $onlineSettings = $this->getSettings($index_name);

        $removes = array('slaves');

        foreach ($removes as $remove)
            if (isset($onlineSettings[$remove]))
                unset($onlineSettings[$remove]);

        foreach ($settings as $key => $value)
            $onlineSettings[$key] = $value;

        return $onlineSettings;
    }

    public function handleIndexCreation()
    {
        $index_name         = $this->algolia_registry->index_prefix;

        global $attributesToSnippet;

        $attributesToIndex  = array();
        $unretrievableAttributes = array();

        foreach ($this->algolia_registry->attributesToIndex as $value)
        {
            if ($value['retrievable'] === false)
            {
                $unretrievableAttributes[] = $value['name'];
                continue;
            }

            if ($value['searchable'] === true)
            {
                if ($value['name'] == 'content')
                {
                    foreach (array('h1', 'h2', 'h3', 'h4', 'h5', 'h6') as $tag)
                        $attributesToIndex[] = 'content.'.$tag;

                    $attributesToIndex[] = 'unordered(content.text)';
                }

                if ($value['ordered'] == 'unordered')
                    $attributesToIndex[] = $value['ordered'].'('.$value['name'].')';
                else
                    $attributesToIndex[] = $value['name'];
            }
        }

        $defaultSettings = array(
            "attributesToIndex"     => $attributesToIndex,
            "unretrievableAttributes" => $unretrievableAttributes
        );

        /**
         * Handle Additional autocomplete sections
         */
        foreach ($this->algolia_registry->additionalAttributes as $value)
        {
            $mergeSettings = $this->mergeSettings($index_name . $value['name'], $defaultSettings);

            if (has_filter('prepare_algolia_set_settings'))
            {
                $mergeSettings = apply_filters('prepare_algolia_set_settings', $index_name . $value['name'], $mergeSettings);
                $mergeSettings = apply_filters('prepare_algolia_set_settings', $index_name . $value['name'] . "_temp", $mergeSettings);
            }

            $this->setSettings($index_name . $value['name'], $mergeSettings);
            $this->setSettings($index_name . $value['name'] . "_temp", $mergeSettings);
        }

        /**
         * Handle Types autocomplete
         */

        foreach ($this->algolia_registry->autocompleteTypes as $value)
        {
            $mergeSettings = $this->mergeSettings($index_name . $value['name'], $defaultSettings);

            if (has_filter('prepare_algolia_set_settings'))
            {
                $mergeSettings = apply_filters('prepare_algolia_set_settings', $index_name . $value['name'], $mergeSettings);
                $mergeSettings = apply_filters('prepare_algolia_set_settings', $index_name . $value['name'] . "_temp", $mergeSettings);
            }

            $this->setSettings($index_name . $value['name'], $mergeSettings);
            $this->setSettings($index_name . $value['name'] . "_temp", $mergeSettings);
        }


        $facets             = array();
        $customRanking      = array();

        foreach($this->algolia_registry->facets as $value)
            $facets[] = $value['name'];

        foreach($this->algolia_registry->customRankings as $value)
            $customRanking[] = $value['sort'] . '(' . $value['name'] . ')';

        $settings = array(
            'attributesToIndex'         => $attributesToIndex,
            "unretrievableAttributes"   => $unretrievableAttributes,
            'attributesForFaceting'     => array_values(array_unique($facets)),
            'customRanking'             => $customRanking,
        );

        /**
         * Handle Instant Search Indexes
         */

        $mergeSettings = $this->mergeSettings($index_name.'all', $settings);

        if (count($this->algolia_registry->instantTypes) <= 0)
            return;

        if (has_filter('prepare_algolia_set_settings'))
        {
            $mergeSettings = apply_filters('prepare_algolia_set_settings', $index_name.'all', $mergeSettings);
            $mergeSettings = apply_filters('prepare_algolia_set_settings', $index_name.'all_temp', $mergeSettings);
        }

        $this->setSettings($index_name.'all', $mergeSettings);
        $this->setSettings($index_name.'all_temp', $mergeSettings);

        /**
         * Handle Slaves
         */

        if (count($this->algolia_registry->sorts) > 0)
        {
            $slaves = array();

            foreach ($this->algolia_registry->sorts as $values)
                $slaves[] = $index_name.$values['name'].'_'.$values['sort'];

            $settings = array('slaves' => $slaves);

            if (has_filter('prepare_algolia_set_settings'))
            {
                $settings = apply_filters('prepare_algolia_set_settings', $index_name.'all', $settings);
            }

            $this->setSettings($index_name.'all', $settings);

            foreach ($this->algolia_registry->sorts as $values)
            {
                $mergeSettings['ranking'] = array($values['sort'].'('.$values['name'].')', 'typo', 'geo', 'words', 'proximity', 'attribute', 'exact', 'custom');

                if (has_filter('prepare_algolia_set_settings'))
                {
                    $mergeSettings = apply_filters('prepare_algolia_set_settings', $index_name.$values['name'].'_'.$values['sort'], $mergeSettings);
                }

                $this->setSettings($index_name.$values['name'].'_'.$values['sort'], $mergeSettings);
            }
        }
    }

    public function move($temp_index_name, $index_name)
    {
        $this->algolia_client->moveIndex($temp_index_name, $index_name);
    }

    public function pushObjects($index_name, $objects)
    {
        $index = $this->algolia_client->initIndex($index_name);

        try
        {
            $index->saveObjects($objects);
        }
        catch(AlgoliaException $e)
        {
            if (strstr($e->getMessage(), 'Record is too big') == false)
                throw $e;

            echo "<div>One of your record is too big. You need to reconfigure truncation in the Algolia plugin admin panel or contact support to increase record size limit</div>";
        }
    }

    public function pushObject($index_name, $object)
    {
        if (isset($_GET['reload']))
            return;

        $index = $this->algolia_client->initIndex($index_name);

        try
        {
            $index->saveObject($object);
        }
        catch(AlgoliaException $e)
        {
            if (strstr($e->getMessage(), 'Record is too big') == false)
                throw $e;

            echo "<div>Your record is too big. You need to reconfigure truncation in the Algolia plugin admin panel or contact support to increase record size limit</div>";
            echo "<div>You will be redirected in 5 seconds</div>";
            echo "<script>
                setTimeout(function () {
                    window.location = window.location + '?reload=true';
                }, 5000);
            </script>";

            die();
        }
    }

    public function deleteObject($index_name, $object)
    {
        $index = $this->algolia_client->initIndex($index_name);

        $index->deleteObject($object);
    }


    public function deleteObjects($index_name, $objects)
    {
        $index = $this->algolia_client->initIndex($index_name);

        $index->deleteObjects($objects);
    }
}