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
        $index_name         = $this->algolia_registry->index_name;
        $facets             = array();
        $customRankingTemp  = array();

        global $attributesToSnippet;

        $attributesToIndex  = array();

        foreach ($this->algolia_registry->searchable as $key => $value)
            if ($value['ordered'] == 'unordered')
                $attributesToIndex[] = $value['ordered'].'('.$key.')';
            else
                $attributesToIndex[] = $key;

        $defaultSettings = array(
            "attributesToIndex"     => $attributesToIndex,
            "attributesToSnippet"   => $attributesToSnippet
        );

        /**
         * Handle Autocomplete Taxonomies
         */

        if (isset($this->algolia_registry->metas['tax']))
        {
            foreach ($this->algolia_registry->metas['tax'] as $name => $value)
            {
                if ($value['default_attribute'] == 0 && $value['autocompletable'] && in_array('autocomplete', $this->algolia_registry->type_of_search))
                {
                    $mergeSettings = $this->mergeSettings($index_name . $name, $defaultSettings);
                    $this->setSettings($index_name . $name, $mergeSettings);
                    $this->setSettings($index_name . $name . "_temp", $mergeSettings);
                }

                if (isset($this->algolia_registry->metas['tax'][$name]) && $this->algolia_registry->metas['tax'][$name]['facetable'])
                    $facets[] = $name;
            }
        }

        /**
         * Handle Autocomplete Types
         */
        foreach (array_keys($this->algolia_registry->indexable_types) as $name)
        {
            if (in_array('autocomplete', $this->algolia_registry->type_of_search))
            {
                $mergeSettings = $this->mergeSettings($index_name . $name, $defaultSettings);

                $this->setSettings($index_name . $name, $mergeSettings);
                $this->setSettings($index_name . $name . "_temp", $mergeSettings);
            }
        }

        foreach (array_merge(array('tax'), array_keys($this->algolia_registry->indexable_types)) as $name)
        {
            if (isset($this->algolia_registry->metas[$name]))
            {
                foreach ($this->algolia_registry->metas[$name] as $key => $value)
                {
                    if ($value['facetable'])
                        $facets[] = $key;

                    if ($value['custom_ranking'])
                        $customRankingTemp[] = array('sort' => $value['custom_ranking_sort'], 'value' => $value['custom_ranking_order'] . '(' . $key . ')');
                }
            }
        }


        /**
         * Prepare Settings
         */

        usort($customRankingTemp, function ($a, $b) {
            if ($a['sort'] < $b['sort'])
                return -1;
            if ($a['sort'] == $b['sort'])
                return 0;
            return 1;
        });

        $customRanking = array_map(function ($obj) {
            return $obj['value'];
        }, $customRankingTemp);

        $settings = array(
            'attributesToIndex'     => $attributesToIndex,
            'attributesForFaceting' => array_values(array_unique($facets)),
            'attributesToSnippet'   => $attributesToSnippet,
            'customRanking'         => $customRanking
        );

        /**
         * Handle Instant Search Indexes
         */

        $mergeSettings = $this->mergeSettings($index_name.'all', $settings);

        if (in_array('instant', $this->algolia_registry->type_of_search) == false)
            return;

        $this->setSettings($index_name.'all', $mergeSettings);
        $this->setSettings($index_name.'all_temp', $mergeSettings);

        /**
         * Handle Slaves
         */

        if (count($this->algolia_registry->sortable) > 0)
        {
            $slaves = array();

            foreach ($this->algolia_registry->sortable as $values)
                $slaves[] = $index_name.'all_'.$values['name'].'_'.$values['sort'];

            $this->setSettings($index_name.'all', array('slaves' => $slaves));

            foreach ($this->algolia_registry->sortable as $values)
            {
                $mergeSettings['ranking'] = array($values['sort'].'('.$values['name'].')', 'typo', 'geo', 'words', 'proximity', 'attribute', 'exact', 'custom');

                $this->setSettings($index_name.'all_'.$values['name'].'_'.$values['sort'], $mergeSettings);
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