<?php namespace Algolia\Core;

class Indexer
{
    private $algolia_helper;
    private $algolia_registry;
    private $wordpress_fetcher;

    public function __construct()
    {
        $this->algolia_registry     = \Algolia\Core\Registry::getInstance();
        $this->algolia_helper       = new \Algolia\Core\AlgoliaHelper(
                                        $this->algolia_registry->app_id,
                                        $this->algolia_registry->search_key,
                                        $this->algolia_registry->admin_key
                                      );
        $this->wordpress_fetcher    = new WordpressFetcher();
    }

    public function indexAllPosts()
    {
        global $wpdb;

        foreach (array_keys($this->algolia_registry->indexable_types) as $obj)
        {
            $query = "SELECT COUNT(*) as count FROM " . $wpdb->posts . " WHERE post_status IN ('publish') AND post_type = '".$obj['name']."'";
            $result = $wpdb->get_results($query);
            $count = $result[0]->count;
            $max = 10000;

            for ($i = 0; $i < ceil($count / $max); $i++)
                $this->indexPostsTypePart($obj['name'], $max, $i * $max);
        }
    }

    public function moveTempIndexes()
    {
        $this->algolia_helper->move($this->algolia_registry->index_name.'_temp', $this->algolia_registry->index_name);

        $taxonomies_name = array_keys($this->algolia_registry->indexable_tax);

        foreach ($taxonomies_name as $obj)
            $this->algolia_helper->move($this->algolia_registry->index_name.'_'.$obj['name'].'_temp', $this->algolia_registry->index_name.'_'.$obj['name']);

        foreach (array_keys($this->algolia_registry->indexable_types) as $obj)
            $this->algolia_helper->move($this->algolia_registry->index_name.'_'.$obj['name'].'_temp', $this->algolia_registry->index_name.'_'.$obj['name']);
    }

    private function getPosts($type, $limit)
    {
        global $wpdb;

        $query = "SELECT * FROM " . $wpdb->posts . " WHERE post_status IN ('publish') ".$type." ".$limit;

        $posts = $wpdb->get_results($query);

        $objects = array();

        if ($posts)
            foreach ($posts as $post)
                $objects[] = $this->wordpress_fetcher->getPostObj($post);

        return $objects;
    }

    public function indexTaxonomie($tax)
    {
        $terms = [];

        foreach (get_terms($tax) as $term)
            $terms[] = $this->wordpress_fetcher->getTermObj($term);

        $this->algolia_helper->pushObjects($this->algolia_registry->index_name.'_'.$tax.'_temp', $terms);
    }

    public function indexTaxonomies()
    {
        $taxonomies_name = array_keys($this->algolia_registry->indexable_tax);

        foreach ($taxonomies_name as $obj)
            $this->indexTaxonomie($obj['name']);
    }

    public function indexPost($post)
    {
        $object = $this->wordpress_fetcher->getPostObj($post);

        $this->algolia_helper->pushObject($this->algolia_registry->index_name.'_'.$post->post_type, $object);
        $this->algolia_helper->pushObject($this->algolia_registry->index_name, $object);
    }

    public function deletePost($post_id, $type)
    {
        $this->algolia_helper->deleteObject($this->algolia_registry->index_name.'_'.$type, $post_id);
        $this->algolia_helper->deleteObject($this->algolia_registry->index_name, $post_id);
    }

    public function indexTerm($term, $taxonomy)
    {
        $object = $this->wordpress_fetcher->getTermObj($term);

        $this->algolia_helper->pushObject($this->algolia_registry->index_name.'_'.$taxonomy, $object);
    }

    public function deleteTerm($term_id, $taxonomy)
    {
        $this->algolia_helper->deleteObject($this->algolia_registry->index_name.'_'.$taxonomy, $term_id);
    }

    public function indexPostsTypePart($type, $count, $offset)
    {
        $objects = $this->getPosts("AND post_type = '".$type."' ", "LIMIT ".$offset.",".$count);

        $this->algolia_helper->pushObjects($this->algolia_registry->index_name.'_'.$type.'_temp', $objects);
        $this->algolia_helper->pushObjects($this->algolia_registry->index_name.'_temp', $objects);
    }
}