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
                        <?php

                        $i = 0;

                        $facet_types = array_merge(array("conjunctive" => "Conjunctive", "disjunctive" => "Disjunctive"), $current_theme->facet_types);

                        ?>
                        <?php foreach (get_post_types() as $type) : ?>
                            <?php if (is_array($algolia_registry->indexable_types) && in_array($type, array_keys($algolia_registry->indexable_types))) : ?>
                                <?php
                                $metas = get_meta_key_list($type);

                                if (isset($external_attrs[$type.'_attrs']))
                                    $metas = array_merge(get_meta_key_list($type), $external_attrs[$type.'_attrs']);
                                ?>
                                <?php foreach ($metas as $meta_key) : ?>
                                    <?php
                                    $order = -1;
                                    if (isset($algolia_registry->metas[$type]) && in_array($meta_key, array_keys($algolia_registry->metas[$type])))
                                        $order = $algolia_registry->metas[$type][$meta_key]['order'];
                                    ?>
                                    <?php if ($order != -1): ?>
                                        <tr data-type="extra-meta" data-order="<?php echo $order; ?>">
                                    <?php else: ?>
                                        <tr data-type="extra-meta" data-order="<?php echo (10000 + $i); $i++ ?>">
                                    <?php endif; ?>
                                    <td class="table-col-enabled">
                                        <input type="checkbox"
                                               name="TYPES[<?php echo $type; ?>][METAS][<?php echo $meta_key; ?>][INDEXABLE]"
                                               value="<?php echo $type; ?>"
                                            <?php checked(isset($algolia_registry->metas[$type])
                                                && in_array($meta_key, array_keys($algolia_registry->metas[$type]))
                                                && $algolia_registry->metas[$type][$meta_key]["indexable"]); ?>
                                            >
                                    </td>
                                    <td><?php echo $type; ?></td>
                                    <td><?php echo $meta_key; ?></td>
                                    <td></td>
                                    <td>
                                        <?php if (in_array('instant', $algolia_registry->type_of_search)): ?>
                                            <input type="checkbox"
                                                   name="TYPES[<?php echo $type; ?>][METAS][<?php echo $meta_key; ?>][FACETABLE]"
                                                   value="1"
                                                <?php checked(isset($algolia_registry->metas[$type])
                                                    && isset($algolia_registry->metas[$type][$meta_key])
                                                    && $algolia_registry->metas[$type][$meta_key]["facetable"]
                                                ); ?>
                                                >
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <select name="TYPES[<?php echo $type; ?>][METAS][<?php echo $meta_key; ?>][TYPE]">
                                            <?php foreach ($facet_types as $key => $value): ?>
                                                <?php if (checked(isset($algolia_registry->metas[$type])
                                                    && in_array($meta_key, array_keys($algolia_registry->metas[$type]))
                                                    && $algolia_registry->metas[$type][$meta_key]["type"] == $key)) : ?>
                                                    <option selected="selected" value="<?php echo $key ?>"><?php echo $value; ?></option>
                                                <?php else : ?>
                                                    <option value="<?php echo $key ?>"><?php echo $value; ?></option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        <input type="text"
                                               value="<?php echo (isset($algolia_registry->metas[$type][$meta_key]) ? $algolia_registry->metas[$type][$meta_key]["name"] : "") ?>" name="TYPES[<?php echo $type; ?>][METAS][<?php echo $meta_key; ?>][NAME]">
                                        <img width="10" src="<?php echo plugin_dir_url(__FILE__); ?>../../imgs/move.png">
                                    </td>
                                    <!-- PREVENT FROM ERASING CUSTOM RANKING -->
                                    <?php $customs = array('custom_ranking' => 'CUSTOM_RANKING', 'custom_ranking_order' => 'CUSTOM_RANKING_ORDER', 'custom_ranking_sort' => 'CUSTOM_RANKING_SORT'); ?>
                                    <?php foreach($customs as $custom_key => $custom_value): ?>
                                        <?php if (isset($algolia_registry->metas[$type])
                                            && in_array($meta_key, array_keys($algolia_registry->metas[$type]))
                                            && $algolia_registry->metas[$type][$meta_key][$custom_key]): ?>
                                            <input type="hidden"
                                                   name="TYPES[<?php echo $type; ?>][METAS][<?php echo $meta_key; ?>][<?php echo $custom_value; ?>]"
                                                   value="<?php echo $algolia_registry->metas[$type][$meta_key][$custom_key]; ?>"
                                                >
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <!-- /////// PREVENT FROM ERASING CUSTOM RANKING -->
                                    <input type="hidden" name="TYPES[<?php echo $type; ?>][METAS][<?php echo $meta_key; ?>][ORDER]" class="order" />
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>

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

                        <?php $i = 0; ?>
                        <?php foreach (array_merge($algolia_registry->extras, get_taxonomies()) as $tax) : ?>
                            <?php $count = wp_count_terms($tax, array('hide_empty' => false)); ?>
                            <?php if (in_array($tax, $algolia_registry->extras) || (is_numeric($count) && intval($count) > 0)): ?>

                                <?php
                                $order = -1;
                                if (isset($algolia_registry->metas['tax']) && isset($algolia_registry->metas['tax'][$tax]))
                                    $order = $algolia_registry->metas['tax'][$tax]['order'];
                                ?>
                                <?php if ($order != -1): ?>
                                    <tr data-type="taxonomy" data-order="<?php echo $order; ?>">
                                <?php else: ?>
                                    <tr data-type="taxonomy" data-order="<?php echo (10000 + $i); $i++; ?>">
                                <?php endif; ?>
                                <td class="table-col-enabled">
                                    <?php if (in_array($tax, $algolia_registry->extras) == false): ?>
                                        <input type="checkbox"
                                               name="TAX[<?php echo $tax; ?>][SLUG]"
                                               value="<?php echo $tax; ?>"
                                            <?php checked(isset($algolia_registry->metas['tax']) && in_array($tax, array_keys($algolia_registry->metas['tax']))); ?>
                                            >
                                    <?php else: ?>
                                        <i class="dashicons dashicons-yes"></i>
                                        <input type="hidden" name="TAX[<?php echo $tax; ?>][SLUG]" value="<?php echo $tax; ?>">
                                    <?php endif; ?>
                                </td>
                                <td>*</td>
                                <td>
                                    <?php echo $tax; ?>
                                </td>
                                <td>
                                    <?php if (in_array('autocomplete', $algolia_registry->type_of_search)): ?>
                                        <?php if (isset($algolia_registry->metas['tax']) == false || isset($algolia_registry->metas['tax'][$tax]) == false || $algolia_registry->metas['tax'][$tax]['default_attribute'] == 0): ?>
                                            <input type="checkbox"
                                                   value="facetable"
                                                <?php checked(isset($algolia_registry->metas['tax']) && isset($algolia_registry->metas['tax'][$tax])
                                                    && $algolia_registry->metas['tax'][$tax]['autocompletable'])
                                                ?>
                                                   name="TAX[<?php echo $tax; ?>][AUTOCOMPLETABLE]">
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (in_array('instant', $algolia_registry->type_of_search)): ?>
                                        <input type="checkbox"
                                               value="facetable"
                                            <?php checked(isset($algolia_registry->metas['tax']) && isset($algolia_registry->metas['tax'][$tax])
                                                && $algolia_registry->metas['tax'][$tax]['facetable'])
                                            ?>
                                               name="TAX[<?php echo $tax; ?>][FACETABLE]">
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <select name="TAX[<?php echo $tax; ?>][FACET_TYPE]">
                                        <?php foreach ($facet_types as $key => $value): ?>
                                            <?php if (checked(isset($algolia_registry->metas['tax']) && isset($algolia_registry->metas['tax'][$tax])
                                                && $algolia_registry->metas['tax'][$tax]["type"] == $key)) : ?>
                                                <option selected="selected" value="<?php echo $key ?>"><?php echo $value; ?></option>
                                            <?php else : ?>
                                                <option value="<?php echo $key ?>"><?php echo $value; ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td style="white-space: nowrap;">
                                    <input type="text" value="<?php echo (isset($algolia_registry->metas['tax']) && isset($algolia_registry->metas['tax'][$tax]) ? $algolia_registry->metas['tax'][$tax]['name'] : "") ?>" name="TAX[<?php echo $tax; ?>][NAME]">
                                    <img width="10" src="<?php echo plugin_dir_url(__FILE__); ?>../../imgs/move.png">
                                </td>
                                <input type="hidden" name="TAX[<?php echo $tax; ?>][ORDER]" class="order" />
                                </tr>
                            <?php endif; ?>
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