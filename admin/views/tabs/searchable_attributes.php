<?php
    $i = 0;

    $searchable = array();

    if (isset($algolia_registry->metas['tax']))
    {
        foreach (array_keys($algolia_registry->metas['tax']) as $tax)
        {
            if ($tax != 'type')
            {
                $searchItem             = new stdClass();

                $searchItem->name       = $tax;
                $searchItem->order      = isset($algolia_registry->searchable[$tax]) ? $algolia_registry->searchable[$tax]['order'] : 10000 + $i;
                $searchItem->checked    = isset($algolia_registry->searchable[$tax]);
                $searchItem->sort       = isset($algolia_registry->searchable[$tax]) && $algolia_registry->searchable[$tax]['ordered'] ? $algolia_registry->searchable[$tax]['ordered'] : 'asc';

                $searchable[] = $searchItem;

                $i++;
            }
        }


    }

    foreach (get_post_types() as $type)
    {
        $metas = get_meta_key_list($type);

        if (isset($external_attrs[$type.'_attrs']))
            $metas = array_merge($metas, $external_attrs[$type.'_attrs']);


        foreach ($metas as $meta_key)
        {
            if (is_array($algolia_registry->indexable_types) && in_array($type, array_keys($algolia_registry->indexable_types)))
            {
                if (isset($algolia_registry->metas[$type])
                    && in_array($meta_key, array_keys($algolia_registry->metas[$type]))
                    && $algolia_registry->metas[$type][$meta_key]["indexable"])
                {
                    $searchItem             = new stdClass();

                    $searchItem->name       = $meta_key;
                    $searchItem->order      = isset($algolia_registry->searchable[$meta_key]) ? $algolia_registry->searchable[$meta_key]['order'] : 10000 + $i;
                    $searchItem->checked    = isset($algolia_registry->searchable[$tax]);
                    $searchItem->sort       = isset($algolia_registry->searchable[$meta_key]) && $algolia_registry->searchable[$meta_key]['ordered'] ? $algolia_registry->searchable[$meta_key]['ordered'] : 'asc';

                    $searchable[]           = $searchItem;

                    $i++;
                }
            }
        }
    }

?>

<div class="tab-content" id="_searchable_attributes">
    <form action="<?php echo site_url(); ?>/wp-admin/admin-post.php" method="post">
        <input type="hidden" name="action" value="update_searchable_attributes">
        <div class="content-wrapper" id="customization">
            <div class="content">
                <p class="help-block">Configure here the attributes you want to be able to search in. The order of this setting matters as those at the top of the list are considered more important.</p>
                <table>
                    <tr data-order="-1">
                        <th class="table-col-enabled">Enabled</th>
                        <th>Name</th>
                        <th>Attribute ordering</th>
                    </tr>
                    <?php

                    ?>
                    <?php foreach ($searchable as $searchItem): ?>
                        <tr data-order="<?php echo $searchItem->order; ?>">
                            <td class="table-col-enabled"><input <?php checked($searchItem->checked); ?> type="checkbox" name="ATTRIBUTES[<?php echo $searchItem->name; ?>][SEARCHABLE]"></td>
                            <td>
                                <?php echo $searchItem->name; ?>
                            </td>
                            <td style="white-space: nowrap;">
                                <select name="ATTRIBUTES[<?php echo $searchItem->name; ?>][ORDERED]">
                                    <?php foreach (array("ordered" => "Ordered", "unordered" => "Unordered") as $key => $value): ?>
                                        <?php if ($searchItem->sort == $key): ?>
                                            <option selected value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                        <?php else: ?>
                                            <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                <img width="10" src="<?php echo $move_icon_url; ?>">
                            </td>
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