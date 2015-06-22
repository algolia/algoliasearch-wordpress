<?php namespace Algolia\Core;


class WordpressFetcher
{
    private $termFieldsNames = array(
        'term_taxonomy_id'	=> array ('label' => "objectID",        'type' => 'integer'),
        'term_id'			=> array ('label' => "termId",          'type' => 'integer'),
        'name'				=> array ('label' => "title",           'type' => 'string'),
        'slug'				=> array ('label' => "slug",            'type' => 'string'),
        'description'		=> array ('label' => "content",         'type' => 'string'),
        'parent'			=> array ('label' => "parent",          'type' => 'integer'),
        'count'				=> array ('label' => "postsRelated",    'type' => 'integer'),
        'taxonomy'			=> array ('label' => "taxonomy",        'type' => 'string'),
    );

    private $contentFieldsNames = array (
        'ID'			=> array ('label' => "objectID",   'type' => 'integer'),
        'post_author'	=> array ('label' => "authorId",   'type' => 'integer'),
        'post_date'		=> array ('label' => "date",       'type' => 'datetime'),
        'post_content'	=> array ('label' => "content",    'type' => 'string'),
        'post_title'	=> array ('label' => "title",      'type' => 'string'),
        'post_excerpt'	=> array ('label' => "excerpt",    'type' => 'string'),
        'post_name'		=> array ('label' => "slug",       'type' => 'string'),
        'post_modified' => array ('label' => "modified",   'type' => 'datetime'),
        'post_parent'	=> array ('label' => "parent",     'type' => 'integer'),
        'menu_order'	=> array ('label' => "menu_order", 'type' => 'integer'),
        'post_type'		=> array ('label' => "type",       'type' => 'string')
    );

    private function cast($value, $type)
    {
        if ($type == 'integer')
            return intval($value);
        if ($type == 'float')
            return floatval($value);
        if ($type == 'datetime')
            return strtotime($value, current_time('timestamp'));

        return $value; //utf8_encode ?
    }

    private function try_cast($value)
    {
        if (is_numeric($value) && floatval($value) == floatval(intval($value)))
            return intval($value);

        if (is_numeric($value))
            return floatval($value);

        return $value;
    }

    public function getImage($id)
    {
        $image_fields = array("ID" => "ID", "guid" => "file", "post_mime_type" => "mime_type");

        $indexable_image_size = get_intermediate_image_sizes();

        $uploadDir = wp_upload_dir();
        $uploadBaseUrl = $uploadDir['baseurl'];

        $image = new \stdClass();

        $post = get_post($id);

        foreach ($image_fields as $key => $value)
            $image->$value = $post->$key;

        $metas = get_post_meta($post->ID, '_wp_attachment_metadata', true);

        $image->width    = $metas["width"];
        $image->height   = $metas["height"];
        $image->file     = sprintf('%s/%s', $uploadBaseUrl, $metas["file"]);
        $image->sizes    = $metas["sizes"] ? $metas["sizes"] : array();

        foreach ($image->sizes as $size => &$sizeAttrs)
        {
            if (in_array($size, $indexable_image_size) == false)
            {
                unset($image->sizes[$size]);
                continue;
            }

            $baseFileUrl = str_replace(wp_basename($metas['file']), '', $metas['file']);

            $sizeAttrs['file'] = sprintf('%s/%s%s', $uploadBaseUrl, $baseFileUrl, $sizeAttrs['file']);
        }


        return $image;
    }

    private function strip($s)
    {
        $s = trim(preg_replace('/\s+/', ' ', $s));
        $s = preg_replace('/&nbsp;/', ' ', $s);
        $s = preg_replace('!\s+!', ' ', $s);
        return trim(strip_tags($s));
    }

    public function getTermObj($data)
    {
        $obj = new \stdClass();

        foreach ($this->termFieldsNames as $key => $value)
        {
            $name = $value["label"];
            $obj->$name = $this->cast($data->$key, $value["type"]);
        }

        $obj->permalink  = get_term_link($data);

        return (array) $obj;
    }

