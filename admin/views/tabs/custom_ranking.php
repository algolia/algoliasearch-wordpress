<?php
    $i = 0;

    global $batch_count;

    $custom_rankings = array();

    if (isset($algolia_registry->metas['tax']))
    {
        foreach (array_keys($algolia_registry->metas['tax']) as $tax)
        {
            if ($tax != 'type')
            {
                if (isset($custom_ranking['tax']) == false)
                    $custom_ranking['tax'] = array();

                $rankable           = new stdClass();
                $rankable->name     = $tax;
                $rankable->type     = 'tax';

                $rankable->order    = isset($algolia_registry->metas['tax']) && in_array($tax, array_keys($algolia_registry->metas['tax']))
                                        ? $algolia_registry->metas['tax'][$tax]['custom_ranking_sort']
                                        : 10000 + $i;

                $rankable->checked  = isset($algolia_registry->metas['tax'])
                                        && in_array($tax, array_keys($algolia_registry->metas['tax']))
                                        && $algolia_registry->metas['tax'][$tax]["custom_ranking"];

                $rankable->sort     = isset($algolia_registry->metas['tax'])
                                        && in_array($tax, array_keys($algolia_registry->metas['tax']))
                                        && $algolia_registry->metas['tax'][$tax]["custom_ranking_order"]
                                        ? $algolia_registry->metas['tax'][$tax]["custom_ranking_order"]
                                        : 'asc';


                $custom_rankings[] = $rankable;

                $i++;
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
            {
                if (isset($algolia_registry->metas[$type])
                    && in_array($meta_key, array_keys($algolia_registry->metas[$type]))
                    && $algolia_registry->metas[$type][$meta_key]["indexable"])
                {
                    $rankable           = new stdClass();
                    $rankable->name     = $meta_key;
                    $rankable->type     = $type;

                    $rankable->order    = isset($algolia_registry->metas[$type]) && in_array($meta_key, array_keys($algolia_registry->metas[$type]))
                                            ? $algolia_registry->metas[$type][$meta_key]['custom_ranking_sort']
                                            : 10000 + $i;

                    $rankable->checked  = isset($algolia_registry->metas[$type])
                                            && in_array($meta_key, array_keys($algolia_registry->metas[$type]))
                                            && $algolia_registry->metas[$type][$meta_key]["custom_ranking"];

                    $rankable->sort     = isset($algolia_registry->metas[$type])
                                            && in_array($meta_key, array_keys($algolia_registry->metas[$type]))
                                            && $algolia_registry->metas[$type][$meta_key]["custom_ranking_order"]
                                            ? $algolia_registry->metas[$type][$meta_key]["custom_ranking_order"]
                                            : 'asc';

                    $custom_rankings[] = $rankable;

                    $i++;
                }
            }
        }
    }


?>

<div class="tab-content" id="_custom-ranking">
    <form action="<?php echo site_url(); ?>/wp-admin/admin-post.php" method="post">
        <input type="hidden" name="action" value="custom_ranking">
        <div class="content-wrapper" id="customization">
            <div class="content">
                <h3>Ranking Configuration</h3>
                <p class="help-block">Configure here the attributes used to reflect the popularity of your records (number of likes, number of views, number of sales...).</p>
                <table>
                    <tr data-order="-1">
                        <th class="table-col-enabled">Enabled</th>
                        <th>Meta key</th>
                        <th>Sort order</th>
                    </tr>

                    <?php foreach($custom_rankings as $rankable): ?>
                        <tr data-order="<?php echo $rankable->order; ?>">
                            <td>
                                <input type="checkbox"
                                       name="TYPES[<?php echo $rankable->type; ?>][METAS][<?php echo $rankable->name; ?>][CUSTOM_RANKING]"
                                    <?php checked($rankable->checked); ?>
                                    />
                            </td>
                            <td data-type="<?php echo $rankable->type; ?>"><?php echo $rankable->name; ?></td>
                            <td style="white-space: nowrap;">
                                <select name="TYPES[<?php echo $rankable->type; ?>][METAS][<?php echo $rankable->name; ?>][CUSTOM_RANKING_ORDER]">
                                    <?php foreach (array('asc' => 'Ascending', 'desc' => 'Descending') as $key => $value): ?>
                                            <option <?php echo ($rankable->sort == $key ? 'selected' : ''); ?> value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <img width="10" src="<?php echo $move_icon_url; ?>" style="float: right; margin-top: 10px" />
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <div class="_content-item">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                </div>
            </div>
        </div>
    </form>
</div>