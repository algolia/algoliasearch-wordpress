<?php
    $langDomain         = "algolia";
    $algolia_registry   = \Algolia\Core\Registry::getInstance();
    $template_helper    = new Algolia\Core\TemplateHelper();
    $current_template   = $template_helper->get_current_template();

    $move_icon_url      = plugin_dir_url(__FILE__) . '../imgs/move.png';

    $need_to_reindex    = $algolia_registry->need_to_reindex;

    global $attributesToIndex;

?>

<?php

if (function_exists('curl_version') == false)
{
?>
    <div>
        <h1>Algolia Search : Errors</h1>
        <ul>
            <li>You need to have <b>curl</b> and <b>php5-curl</b> installed</li>
        </ul>
    </div>
<?php
    return;
}

?>

<div id="algolia-settings" class="wrap">

    <a target="_blank" href="//algolia.com/dashboard" class="header-button" id="dashboard-link">Go to Algolia dashboard</a>

    <?php if ($algolia_registry->validCredential) : ?>
    <h2>
        Algolia Search
        <button type="button" class="button <?php echo (! $need_to_reindex ? "button-secondary" : "button-primary"); ?> " id="algolia_reindex" name="algolia_reindex">
            <i class="dashicons dashicons-upload"></i>
            <?php echo (! $need_to_reindex ? "Reindex data" : "Reindexing Needed"); ?>
            <span class="record-count"></span>
        </button>
        <em id='last-update' style="color: #444;font-family: 'Open Sans',sans-serif;font-size: 13px;line-height: 1.4em;">
            Last update:
            <?php if ($algolia_registry->last_update): ?>
                <?php echo date('Y-m-d H:i:s', $algolia_registry->last_update); ?>
            <?php else: ?>
                <span style="color: red">Never: please re-index your data.</span>
            <?php endif; ?>
        </em>
    </h2>

    <div class="wrapper">
        <?php if ($algolia_registry->validCredential) : ?>
        <div style="clear: both;"</div>
        <?php endif; ?>

        <div id="results-wrapper" style="display: none;">
            <div class="content">
                <div class="show-hide">

                    <div class="content-item">
                        <div>Progression</div>
                        <div style='padding: 5px;'>
                            <div id="reindex-percentage">
                            </div>
                            <div style='clear: both'></div>
                        </div>
                    </div>

                    <div class="content-item">
                        <div>Logs</div>
                        <div style='padding: 5px;'>
                            <table id="reindex-log"></table>
                        </div>
                    </div>

                    <div class="content-item">
                        <button style="display: none;" type="submit" name="submit" id="submit" class="close-results button button-primary">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <h2>
        Algolia Realtime Search
    </h2>
    <?php endif; ?>

    <div class="wrapper">
        <div class="tabs myclearfix">

            <?php if (! $algolia_registry->validCredential) : ?>
            <div data-tab="#credentials" class="title selected">Credentials</div>
            <?php else: ?>
            <div data-tab="#credentials" class="title">Credentials</div>
            <?php endif; ?>

            <?php if ($algolia_registry->validCredential) : ?>

            <div data-tab="#configuration"          class="title selected">UI</div>
            <div data-tab="#indexable-types"        class="title">Types</div>
            <div data-tab="#extra-metas"            class="title">Attributes</div>
            <div data-tab="#searchable_attributes"  class="title">Search</div>
            <div data-tab="#custom-ranking"         class="title">Ranking</div>
            <div data-tab="#sortable_attributes"    class="title">Sorting</div>
            <div data-tab="#advanced"               class="title">Advanced</div>

            <?php endif; ?>
            <div style="clear:both"></div>
        </div>

        <?php include __DIR__ . '/tabs/credentials.php'; ?>

        <?php if ($algolia_registry->validCredential) : ?>

            <?php include __DIR__ . '/tabs/configuration.php'; ?>
            <?php include __DIR__ . '/tabs/indexable_types.php'; ?>
            <?php include __DIR__ . '/tabs/searchable_attributes.php'; ?>
            <?php include __DIR__ . '/tabs/sortable_attributes.php'; ?>
            <?php include __DIR__ . '/tabs/extra-metas.php'; ?>
            <?php include __DIR__ . '/tabs/custom_ranking.php'; ?>
            <?php include __DIR__ . '/tabs/advanced.php'; ?>

        <?php endif; ?>
    </div>
</div>