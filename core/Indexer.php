<?php namespace Algolia\Core;

class Indexer
{
    private $algolia_helper;
    private $algolia_registry;
    private $wordpress_fetcher;

    public function __construct()
    {
        $this->algolia_registry     = \Algolia\Core\Registry::getInstance();

        if ($this->algolia_registry->validCredential)
        {
            $this->algolia_helper = new \Algolia\Core\AlgoliaHelper(
                $this->algolia_registry->app_id,
                $this->algolia_registry->search_key,
                $this->algolia_registry->admin_key
            );
        }

        $this->wordpress_fetcher    = new WordpressFetcher();
    }

    public function indexAllPosts()
    {
        global $wpdb;

        foreach (array_keys($this->algolia_registry->indexable_types) as $type)
        {
            $query = "SELECT COUNT(*) as count FROM " . $wpdb->posts . " WHERE post_status IN ('publish') AND post_type = '".$type."'";
            $result = $wpdb->get_results($query);
            $count = $result[0]->count;
            $max = 10000;

            for ($i = 0; $i < ceil($count / $max); $i++)
                $this->indexPostsTypePart($type, $max, $i * $max);
        }
    }

    public function moveTempIndexes()
    {
        $this->algolia_helper->move($this->algolia_registry->index_name.'all_temp', $this->algolia_registry->index_name.'all');

        if (isset($this->algolia_registry->metas['tax']))
        {
            foreach ($this->algolia_registry->metas['tax'] as $tax => $value)
                if ($value['default_attribute'] == 0)
                    $this->algolia_helper->move($this->algolia_registry->index_name.$tax.'_temp', $this->algolia_registry->index_name.$tax);
        }

        foreach (array_keys($this->algolia_registry->indexable_types) as $type)
            $this->algolia_helper->move($this->algolia_registry->index_name.$type.'_temp', $this->algolia_registry->index_name.$type);

        $this->algolia_helper->handleIndexCreation();
    }

    private function getPosts($type, $limit)
    {
        global $wpdb;

        $query = "SELECT * FROM " . $wpdb->posts . " WHERE post_password = '' AND post_status IN ('publish') ".$type." ".$limit;

        $posts = $wpdb->get_results($query);

        $objects = array();

        if ($posts)
            foreach ($posts as $post)
                $objects[] = $this->wordpress_fetcher->getPostObj($post);

        return $objects;
    }

    public function indexTaxonomie($tax)
    {
        $terms = array();

        foreach (get_terms($tax) as $term)
            $terms[] = $this->wordpress_fetcher->getTermObj($term);

        $this->algolia_helper->pushObjects($this->algolia_registry->index_name.$tax.'_temp', $terms);
    }

    public function indexTaxonomies()
    {
        if (isset($this->algolia_registry->metas['tax']))
            foreach ($this->algolia_registry->metas['tax'] as $tax => $value)
                if ($value['autocompletable'] && $value['default_attribute'] == false)
                    $this->indexTaxonomie($tax);
    }

    public function indexPost($post)
    {
        $object = $this->wordpress_fetcher->getPostObj($post);

        $this->algolia_helper->pushObject($this->algolia_registry->index_name.$post->post_type, $object);
        $this->algolia_helper->pushObject($this->algolia_registry->index_name.'all', $object);
    }

    public function deletePost($post_id, $type)
    {
        $this->algolia_helper->deleteObject($this->algolia_registry->index_name.$type, $post_id);
        $this->algolia_helper->deleteObject($this->algolia_registry->index_name.'all', $post_id);
    }

    public function indexTerm($term, $taxonomy)
    {
        if (isset($this->algolia_registry->metas['tax'][$taxonomy]) && $this->algolia_registry->metas['tax'][$taxonomy]['autocompletable'])
        {
            $object = $this->wordpress_fetcher->getTermObj($term);

            $this->algolia_helper->pushObject($this->algolia_registry->index_name.$taxonomy, $object);
        }

    }

    public function deleteTerm($term_id, $taxonomy)
    {
        $this->algolia_helper->deleteObject($this->algolia_registry->index_name.$taxonomy, $term_id);
    }

    public function indexPostsTypePart($type, $count, $offset)
    {
        $objects = $this->getPosts("AND post_type = '".$type."' ", "LIMIT ".($offset * $count).",".$count);

        $this->algolia_helper->pushObjects($this->algolia_registry->index_name.$type.'_temp', $objects);
        $this->algolia_helper->pushObjects($this->algolia_registry->index_name.'all_temp', $objects);
    }
}