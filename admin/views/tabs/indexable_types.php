<?php
    $excluded_types = $algolia_registry->excluded_types;

    $types = array();
    $i = 0;

    foreach (get_post_types() as $type)
    {
        if (in_array($type, $excluded_types))
            continue;

        $count = wp_count_posts($type)->publish;

        if ($count == 0)
            continue;

        $typeItem                           = new stdClass();

        $typeItem->name                     = $type;
        $typeItem->count                    = $count;

        $typeItem->order                    = is_array($algolia_registry->indexable_types) && in_array($type, array_keys($algolia_registry->indexable_types))
                                                ? $algolia_registry->indexable_types[$type]['order'] : 10000 + $i;

        $typeItem->autocompletable          = isset($algolia_registry->indexable_types[$type]) && $algolia_registry->indexable_types[$type]['autocompletable'];
        $typeItem->instantable              = isset($algolia_registry->indexable_types[$type]) && $algolia_registry->indexable_types[$type]['instantable'];
        $typeItem->nb_results_by_section    = isset($algolia_registry->indexable_types[$type]) && isset($algolia_registry->indexable_types[$type]['nb_results_by_section'])
                                                ? (int) $algolia_registry->indexable_types[$type]['nb_results_by_section'] : 3;

        $typeItem->label                    = isset($algolia_registry->indexable_types[$type]) ? $algolia_registry->indexable_types[$type]['name'] : "";

        $types[]                            = $typeItem;

        $i++;
    }

?>

<div class="tab-content" id="_indexable-types">
    <form action="<?php echo site_url(); ?>/wp-admin/admin-post.php" method="post">
        <input type="hidden" name="action" value="update_indexable_types">
        <div class="content-wrapper" id="customization">
            <div class="content">
                <h3>Wordpress Types</h3>
                <p class="help-block">
                    Configure here the Wordpress types you want index. The order of this setting reflects the order of the sections in the auto-completion menu.
                </p>
                <table>
                    <tr data-order="-1">
                        <th class="table-col-enabled">Auto-completion menu</th>
                        <th class="table-col-enabled">Instant search results page</th>
                        <th>Name</th>
                        <th class="table-col-enabled">Auto-completion suggestions</th>
                        <th>Auto-completion menu label</th>
                        <th></th>
                    </tr>

                    <?php foreach ($types as $typeItem) : ?>
                        <tr data-order="<?php echo $typeItem->order; ?>">

                            <td class="table-col-enabled">
                                <input type="checkbox"
                                       name="TYPES[<?php echo $typeItem->name; ?>][AUTOCOMPLETABLE]"
                                       value="<?php echo $typeItem->name; ?>"
                                    <?php checked($typeItem->autocompletable) ?>
                                    >
                            </td>

                            <td class="table-col-enabled">
                                <input type="checkbox"
                                       name="TYPES[<?php echo $typeItem->name; ?>][INSTANTABLE]"
                                       value="<?php echo $typeItem->name; ?>"
                                    <?php checked($typeItem->instantable) ?>
                                    >
                            </td>

                            <td>
                                <?php echo $typeItem->name; ?> (<?php echo $typeItem->count; ?>)
                            </td>


                            <td class="table-col-enabled">
                                <input type="number"
                                       name="TYPES[<?php echo $typeItem->name; ?>][NB_RESULTS_BY_SECTION]"
                                       value="<?php echo $typeItem->nb_results_by_section; ?>"
                                    >
                            </td>

                            <td style="white-space: nowrap;">
                                <input type="text" value="<?php echo $typeItem->label ?>" name="TYPES[<?php echo $typeItem->name; ?>][NAME]">
                            </td>

                            <td>
                                <img width="10" src="<?php echo $move_icon_url; ?>" style="float: right; margin-top: 10px">
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