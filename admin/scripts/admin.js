jQuery(document).ready(function($) {
    if ($("input[name='TYPE_OF_SEARCH']:checked").val() == 'instant')
        $("#jquery_selector_wrapper").show();

    $(".instant_radio").change(function (e) {
        if ($("input[name='TYPE_OF_SEARCH']:checked").val() == 'instant')
            $("#jquery_selector_wrapper").show();
        else
            $("#jquery_selector_wrapper").hide();
    });
});