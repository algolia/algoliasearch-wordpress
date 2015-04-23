<?php
    $extra_metas = array();
    $facet_types = array_merge(array("conjunctive" => "Conjunctive", "disjunctive" => "Disjunctive"), $current_theme->facet_types);
    $i = 0;

    foreach (get_post_types() as $type)
    {
        if (is_array($algolia_registry->indexable_types) && in_array($type, array_keys($algolia_registry->indexable_types)))
        {
            $metas = get_meta_key_list($type);

            if (isset($external_attrs[$type.'_attrs']))
                $metas = array_merge(get_meta_key_list($type), $external_attrs[$type.'_attrs']);

            foreach ($metas as $meta)
            {
                $metaItem                       = new stdClass();

                $metaItem->name                 = $meta;
                $metaItem->type                 = $type;

                $metaItem->order                = isset($algolia_registry->metas[$type]) && in_array($meta, array_keys($algolia_registry->metas[$type]))
                                                    ? $order = $algolia_registry->metas[$type][$meta]['order']
                                                    : 10000 + $i;

                $metaItem->enabled              = isset($algolia_registry->metas[$type])
                                                    && in_array($meta, array_keys($algolia_registry->metas[$type]))
                                                    && $algolia_registry->metas[$type][$meta]["indexable"];

                $metaItem->facetable            = isset($algolia_registry->metas[$type])
                                                    && isset($algolia_registry->metas[$type][$meta])
                                                    && $algolia_registry->metas[$type][$meta]["facetable"];

                $metaItem->facet_type           = isset($algolia_registry->metas[$type])
                                                    && in_array($meta, array_keys($algolia_registry->metas[$type]))
                                                    ? $algolia_registry->metas[$type][$meta]["type"]
                                                    : 'conjunctive';

                $metaItem->label                = isset($algolia_registry->metas[$type][$meta]) ? $algolia_registry->metas[$type][$meta]["name"] : "";

                $metaItem->custom_ranking       = isset($algolia_registry->metas[$type])
                                                    && in_array($meta, array_keys($algolia_registry->metas[$type]))
                                                    && $algolia_registry->metas[$type][$meta]['custom_ranking'];

                $metaItem->custom_ranking_order = isset($algolia_registry->metas[$type])
                                                    && in_array($meta, array_keys($algolia_registry->metas[$type]))
                                                    && $algolia_registry->metas[$type][$meta]['custom_ranking_order'];

                $metaItem->custom_ranking_sort  = isset($algolia_registry->metas[$type])
                                                    && in_array($meta, array_keys($algolia_registry->metas[$type]))
                                                    && $algolia_registry->metas[$type][$meta]['custom_ranking_sort'];

                $extra_metas[] = $metaItem;

                $i++;
            }
        }
    }


    $taxonomies = array();
    $i = 0;

    foreach (array_merge($algolia_registry->extras, get_taxonomies()) as $tax)
    {
        $count = wp_count_terms($tax, array('hide_empty' => false));

        if (false == in_array($tax, $algolia_registry->extras) && is_numeric($count) == false && intval($count) <= 0)
            continue;

        $taxItem                    = new stdClass();

        $taxItem->name              = $tax;
        $taxItem->order             = isset($algolia_registry->metas['tax']) && isset($algolia_registry->metas['tax'][$tax])
                                        && $algolia_registry->metas['tax'][$tax]['order']
                                        ? $algolia_registry->metas['tax'][$tax]['order']
                                        : 10000 + $i;

        $taxItem->extra             = in_array($tax, $algolia_registry->extras);
        $taxItem->enabled           = isset($algolia_registry->metas['tax']) && in_array($tax, array_keys($algolia_registry->metas['tax']));
        $taxItem->autocompletable   = isset($algolia_registry->metas['tax']) && isset($algolia_registry->metas['tax'][$tax])
                                        && $algolia_registry->metas['tax'][$tax]['autocompletable'];
        $taxItem->facetable         = isset($algolia_registry->metas['tax']) && isset($algolia_registry->metas['tax'][$tax])
                                        && $algolia_registry->metas['tax'][$tax]['facetable'];

        $taxItem->default_attribute = isset($algolia_registry->metas['tax'])
                                        && isset($algolia_registry->metas['tax'][$tax])
                                        && $algolia_registry->metas['tax'][$tax]['default_attribute'] != 0;

        $taxItem->label             = isset($algolia_registry->metas['tax']) && isset($algolia_registry->metas['tax'][$tax])
                                        ? $algolia_registry->metas['tax'][$tax]['name'] : '';

        $taxItem->facet_type        = isset($algolia_registry->metas['tax']) && isset($algolia_registry->metas['tax'][$tax])
                                        && $algolia_registry->metas['tax'][$tax]["type"]
                                        ? $algolia_registry->metas['tax'][$tax]["type"] : 'conjunctive';

        $taxonomies[] = $taxItem;

        $i++;
    }

