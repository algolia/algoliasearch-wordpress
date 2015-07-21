<?php
    global $batch_count;

    $sortable = array();

    $i = 0;

    if (isset($algolia_registry->metas['tax']))
    {
        foreach (array_keys($algolia_registry->metas['tax']) as $tax)
        {
            if ($tax != 'type')
            {
                foreach (array('asc', 'desc') as $sort)
                {
                    $sortItem           = new stdClass();
                    $sortItem->name     = $tax;
                    $sortItem->sort     = $sort;
                    $sortItem->order    = isset($algolia_registry->sortable[$tax.'_'.$sort]) ? $order = $algolia_registry->sortable[$tax.'_'.$sort]['order'] : 10000 + $i;
                    $sortItem->checked  = isset($algolia_registry->sortable[$tax.'_'.$sort]);
                    $sortItem->label    = isset($algolia_registry->sortable[$tax.'_'.$sort]) ? $algolia_registry->sortable[$tax.'_'.$sort]["label"] : "";

                    $sortable[] = $sortItem;

                    $i++;
                }
            }
        }
    }

    foreach (get_post_types() as $type)
    {
        $type_count = floor(get_meta_key_list_count($type) / $batch_count);

        $metas = array();

        for ($offset = 0; $offset <= $type_count; $offset++)
            $metas = array_merge($metas, get_meta_key_list($type, $offset * $batch_count, $batch_count));

        foreach ($metas as $meta_key)
        {
            if (is_array($algolia_registry->indexable_types) && in_array($type, array_keys($algolia_registry->indexable_types)))
                if (isset($algolia_registry->metas[$type])
                    && in_array($meta_key, array_keys($algolia_registry->metas[$type]))
                    && $algolia_registry->metas[$type][$meta_key]["indexable"])
                {
                    foreach (array('asc', 'desc') as $sort)
                    {
                        $sortItem           = new stdClass();
                        $sortItem->name     = $meta_key;
                        $sortItem->sort     = $sort;
                        $sortItem->order    = isset($algolia_registry->sortable[$meta_key.'_'.$sort]) ? $order = $algolia_registry->sortable[$meta_key.'_'.$sort]['order'] : 10000 + $i;
                        $sortItem->checked  = isset($algolia_registry->sortable[$meta_key.'_'.$sort]);
                        $sortItem->label    = isset($algolia_registry->sortable[$meta_key.'_'.$sort]) ? $algolia_registry->sortable[$meta_key.'_'.$sort]["label"] : "";

                        $sortable[] = $sortItem;
                        $i++;
                    }
                }
        }
    }
?>

<div class="tab-content" id="_sortable_attributes">
    <form id="sortable-form" action="<?php echo site_url(); ?>/wp-admin/admin-post.php" method="post">
        <input type="hidden" name="action" value="update_sortable_attributes">
        <div class="content-wrapper" id="customization">
            <div class="content">
                <h3>Sorting Configuration</h3>
                <p class="help-block">By default results are sorted by text relevance &amp; by the ranking criteria. Configure here the attributes you want to use for the additional sorts (by price, by date, etc...).</p>
                <table>
                    <tr data-order="-1">
                        <th class="table-col-enabled">Enabled</th>
                        <th>Name</th>
                        <th>Sort</th>
                        <th>Label</th>
                    </tr>

                    <?php foreach ($sortable as $sortItem): ?>
                        <tr data-order="<?php echo $sortItem->order; ?>">
                            <td class="table-col-enabled">
                                <input <?php checked($sortItem->checked); ?> type="checkbox" name="ATTRIBUTES[<?php echo $sortItem->name; ?>][<?php echo $sortItem->sort; ?>]">
                            </td>
                            <td>
                                <?php echo $sortItem->name; ?>
                            </td>
                            <td>
                                <span class="dashicons dashicons-arrow-<?php echo($sortItem->sort == 'asc' ? 'up' : 'down'); ?>-alt"></span>
                                <?php echo($sortItem->sort == 'asc' ? 'Ascending' : 'Descending'); ?>
                            </td>
                            <td>
                                <input type="text"
                                       value="<?php echo $sortItem->label ?>" name="ATTRIBUTES[<?php echo $sortItem->name; ?>][LABEL_<?php echo $sortItem->sort; ?>]">
                                <img width="10" src="<?php echo $move_icon_url; ?>" style="float: right; margin-top: 10px" />
                            </td>
                            <input type="hidden" name="ATTRIBUTES[<?php echo $sortItem->name; ?>][ORDER_<?php echo $sortItem->sort ?>]" class="order" />
                        </tr>
                    <?php endforeach; ?>
                </table>
                <div class="content-item">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                </div>
            </div>
        </div>
    </form>
</div>