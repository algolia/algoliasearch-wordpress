<?php namespace Algolia\Core;

class QueryReplacer
{
    private $algolia_helper;
    private $algolia_registry;

    private $total_result_count;
    private $num_pages;
    private $page;

    private $ids = array();

    public function __construct(AlgoliaHelper $algolia_helper)
    {
        $this->algolia_helper = $algolia_helper;
        $this->algolia_registry = Registry::getInstance();
    }

    public function search($query)
    {
        if (function_exists('is_main_query') && ! $query->is_main_query())
            return $query;

        if (is_search() && ! is_admin())
        {

            if (in_array('instant', $this->algolia_registry->type_of_search))
            {
                $url = '/#q=w&page=0&refinements=%5B%5D&numerics_refinements=%7B%7D&index_name=%22'.$this->algolia_registry->index_name.'all%22';
                header('Location: '.$url);

                die();
            }

            $algolia_query = get_search_query(false);

            $options = array(
                'hitsPerPage'   => $this->algolia_registry->number_by_page,
                'page'          => get_query_var('paged') ? get_query_var('paged') - 1 : 0
            );

            $results = $this->algolia_helper->search($algolia_query, $options, $this->algolia_registry->index_name.'all');

            foreach ($results['hits'] as $result)
                $this->ids[] = $result['objectID'];

            $this->num_pages            = $results['nbPages'];
            $this->total_result_count   = $results['nbHits'];
            $this->page                 = $results['page'];

            $query->query = array();

            set_query_var('post__in', $this->ids);
            set_query_var('post_type', null);
            set_query_var('s', null);
            set_query_var('paged', null);

            return $query;
        }

        return $query;
    }

    public function getOrderedPost($posts)
    {
        if(! is_search())
            return $posts;

        global $wp_query;

        set_query_var('paged', $this->page + 1);

        $wp_query->max_num_pages    = $this->num_pages;
        $wp_query->found_posts      = $this->total_result_count;

        $lookup_table = array();

        foreach ($posts as $post)
            $lookup_table[$post->ID] = $post;

        $ordered_posts = array();

        foreach ($this->ids as $id)
            if (isset($lookup_table[$id]))
                $ordered_posts[] = $lookup_table[$id];

        return $ordered_posts;
    }
}