?>

<div class="tab-content" id="_extra-metas">
    <form id="extra-metas-form" action="<?php echo site_url(); ?>/wp-admin/admin-post.php" method="post">
        <input type="hidden" name="action" value="update_extra_meta">
        <div class="content-wrapper" id="customization">
            <div class="content">
                <p class="help-block">
                    Configure here the attributes you want to include in your Algolia records.
                </p>

                <table id="extra-meta-and-taxonomies">
                    <tr data-order="-1">
                        <th class="table-col-enabled">Enabled</th>
                        <th>Type</th>
                        <th>Name</th>
                        <th>
                            <?php if (in_array('autocomplete', $algolia_registry->type_of_search)): ?>
                                Autocomplete section
                            <?php endif; ?>
                        </th>
                        <th>
                            <?php if (in_array('instant', $algolia_registry->type_of_search)): ?>
                                Facetable
                            <?php endif; ?>
                        </th>
                        <th>Facet type</th>
                        <th>
                            Label &amp; ordering
                        </th>
                    </tr>
                </table>

                <p class="help-block">
                    Configure here the attributes you want to include in your Algolia records.
                </p>

                <div>
                    <div data-tab="#extra-metas-attributes" class="title selected">Additional Attributes</div>
                    <div data-tab="#taxonomies"             class="title">Taxonomies</div>
                    <div style="clear: both"></div>
                </div>

                <div class="sub-tab-content" id="extra-metas-attributes">
                    <table>
                        <tr data-order="-1">
                            <th class="table-col-enabled">Enabled</th>
                            <th>Type</th>
                            <th>Meta key</th>
                            <th>
                                <?php if (in_array('autocomplete', $algolia_registry->type_of_search)): ?>
                                    Autocomplete section
                                <?php endif; ?>
                            </th>
                            <th>
                                <?php if (in_array('instant', $algolia_registry->type_of_search)): ?>
                                    Facetable
                                <?php endif; ?>
                            </th>
                            <th>Facet type</th>
                            <th>Facet label &amp; ordering</th>
                        </tr>
                        <?php foreach ($extra_metas as $metaItem) : ?>
                            <tr data-type="extra-meta" data-order="<?php echo $metaItem->order; ?>">

                                <td class="table-col-enabled">
                                    <input type="checkbox"
                                           name="TYPES[<?php echo $metaItem->type; ?>][METAS][<?php echo $metaItem->name; ?>][INDEXABLE]"
                                           value="<?php echo $metaItem->type; ?>"
                                            <?php checked($metaItem->enabled); ?>>
                                </td>
                                <td><?php echo $metaItem->type; ?></td>
                                <td><?php echo $metaItem->name; ?></td>
                                <td></td>
                                <td>
                                    <?php if (in_array('instant', $algolia_registry->type_of_search)): ?>
                                        <input type="checkbox"
                                               name="TYPES[<?php echo $metaItem->type; ?>][METAS][<?php echo $metaItem->name; ?>][FACETABLE]"
                                               value="1"
                                               <?php checked($metaItem->facetable); ?>>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <select name="TYPES[<?php echo $metaItem->type; ?>][METAS][<?php echo $metaItem->name; ?>][TYPE]">
                                        <?php foreach ($facet_types as $key => $value): ?>
                                            <?php if ($metaItem->facet_type == $key) : ?>
                                                <option selected="selected" value="<?php echo $key ?>"><?php echo $value; ?></option>
                                            <?php else : ?>
                                                <option value="<?php echo $key ?>"><?php echo $value; ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td style="white-space: nowrap;">
                                    <input type="text"
                                           value="<?php echo $metaItem->label ?>" name="TYPES[<?php echo $metaItem->type; ?>][METAS][<?php echo $metaItem->name; ?>][NAME]">
                                    <img width="10" src="<?php echo $move_icon_url; ?>">
                                </td>

                                <!-- PREVENT FROM ERASING CUSTOM RANKING -->
                                <?php $customs = array('custom_ranking' => 'CUSTOM_RANKING', 'custom_ranking_order' => 'CUSTOM_RANKING_ORDER', 'custom_ranking_sort' => 'CUSTOM_RANKING_SORT'); ?>
                                <?php foreach($customs as $custom_key => $custom_value): ?>
                                    <?php if ($metaItem->$custom_key): ?>
                                        <input type="hidden"
                                               name="TYPES[<?php echo $type; ?>][METAS][<?php echo $metaItem->name; ?>][<?php echo $custom_value; ?>]"
                                               value="<?php echo $algolia_registry->metas[$metaItem->type][$metaItem->name][$custom_key]; ?>"
                                            >
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <!-- /////// PREVENT FROM ERASING CUSTOM RANKING -->

                                <input type="hidden" name="TYPES[<?php echo $metaItem->type; ?>][METAS][<?php echo $metaItem->name; ?>][ORDER]" class="order" />
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <div class="sub-tab-content" id="taxonomies">
                    <table>
                        <tr data-order="-1">
                            <th class="table-col-enabled">Enabled</th>
                            <th></th>
                            <th>Name</th>
                            <?php if (in_array('autocomplete', $algolia_registry->type_of_search)): ?>
                                <th>Autocomplete section</th>
                            <?php endif; ?>
                            <?php if (in_array('instant', $algolia_registry->type_of_search)): ?>
                                <th>Facetable</th>
                            <?php endif; ?>
                            <th>Facet type</th>
                            <th>Facet label &amp; ordering</th>
                        </tr>

                        <?php foreach ($taxonomies as $taxItem) : ?>
                            <tr data-type="taxonomy" data-order="<?php echo $taxItem->order; ?>">
                                <td class="table-col-enabled">
                                    <?php if ($taxItem->extra == false): ?>
                                        <input type="checkbox"
                                               name="TAX[<?php echo $taxItem->name; ?>][SLUG]"
                                               value="<?php echo $taxItem->name; ?>"
                                            <?php checked($taxItem->enabled); ?>
                                            >
                                    <?php else: ?>
                                        <i class="dashicons dashicons-yes"></i>
                                        <input type="hidden" name="TAX[<?php echo $taxItem->name; ?>][SLUG]" value="<?php echo $taxItem->name; ?>">
                                    <?php endif; ?>
                                </td>
                                <td>*</td>
                                <td>
                                    <?php echo $taxItem->name; ?>
                                </td>
                                <td>
                                    <?php if (in_array('autocomplete', $algolia_registry->type_of_search)): ?>
                                        <?php if ($taxItem->default_attribute == false): ?>
                                            <input type="checkbox"
                                                   value="facetable"
                                                <?php checked($taxItem->autocompletable)
                                                ?>
                                                   name="TAX[<?php echo $taxItem->name; ?>][AUTOCOMPLETABLE]">
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (in_array('instant', $algolia_registry->type_of_search)): ?>
                                        <input type="checkbox"
                                               value="facetable"
                                               name="TAX[<?php echo $taxItem->name; ?>][FACETABLE]"
                                               <?php checked($taxItem->facetable)?>>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <select name="TAX[<?php echo $taxItem->name; ?>][FACET_TYPE]">
                                        <?php foreach ($facet_types as $key => $value): ?>
                                            <?php if ($taxItem->facet_type == $key) : ?>
                                                <option selected="selected" value="<?php echo $key ?>"><?php echo $value; ?></option>
                                            <?php else : ?>
                                                <option value="<?php echo $key ?>"><?php echo $value; ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td style="white-space: nowrap;">
                                    <input type="text" value="<?php echo $taxItem->label ?>" name="TAX[<?php echo $taxItem->name; ?>][NAME]">
                                    <img width="10" src="<?php echo $move_icon_url; ?>">
                                </td>
                                <input type="hidden" name="TAX[<?php echo $taxItem->name; ?>][ORDER]" class="order" />
                                </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <div class="content-item">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                </div>
            </div>
        </div>
    </form>
</div>