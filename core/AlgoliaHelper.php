<?php namespace Algolia\Core;

class AlgoliaHelper
{
    private $algolia_client;
    private $algolia_registry;

    private $app_id;
    private $search_key;
    private $admin_key;

    public function __construct($app_id, $search_key, $admin_key)
    {
        $this->algolia_client = new \AlgoliaSearch\Client($app_id, $admin_key);
        $this->algolia_registry = \Algolia\Core\Registry::getInstance();

        $this->app_id = $app_id;
        $this->admin_key = $admin_key;
        $this->search_key = $search_key;
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

    public function setSettings($index_name, $settings)
    {
        $index = $this->algolia_client->initIndex($index_name);
        $index->setSettings($settings);
    }

    public function handleIndexCreation()
    {
        $created_indexes    = $this->algolia_client->listIndexes();

        $index_name = $this->algolia_registry->index_name;

        $indexes = array();

        $facets = array();

        $facets[] = "type";

        global $attributesToIndex;
        global $attributesToIndex2;
        global $attributesToHighlight;
        global $attributesToSnippet;

        global $customRankingTemp;

        $defaultSettings = array(
            "attributesToIndex"     => $attributesToIndex,
            "attributesToHighlight" => $attributesToHighlight,
            "attributesToSnippet"   => $attributesToSnippet
        );

        if (isset($indexes["items"]))
        {
            $indexes = array_map(function ($obj) {
                return $obj["name"];
            }, $created_indexes["items"]);
        }

        foreach (array_keys($this->algolia_registry->indexable_tax) as $name)
        {
            if (in_array($index_name.$name, $indexes) == false)
            {
                $this->setSettings($index_name.$name, $defaultSettings);
                $this->setSettings($index_name.$name."_temp", $defaultSettings);

                $facets[] = $name;
            }
        }

        foreach (array_keys($this->algolia_registry->indexable_types) as $name)
        {
            if (in_array($index_name."_".$name, $indexes) == false)
            {
                if (isset($this->algolia_registry->metas[$name]))
                {
                    foreach ($this->algolia_registry->metas[$name] as $key => $value)
                    {
                        if ($value['facetable'])
                            $facets[] = $key;

                        if ($value['custom_ranking'])
                            $customRankingTemp[] = array('sort' => $value['custom_ranking_sort'], 'value' => $value['custom_ranking_order'].'('.$key.')');
                    }
                }

                $this->setSettings($index_name.$name, $defaultSettings);
                $this->setSettings($index_name.$name."_temp", $defaultSettings);
            }
        }

        usort($customRankingTemp, function ($a, $b) {
            if ($a['sort'] < $b['sort'])
                return -1;
            if ($a['sort'] == $b['sort'])
                return 0;
            return -1;
        });

        $customRanking = array_map(function ($obj) {
            return $obj['value'];
        }, $customRankingTemp);

        $customRanking[] = "desc(date)";

        $settings = array(
            'attributesToIndex'     => $attributesToIndex2,
            'attributesForFaceting' => array_values(array_unique($facets)),
            'attributesToHighlight' => $attributesToHighlight,
            'attributesToSnippet'   => $attributesToSnippet,
            'customRanking'         => $customRanking
        );

        $this->setSettings($index_name.'all', $settings);
        $this->setSettings($index_name.'all_temp', $settings);
    }

    public function move($temp_index_name, $index_name)
    {
        $this->algolia_client->moveIndex($temp_index_name, $index_name);
    }

    public function pushObjects($index_name, $objects)
    {
        $index = $this->algolia_client->initIndex($index_name);

        $index->saveObjects($objects);
    }

    public function pushObject($index_name, $object)
    {
        $index = $this->algolia_client->initIndex($index_name);

        $index->saveObject($object);
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