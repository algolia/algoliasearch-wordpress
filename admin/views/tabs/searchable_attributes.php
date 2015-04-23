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
                    $searchable = array();

                    if (isset($algolia_registry->metas['tax']))
                        foreach (array_keys($algolia_registry->metas['tax']) as $tax)
                            if ($tax != 'type')
                                $searchable[] = $tax;

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
                                    $searchable[] = $meta_key;
                    }

                    if (isset($algolia_registry->date_custom_ranking['enabled']) && $algolia_registry->date_custom_ranking['enabled'])
                        $searchable[] = 'date';

                    $i = 0;
                    ?>
                    <?php foreach ($searchable as $searchItem): ?>
                        <?php
                        $order = -1;
                        if (isset($algolia_registry->searchable[$searchItem]))
                            $order = $algolia_registry->searchable[$searchItem]['order'];
                        ?>
                        <?php if ($order != -1): ?>
                            <tr data-order="<?php echo $order; ?>">
                        <?php else: ?>
                            <tr data-order="<?php echo (10000 + $i); $i++ ?>">
                        <?php endif; ?>
                        <td class="table-col-enabled"><input <?php checked(isset($algolia_registry->searchable[$searchItem])); ?> type="checkbox" name="ATTRIBUTES[<?php echo $searchItem; ?>][SEARCHABLE]"></td>
                        <td>
                            <?php echo $searchItem; ?>
                        </td>
                        <td style="white-space: nowrap;">
                            <select name="ATTRIBUTES[<?php echo $searchItem; ?>][ORDERED]">
                                <?php foreach (array("ordered" => "Ordered", "unordered" => "Unordered") as $key => $value): ?>
                                    <?php if (isset($algolia_registry->searchable[$searchItem]) && $algolia_registry->searchable[$searchItem]['ordered'] == $key): ?>
                                        <option selected value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                    <?php else: ?>
                                        <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            <img width="10" src="<?php echo plugin_dir_url(__FILE__); ?>../../imgs/move.png">
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