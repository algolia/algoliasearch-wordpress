<?php

/*
 * This class is responsible of the following events:
 *  - before_delete_post
 *  - transition_post_status
 *  - save_post
 *  - edited_term_taxonomy
 *  - created_term
 *  - delete_term
 * and triggers the indexer to make sure the DB is synchronized with Algolia
 */
class AlgoliaPluginAuto
{
    private $algolia_registry;
    private $algolia_helper;
    private $indexer;

    public function __construct()
    {
        $this->algolia_registry = \Algolia\Core\Registry::getInstance();

        if ($this->algolia_registry->validCredential == false)
            return;

        $this->algolia_helper   = new \Algolia\Core\AlgoliaHelper(
            $this->algolia_registry->app_id,
            $this->algolia_registry->search_key,
            $this->algolia_registry->admin_key
        );


        $this->indexer = new \Algolia\Core\Indexer();

        if ($this->algolia_registry->validCredential)
        {
            add_action('before_delete_post',       array($this, 'postDeleted'));
            add_action('transition_post_status',   array($this, 'postUnpublished')      , 10, 3);
            add_action('save_post',                array($this, 'postUpdated')          , 11, 3);
            add_action('edited_term_taxonomy',     array($this, 'termTaxonomyUpdated')  , 10, 2);
            add_action('created_term',             array($this, 'termCreated')          , 10, 3);
            add_action('delete_term',              array($this, 'termDeleted')          , 10, 4);
        }
    }

    public function postDeleted($post_id)
    {
        if (! empty($post_id))
        {
            $post = get_post($post_id);
            $this->indexer->deletePost($post_id, $post->post_type);
        }
    }

    public function postUnpublished($new_status, $old_status, $post)
    {
        if ($post->post_password != "")
            return $post->ID;

        if ($old_status == 'publish' && $new_status != 'publish' && ! empty($post->ID))
            $this->indexer->deletePost($post->ID, $post->post_type);
    }

    public function postUpdated($post_id, $post)
    {
        if (wp_is_post_revision($post_id))
            return $post_id;

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return $post_id;

        if ($post->post_status != 'publish')
            return $post_id;

        if ($post->post_password != "")
            return $post_id;

        $autocompleteTypes = $this->algolia_registry->autocompleteTypes;
        $instantTypes = $this->algolia_registry->instantTypes;

        $count1 = count(array_filter($autocompleteTypes, function ($item) use ($post) { return $item['name'] == $post->post_type; }));
        $count2 = count(array_filter($instantTypes, function ($item) use ($post) { return $item['name'] == $post->post_type; }));

        if ($count1 > 0 || $count2 > 0)
            $this->indexer->indexPost($post, $count1 > 0, $count2 > 0);
    }

    public function termTaxonomyUpdated($term_id, $taxonomy)
    {
        $additionalAttributes = $this->algolia_registry->additionalAttributes;

        if (count(array_filter($additionalAttributes, function($item) use($taxonomy) { return $item['group'] == 'Taxonomy' && $item['name'] == $taxonomy; })) > 0)
            $this->indexer->indexTerm(get_term_by('term_taxonomy_id', $term_id, $taxonomy), $taxonomy);
    }

    public function termCreated($term_id, $tt_id, $taxonomy)
    {
        $additionalAttributes = $this->algolia_registry->additionalAttributes;

        if (count(array_filter($additionalAttributes, function($item) use($taxonomy) { return $item['group'] == 'Taxonomy' && $item['name'] == $taxonomy; })) > 0)
                $this->indexer->indexTerm(get_term($term_id, $taxonomy), $taxonomy);
    }

    public function termDeleted($term_id, $tt_id, $taxonomy, $deleted_term)
    {
        $additionalAttributes = $this->algolia_registry->additionalAttributes;

        if (count(array_filter($additionalAttributes, function($item) use($taxonomy) { return $item['group'] == 'Taxonomy' && $item['name'] == $taxonomy; })) > 0)
               $this->indexer->deleteTerm($term_id, $taxonomy);
    }
}