$(document).ready(function() {
    $("span.edit").click(function() {
        $(".old").hide();
        $(".new").show();
    });

    $("span.cancel").click(function() {
        $(".old").show();
        $(".new").hide();
    });

    $("span.update").click(function() {
        $("form[name='profile']").submit();
    });
});