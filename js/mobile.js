$(document).ready(function() {
    $(".more").click(function() {
        var id = 'detail_' + $(this).attr('id');

        if ($(this).html() == '더보기') {
            $(this).html('숨기기');
        } else {
            $(this).html('더보기');
        }

        $('#' + id).toggle();
    });

    $(".search").click(function() {
        $(".error").hide();
        var data = {name: $.trim($("#keyword").val()), list: 1};

        if (data.name.length > 0) {
            $.post('/ajax/search-member/', data, function (response) {
                if (response.success) {
                    if (response.searchResult.length > 0) {
                        showRows(response.searchResult);
                        $('.show-all').show();
                    } else {
                        showAll(1);
                    }
                }
            }, "json");
        } else {
            showAll(1);
        }
    });

    $(".show-all").click(function() {
        showAll(0);
    });

    function showAll(showError) {
        if (showError) {
            window.location.href = '/mobile/index/error/1';
        } else {
            window.location.href = '/mobile';
        }
    }

    function hideAll() {
        $("#list").children().find('tr').hide();
    }

    function showRows(list) {
        // show all rows first
        hideAll();

        $("#list").children().find('.header').show();
        $("#list").children().find('tr').each(function() {
            if (typeof $(this).attr('id') != 'undefined') {
                var id_str = $(this).attr('id').split('_');
                var id = id_str[1];

                if ($.inArray(id, list) != -1) {
                    $(this).show();
                    $(this).children().find('.more').html('숨기기');
                }
            }
        });
    }
});