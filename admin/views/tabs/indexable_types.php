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

                    <?php $i = 0; ?>

                    <?php foreach (get_post_types() as $type) : ?>
                        <?php $count = wp_count_posts($type)->publish; ?>
                        <?php if ($count == 0 || in_array($type, array('revision', 'nav_menu_item', 'acf', 'product_variation', 'shop_order', 'shop_order_refund', 'shop_coupon', 'shop_webhook', 'wooframework'))) { continue; } ?>
                        <?php
                        $order = -1;
                        if (is_array($algolia_registry->indexable_types) && in_array($type, array_keys($algolia_registry->indexable_types)))
                            $order = $algolia_registry->indexable_types[$type]['order'];
                        ?>
                        <?php if ($order != -1): ?>
                            <tr data-order="<?php echo $order; ?>">
                        <?php else: ?>
                            <tr data-order="<?php echo (10000 + $i); $i++ ?>">
                        <?php endif; ?>
                        <td class="table-col-enabled">
                            <input type="checkbox"
                                   name="TYPES[<?php echo $type; ?>][SLUG]"
                                   value="<?php echo $type; ?>"
                                <?php checked(is_array($algolia_registry->indexable_types) && in_array($type, array_keys($algolia_registry->indexable_types))) ?>
                                >
                        </td>
                        <td>
                            <?php echo $type; ?> (<?php echo $count; ?>)
                        </td>
                        <?php if (in_array('autocomplete', $algolia_registry->type_of_search)): ?>
                            <td style="white-space: nowrap;">
                                <input type="text" value="<?php echo (isset($algolia_registry->indexable_types[$type]) ? $algolia_registry->indexable_types[$type]['name'] : "") ?>" name="TYPES[<?php echo $type; ?>][NAME]">
                                <img width="10" src="<?php echo plugin_dir_url(__FILE__); ?>../../imgs/move.png">
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