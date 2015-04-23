<div class="tab-content" id="_sortable_attributes">
    <form id="sortable-form" action="<?php echo site_url(); ?>/wp-admin/admin-post.php" method="post">
        <input type="hidden" name="action" value="update_sortable_attributes">
        <div class="content-wrapper" id="customization">
            <div class="content">
                <p class="help-block">By default results are sorted by text relevance &amp; your ranking criteria. Configure here the attributes you want to use for the additional sorts (by price, by date, etc...).</p>
                <table>
                    <tr data-order="-1">
                        <th class="table-col-enabled">Enabled</th>
                        <th>Name</th>
                        <th>Sort</th>
                        <th>Label</th>
                    </tr>
                    <?php
                    $sortable = array();

                    if (isset($algolia_registry->metas['tax']))
                        foreach (array_keys($algolia_registry->metas['tax']) as $tax)
                            if ($tax != 'type')
                                $sortable[] = $tax;

                    foreach (get_post_types() as $type)
                    {
                        $metas = get_meta_key_list($type);

                        if (isset($external_attrs[$type.'_attrs']))
                            $metas = array_merge($metas, $external_attrs[$type.'_attrs']);


                        foreach ($metas as $meta_key)
                            if (is_array($algolia_registry->indexable_types) && in_array($type, array_keys($algolia_registry->indexable_types)))
                                if (isset($algolia_registry->metas[$type])
                                    && in_array($meta_key, array_keys($algolia_registry->metas[$type]))
                                    && $algolia_registry->metas[$type][$meta_key]["indexable"])
                                    $sortable[] = $meta_key;
                    }

                    $i = 0;

                    ?>
                    <?php foreach ($sortable as $sortItem): ?>
                        <?php foreach (array('asc', 'desc') as $sort): ?>

                            <?php
                            $order = -1;
                            if (isset($algolia_registry->sortable[$sortItem.'_'.$sort]))
                                $order = $algolia_registry->sortable[$sortItem.'_'.$sort]['order'];
                            ?>
                            <?php if ($order != -1): ?>
                                <tr data-order="<?php echo $order; ?>">
                            <?php else: ?>
                                <tr data-order="<?php echo (10000 + $i); $i++ ?>">
                            <?php endif; ?>
                            <td class="table-col-enabled">
                                <input <?php checked(isset($algolia_registry->sortable[$sortItem.'_'.$sort])); ?> type="checkbox" name="ATTRIBUTES[<?php echo $sortItem; ?>][<?php echo $sort; ?>]">
                            </td>
                            <td>
                                <?php echo $sortItem; ?>
                            </td>
                            <td>
                                <span class="dashicons dashicons-arrow-<?php echo($sort == 'asc' ? 'up' : 'down'); ?>-alt"></span>
                                <?php echo($sort == 'asc' ? 'Ascending' : 'Descending'); ?>
                            </td>
                            <td>
                                <input type="text"
                                       value="<?php echo (isset($algolia_registry->sortable[$sortItem.'_'.$sort]) ? $algolia_registry->sortable[$sortItem.'_'.$sort]["label"] : "") ?>" name="ATTRIBUTES[<?php echo $sortItem; ?>][LABEL_<?php echo $sort; ?>]">
                                <img width="10" src="<?php echo plugin_dir_url(__FILE__); ?>../../imgs/move.png">
                            </td>
                            <input type="hidden" name="ATTRIBUTES[<?php echo $sortItem; ?>][ORDER_<?php echo $sort ?>]" class="order" />
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </table>
                <div class="content-item">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                </div>
            </div>
        </div>
    </form>
</div>