<div class="tab-content" id="_custom-ranking">
    <form action="<?php echo site_url(); ?>/wp-admin/admin-post.php" method="post">
        <input type="hidden" name="action" value="custom_ranking">
        <div class="content-wrapper" id="customization">
            <div class="content">
                <p class="help-block">Configure here the attributes used to reflect the popularity of your records (number of likes, number of views, number of sales...).</p>
                <table>
                    <tr data-order="-1">
                        <th class="table-col-enabled">Enabled</th>
                        <th>Meta key</th>
                        <th>Sort order</th>
                    </tr>

                    <?php
                    $custom_rankings = array();

                    if (isset($algolia_registry->metas['tax']))
                    {
                        foreach (array_keys($algolia_registry->metas['tax']) as $tax)
                        {
                            if ($tax != 'type')
                            {
                                if (isset($custom_ranking['tax']) == false)
                                    $custom_ranking['tax'] = array();

                                $custom_rankings['tax'][] = $tax;
                            }
                        }
                    }

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
                                {
                                    if (isset($custom_rankings[$type]) == false)
                                        $custom_rankings[$type] = array();

                                    $custom_rankings[$type][] = $meta_key;
                                }
                    }

                    ?>
                    <?php $i = 0; $n = 0; ?>
                    <?php foreach($custom_rankings as $type => $values): ?>
                        <?php foreach($values as $meta_key): ?>
                            <?php
                            $order = -1;
                            ++$n;
                            if (isset($algolia_registry->metas[$type]) && in_array($meta_key, array_keys($algolia_registry->metas[$type])))
                                $order = $algolia_registry->metas[$type][$meta_key]['custom_ranking_sort'];
                            ?>
                            <?php if ($order != -1): ?>
                                <tr data-order="<?php echo $order; ?>">
                            <?php else: ?>
                                <tr data-order="<?php echo (10000 + $i); $i++ ?>">
                            <?php endif; ?>
                            <td>
                                <input type="checkbox"
                                       name="TYPES[<?php echo $type; ?>][METAS][<?php echo $meta_key; ?>][CUSTOM_RANKING]"
                                    <?php checked(isset($algolia_registry->metas[$type])
                                        && in_array($meta_key, array_keys($algolia_registry->metas[$type]))
                                        && $algolia_registry->metas[$type][$meta_key]["custom_ranking"]); ?>
                                    />
                            </td>
                            <td data-type="<?php echo $type; ?>"><?php echo $meta_key; ?></td>
                            <td style="white-space: nowrap;">
                                <select name="TYPES[<?php echo $type; ?>][METAS][<?php echo $meta_key; ?>][CUSTOM_RANKING_ORDER]">
                                    <?php foreach (array('asc' => 'Ascending', 'desc' => 'Descending') as $key => $value): ?>
                                        <?php if (isset($algolia_registry->metas[$type])
                                            && in_array($meta_key, array_keys($algolia_registry->metas[$type]))
                                            && $algolia_registry->metas[$type][$meta_key]["custom_ranking_order"] == $key): ?>
                                            <option selected value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                        <?php else : ?>
                                            <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                <img width="10" src="<?php echo plugin_dir_url(__FILE__); ?>../../imgs/move.png">
                            </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </table>
                <div class="_content-item">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                </div>
            </div>
        </div>
    </form>
</div>