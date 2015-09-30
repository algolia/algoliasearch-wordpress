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
        'post_author'	=> 'integer',
        'post_date'		=> 'datetime',
        'post_title'	=> 'string',
        'post_excerpt'	=> 'string',
        'post_name'		=> 'string',
        'post_modified' => 'datetime',
        'post_parent'	=> 'integer',
        'menu_order'	=> 'integer',
        'post_type'		=> 'string'
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

    public static function try_cast($value)
    {
        if (is_serialized($value))
            return @unserialize($value);

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

    public function getContent($data)
    {
        if ($data->post_type != "post" && $data->post_type != "page")
            return $data->post_content;

        $algolia_registry = \Algolia\Core\Registry::getInstance();

        $html = $data->post_content;

        if ($html == "")
            return;

        $content = array();

        $html = preg_replace( '/>(\s|\n|\r)+</', '><', $html);

        $html = str_get_html($html);

        $nodes = $html->root->nodes;

        while (count($nodes) == 1 && count($nodes[0]->nodes) >= 1)
            $nodes = $nodes[0]->nodes;

        $tags = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6');

        foreach ($tags as $tag)
            $content[$tag] = array();

        $excludedTags = array('hr');

        $order = 0;

        /** @var \simple_html_dom_node $child */
        foreach ($nodes as $child)
        {
            if (in_array($child->tag, $excludedTags))
                continue;

            if (in_array($child->tag, $tags))
                $content[$child->tag][] = array('order' => $order, 'value' => $this->strip($child->innertext()));
            else
                $content['text'][] = array('order' => $order, 'value' => $this->strip($child->innertext()));

            $order++;
        }

        if ($algolia_registry->enable_truncating)
        {
            $size = 0;

            $too_much = false;

            foreach (array_merge($tags, array("text")) as $tag)
            {
                foreach ($content[$tag] as $key => $tag_element)
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
                        unset($content[$tag][$key]);
                }
            }
        }

        return $content;
    }

    private function getAttribute($attribute, $data)
    {
        if ($attribute['group'] == 'Record attribute')
        {
            if (isset($this->contentFieldsNames[$attribute['name']]))
                return $this->cast($data->$attribute['name'], $this->contentFieldsNames[$attribute['name']]);

            if (in_array($attribute['name'], array('display_name', 'first_name', 'last_name', 'user_login')))
                return get_the_author_meta($attribute['name'], $data->post_author);

            if ($attribute['name'] === 'permalink')
                return get_permalink($data->ID);

            if ($attribute['name'] === 'post_content')
                return $this->getContent($data);

            if ($attribute['name'] === 'featureImage')
            {
                $thumbnail_id = get_post_thumbnail_id($data->ID);

                if ($thumbnail_id)
                    return $this->getImage($thumbnail_id);
            }

            return '';
        }

        if ($attribute['group'] == 'Taxonomy')
        {
            $terms = wp_get_post_terms($data->ID, $attribute['name']);

            if (count($terms) <= 0)
                return array();

            return array_map(function ($obj) {
                return $obj->name;
            }, $terms);
        }

        if (strpos($attribute['group'], 'Meta') !== false)
        {
            $value = get_post_meta($data->ID, $attribute['name']);

            if ($value == false)
                $value = '';

            if (is_array($value) && count($value) === 1)
                $value = $value[0];

            return $this->try_cast($value);
        }
    }

    public function getPostObj($data)
    {
        $algolia_registry = \Algolia\Core\Registry::getInstance();

        $obj = new \stdClass();

        $obj->objectID = $data->ID;

        foreach ($algolia_registry->attributesToIndex as $attribute)
            $obj->{$attribute['name']} = $this->getAttribute($attribute, $data);

        foreach ($algolia_registry->additionalAttributes as $attribute)
            $obj->{$attribute['name']} = $this->getAttribute($attribute, $data);

        if (has_filter('prepare_algolia_record'))
            $obj = apply_filters('prepare_algolia_record', $obj);

        return (array) $obj;
    }
}