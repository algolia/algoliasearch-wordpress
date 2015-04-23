<?php
    $excluded_types = array('revision', 'nav_menu_item', 'acf', 'product_variation', 'shop_order', 'shop_order_refund', 'shop_coupon', 'shop_webhook', 'wooframework');
    $types = array();
    $i = 0;

    foreach (get_post_types() as $type)
    {
        $count = wp_count_posts($type)->publish;

        if ($count == 0 || in_array($type, $excluded_types))
            continue;

        $typeItem           = new stdClass();

        $typeItem->name     = $type;
        $typeItem->count    = $count;

        $typeItem->order    = is_array($algolia_registry->indexable_types) && in_array($type, array_keys($algolia_registry->indexable_types))
                                ? $algolia_registry->indexable_types[$type]['order'] : 10000 + $i;

        $typeItem->checked  = is_array($algolia_registry->indexable_types) && in_array($type, array_keys($algolia_registry->indexable_types));

        $typeItem->label    = isset($algolia_registry->indexable_types[$type]) ? $algolia_registry->indexable_types[$type]['name'] : "";

        $types[]            = $typeItem;

        $i++;
    }
?>

<div class="tab-content" id="_indexable-types">
    <form action="<?php echo site_url(); ?>/wp-admin/admin-post.php" method="post">
        <input type="hidden" name="action" value="update_indexable_types">
        <div class="content-wrapper" id="customization">
            <div class="content">
                <p class="help-block">Configure here the Wordpress types you want index.</p>
                <table>
                    <tr data-order="-1">
                        <th class="table-col-enabled">Enabled</th>
                        <th>Name</th>
                        <?php if (in_array('autocomplete', $algolia_registry->type_of_search)): ?>
                            <th>Auto-completion menu label &amp; ordering</th>
                        <?php endif; ?>
                    </tr>

                    <?php foreach ($types as $typeItem) : ?>
                        <tr data-order="<?php echo $typeItem->order; ?>">
                            <td class="table-col-enabled">
                                <input type="checkbox"
                                       name="TYPES[<?php echo $typeItem->name; ?>][SLUG]"
                                       value="<?php echo $typeItem->name; ?>"
                                    <?php checked($typeItem->checked) ?>
                                    >
                            </td>
                            <td>
                                <?php echo $typeItem->name; ?> (<?php echo $typeItem->count; ?>)
                            </td>
                            <?php if (in_array('autocomplete', $algolia_registry->type_of_search)): ?>
                            <td style="white-space: nowrap;">
                                <input type="text" value="<?php echo $typeItem->label ?>" name="TYPES[<?php echo $typeItem->name; ?>][NAME]">
                                <img width="10" src="<?php echo $move_icon_url; ?>">
                            </td>
                            <?php endif; ?>
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