    public function getContent($data, &$obj)
    {
        if ($data->post_type != "post" && $data->post_type != "page")
            return;

        $algolia_registry = \Algolia\Core\Registry::getInstance();

        $html = $obj->content;

        if ($html == "")
            return;

        unset($obj->content);

        $html = preg_replace( '/>(\s|\n|\r)+</', '><', $html);

        $html = str_get_html($html);

        $nodes = $html->root->nodes;

        while (count($nodes) == 1 && count($nodes[0]->nodes) >= 1)
            $nodes = $nodes[0]->nodes;

        $obj->h1 = array();
        $obj->h2 = array();
        $obj->h3 = array();
        $obj->h4 = array();
        $obj->h5 = array();
        $obj->h6 = array();
        $obj->text = array();

        $tags = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6');
        $excludedTags = array('hr');

        $order = 0;

        /** @var \simple_html_dom_node $child */
        foreach ($nodes as $child)
        {
            if (in_array($child->tag, $excludedTags))
                continue;

            if (in_array($child->tag, $tags))
                $obj->{$child->tag}[] = array('order' => $order, 'value' => $this->strip($child->innertext()));
            else
                $obj->text[] = array('order' => $order, 'value' => $this->strip($child->innertext()));

            $order++;
        }

        if ($algolia_registry->enable_truncating)
        {
            $size = 0;

            $too_much = false;

            foreach (array_merge($tags, array("text")) as $tag)
            {
                foreach ($obj->$tag as $key => $tag_element)
                {
                    if (! $too_much)
                    {
                        $add_size = mb_strlen(json_encode($tag_element));

                        if ($size + $add_size <= $algolia_registry->truncate_size)
                            $size += $add_size;
                        else
                            $too_much = true;
                    }

                    if ($too_much)
                        unset($obj->{$tag}[$key]);
                }
            }
        }
    }

    public function getPostObj($data)
    {
        global $external_attrs;

        $algolia_registry = \Algolia\Core\Registry::getInstance();

        $obj = new \stdClass();

        foreach ($this->contentFieldsNames as $key => $value)
        {
            $name = $value["label"];
            $obj->$name = $this->cast($data->$key, $value["type"]);
        }

        $obj->author            = get_the_author_meta('display_name', $data->post_author);
        $obj->author_first_name = get_the_author_meta('first_name', $data->post_author);
        $obj->author_last_name  = get_the_author_meta('last_name', $data->post_author);
        $obj->author_login      = get_the_author_meta('user_login', $data->post_author);
        $obj->permalink         = get_permalink($data->ID);

        $this->getContent($data, $obj);

        unset($obj->excerpt);
        //$obj->excerpt           = my_excerpt($data->post_content, get_the_excerpt());

        $thumbnail_id = get_post_thumbnail_id($data->ID);

        $extra_attrs = array();

        if (isset($external_attrs[$data->post_type]))
            $extra_attrs = $external_attrs[$data->post_type]($data);


        if ($thumbnail_id)
            $obj->featureImage = $this->getImage($thumbnail_id);

        if ($algolia_registry->metas && isset($algolia_registry->metas[$data->post_type]) && is_array($algolia_registry->metas[$data->post_type]))
        {
            foreach (get_post_meta($data->ID) as $meta_key => $meta_value)
                if (in_array($meta_key, array_keys($algolia_registry->metas[$data->post_type])))
                    if ($algolia_registry->metas[$data->post_type][$meta_key]["indexable"])
                        $obj->$meta_key = $this->try_cast($meta_value[0]);

            foreach ($extra_attrs as $meta_key => $meta_value)
                if (in_array($meta_key, array_keys($algolia_registry->metas[$data->post_type])))
                    if ($algolia_registry->metas[$data->post_type][$meta_key]["indexable"])
                        $obj->$meta_key = $this->try_cast($meta_value);
        }


        foreach (get_post_taxonomies($data->ID) as $tax)
        {
            $terms = wp_get_post_terms($data->ID, $tax);

            if (count($terms) <= 0)
                continue;

            if (isset($algolia_registry->metas['tax']) && isset($algolia_registry->metas['tax'][$tax]))
            {
                $obj->$tax = array_map(function ($obj) {
                    return $obj->name;
                }, $terms);
            }
        }

        return (array) $obj;
    }
}