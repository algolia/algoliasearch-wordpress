(function($) {

  $(function() {
    var $reindexButtons = $('.algolia-reindex-button');
    $reindexButtons.on('click', handleReindexButtonClick);
  });

  function handleReindexButtonClick(e) {

    $clickedButton = $(e.currentTarget);
    var index = $clickedButton.data('index');
    if (!index) {
      throw new Error('Clicked button has no "data-index" set.');
    }

    $clickedButton.attr('disabled', 'disabled');
    $clickedButton.data('originalText', $clickedButton.text());
    updateIndexingPourcentage($clickedButton, 0);

    reIndex($clickedButton, index);
  }

  function updateIndexingPourcentage($clickedButton, amount) {
    $clickedButton.text('Processing, please be patient ... ' + amount + '%');
  }

  function reIndex($clickedButton, index, currentPage) {
    if(!currentPage) {
      currentPage = 1;
    }

    var data = {
      'action': 'algolia_re_index',
      'index_id': index,
      'page': currentPage
    };

    $.post(ajaxurl, data, function(response) {
      progress = Math.round((currentPage / response.totalPagesCount)*100);
      updateIndexingPourcentage($clickedButton, progress);

      if(response.finished !== true) {
        reIndex($clickedButton, index, ++currentPage);
      } else {
        resetButton($clickedButton);
      }
    }).fail(function(response) {
      alert('An error occurred: ' + response.responseText);
      resetButton($clickedButton);
    });
  }

  function resetButton($clickedButton) {
    $clickedButton.text($clickedButton.data('originalText'));
    $clickedButton.removeAttr('disabled');
    $clickedButton.data('currentPage', 1);
  }

})(jQuery);


