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
        $this->algolia_helper->move($this->algolia_registry->index_prefix.'all_temp', $this->algolia_registry->index_prefix.'all');

        foreach ($this->algolia_registry->additionalAttributes as $value)
            $this->algolia_helper->move($this->algolia_registry->index_prefix . $value['name'] . "_temp", $this->algolia_registry->index_prefix . $value['name']);

        foreach ($this->algolia_registry->autocompleteTypes as $value)
            $this->algolia_helper->move($this->algolia_registry->index_prefix . $value['name'] . "_temp", $this->algolia_registry->index_prefix . $value['name']);
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

    public function indexMeta($meta)
    {
        global $wpdb;

        $terms = $wpdb->get_col("SELECT DISTINCT(meta_value) FROM $wpdb->postmeta WHERE meta_key = '$meta'" );

        $terms = array_map(function ($item) use ($meta) {
            return array(
                'objectID'  => $item,
                $meta       => $item
            );
        }, $terms);

        $this->algolia_helper->pushObjects($this->algolia_registry->index_prefix.$meta.'_temp', $terms);
    }

    public function indexAttribute($attribute)
    {
        global $wpdb;

        if ($attribute == 'author')
        {
            $terms = $wpdb->get_col("SELECT DISTINCT(display_name) FROM $wpdb->users INNER JOIN $wpdb->posts ON $wpdb->posts.post_author = $wpdb->users.ID WHERE post_type='post' OR post_type='page'");

            $terms = array_map(function ($item) {
                return array(
                    'objectID' => $item,
                    'author'   => $item,
                    'title'    => $item
                );
            }, $terms);

            $this->algolia_helper->pushObjects($this->algolia_registry->index_prefix.$attribute.'_temp', $terms);
        }
    }

    public function indexTaxonomie($tax)
    {
        $terms = array();

        foreach (get_terms($tax) as $term)
            $terms[] = $this->wordpress_fetcher->getTermObj($term);

        $this->algolia_helper->pushObjects($this->algolia_registry->index_prefix.$tax.'_temp', $terms);
    }

    public function indexTaxonomies()
    {
        foreach ($this->algolia_registry->additionalAttributes as $attribute)
        {
            if ($attribute['group'] == 'Taxonomy')
                $this->indexTaxonomie($attribute['name']);
            if (strpos($attribute['group'], 'Meta') !== false)
                $this->indexMeta($attribute['name']);
            if ($attribute['group'] == 'Record attribute')
                $this->indexAttribute($attribute['name']);
        }
    }

    public function indexPost($post, $autocomplete, $instant)
    {
        $object = $this->wordpress_fetcher->getPostObj($post);

        if ($autocomplete)
            $this->algolia_helper->pushObject($this->algolia_registry->index_prefix.$post->post_type, $object);

        if ($instant)
            $this->algolia_helper->pushObject($this->algolia_registry->index_prefix.'all', $object);
    }

    public function deletePost($post_id, $type)
    {
        $this->algolia_helper->deleteObject($this->algolia_registry->index_prefix.$type, $post_id);
        $this->algolia_helper->deleteObject($this->algolia_registry->index_prefix.'all', $post_id);
    }

    public function indexTerm($term, $taxonomy)
    {
        $additionalAttributes = $this->algolia_registry->additionalAttributes;

        if (count(array_filter($additionalAttributes, function($item) use($taxonomy) { return $item['group'] == 'Taxonomy' && $item['name'] == $taxonomy; })) > 0)
        {
            $object = $this->wordpress_fetcher->getTermObj($term);

            $this->algolia_helper->pushObject($this->algolia_registry->index_prefix.$taxonomy, $object);
        }

    }

    public function deleteTerm($term_id, $taxonomy)
    {
        $this->algolia_helper->deleteObject($this->algolia_registry->index_prefix.$taxonomy, $term_id);
    }

    public function indexPostsTypePart($type, $count, $offset)
    {
        $objects = $this->getPosts("AND post_type = '".$type."' ", "LIMIT ".($offset * $count).",".$count);

        $this->algolia_helper->pushObjects($this->algolia_registry->index_prefix.$type.'_temp', $objects);

        $this->algolia_helper->pushObjects($this->algolia_registry->index_prefix.'all_temp', $objects);
    }
}