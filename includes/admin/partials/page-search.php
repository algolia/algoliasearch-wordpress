<div class="wrap">
  <h1>
    <?php echo esc_html(get_admin_page_title()); ?>
    <button class="algolia-reindex-button button button-primary" data-index="searchable_posts">Re-index search page records.</button>
  </h1>
  <form method="post" action="options.php">
      <?php
      settings_fields($this->option_group);
      do_settings_sections($this->slug);
      submit_button();
      ?>
  </form>
</div>

