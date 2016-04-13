$(document).ready(function() {

    $("select[name='year']").css("visibility", "hidden");
    $("select[name='semester']").css("visibility", "hidden");

    $("select[name='name']").on("change", function() {
        $("p.error").html('');
        if ($(this).val() != "no") {
            var id = $(this).val();
            $.post('/ajax/load-year-options-by-id/', {student_id: id}, function(response) {
                if (response.success) {
                    var options = '<option value="all" selected>All Years</option>' + response.options;
                    $("select[name='year']").html(options);
                    $("select[name='year']").css("visibility", "visible");
                    $("select[name='semester']").css("visibility", "hidden");
                    loadPayments(id, 'all', 'all');
                }
            }, "json");
        } else {
            $("select[name='year']").css("visibility", "hidden");
            $("select[name='semester']").css("visibility", "hidden");
            $("#tableData").html('');
            $("p.error").html('Please select student...');
        }
    });

    $("select[name='year']").on("change", function() {
        if ($("select[name='year']").val() == 'all') {
            $("select[name='semester']").css("visibility", "hidden")
            loadPayments($("select[name='name']").val(), 'all', 'all');
        } else {
            $("select[name='semester']").css("visibility", "visible");
            loadPayments($("select[name='name']").val(), $(this).val(), $("select[name='semester']").val());
        }
    });

    $("select[name='semester']").on("change", function() {
        loadPayments($("select[name='name']").val(), $("select[name='year']").val(), $(this).val());
    });
});

loadPayments = function(id, year, semester) {
    $("#tableData").html('<img src="/images/icons/loading.gif" />');
    $("p.error").html('');
    var data = {
        student_id: id,
        year: year,
        semester: semester
    };

    $.post('/ajax/load-payment-by-id/', data, function(response) {
        if (response.success) {
            $("#tableData").html(response.tableData);
            bindActions();
        } else {
            $("#tableData").html('');
            $("p.error").html(response.errorMessage);
        }
    }, "json");
};

bindActions = function() {
    bindOverlay();
    $(".edit").unbind('click');
    $(".delete").unbind('click');
    $(".save").unbind('click');
    $(".cancel").unbind('click');

    $(".edit").click(function() {
        var id_str = $(this).attr("id").split('_');
        var id = id_str[0];

        $("#" + id).hide();
        $("#" + id + "_edit").show();
    });

    $(".delete").click(function() {
        var id_str = $(this).attr("id").split('_');
        var data = {
            id: id_str[0],
            current_year: $('select[name="year"]').val(),
            current_semester: $('select[name="semester"]').val(),
            student_id: $('select[name="name"]').val()
        };

        if (confirm("Are you sure to remove the selected payment record?")) {
            $.post('/ajax/remove-payment-record/', data, function(response) {
                if (response.success) {
                    $("#tableData").html(response.tableData);
                    bindActions();
                } else {
                    $("p.error").html(response.errorMessage);
                }
            }, "json");
        }
    });

    $(".save").click(function() {
        var id_str = $(this).attr("id").split('_');
        var id = id_str[0];
        var data = {
            current_year: $('select[name="year"]').val(),
            current_semester: $('select[name="semester"]').val(),
            id: id,
            student_id: $('select[name="name"]').val(),
            date: $("#" + id + "_date").val(),
            type: $("#" + id + "_type").val(),
            amount: $("#" + id + "_amount").val(),
            reason_code: $("#" + id + "_reason").val()
        }

        $.post('/ajax/update-payment-record/', data, function(response) {
            if (response.success) {
                $("#tableData").html(response.tableData);
                bindActions();
            } else {
                $("p.error").html(response.errorMessage);
            }
        }, "json");
    });

    $(".cancel").click(function() {
        id_str = $(this).attr("id").split('_');
        var id = id_str[0];

        $("#" + id).show();
        $("#" + id + "_edit").hide();
    });

    $("#payment-new").click(function() {
        $("p.overlay").html('');
        var data = {
            student_id: $('select[name="name"]').val(),
            current_year: $('select[name="year"]').val(),
            current_semester: $('select[name="semester"]').val(),
            date: $('input[name="new_date"]').val(),
            type: $('select[name="new_type"]').val(),
            amount: $('input[name="new_amount"]').val(),
            reason_code: $('select[name="new_reason"]').val()
        }

        var errorMessage = '';

        if (!validateDate(data.date)) {
            errorMessage += 'Please input a valid date format (2016-02-20)<br />';
        }
        if (data.type == '0') {
            errorMessage += 'Please select a payment type<br />';
        }
        if (data.reason_code == '0') {
            errorMessage += 'Please select a payment reason<br />';
        }
        if (isNaN(data.amount) || data.amount <= 0) {
            errorMessage += 'Please input a valid amount (greater than 0)<br />';
        }

        if (errorMessage == '') {
            $.post('/ajax/add-payment-record/', data, function (response) {
                if (response.success) {
                    $("#tableData").html(response.tableData);
                    $("#close").click();
                    bindActions();
                } else {
                    $("p.overlay").html(response.errorMessage);
                }
            }, "json");
        } else {
            $("p.overlay").html(errorMessage);
        }
    });
};

validateDate = function(str) {
    // STRING FORMAT yyyy-mm-dd
    if (str == "" || str == null) {
        return false;
    }

    // m[1] is year 'YYYY' * m[2] is month 'MM' * m[3] is day 'DD'
    var m = str.match(/(\d{4})-(\d{2})-(\d{2})/);

    // STR IS NOT FIT m IS NOT OBJECT
    if (m === null || typeof m !== 'object') {
        return false;
    }

    // CHECK m TYPE
    if (typeof m !== 'object' && m !== null && m.size !== 3) {
        return false;
    }

    var ret = true; //RETURN VALUE
    var thisYear = new Date().getFullYear(); //YEAR NOW
    var minYear = 1995; //MIN YEAR

    // YEAR CHECK
    if ((m[1].length < 4) || m[1] < minYear || m[1] > thisYear) {
        ret = false;
    }
    // MONTH CHECK
    if ((m[2].length < 2) || m[2] < 1 || m[2] > 12) {
        ret = false;
    }
    // DAY CHECK
    if ((m[3].length < 2) || m[3] < 1 || m[3] > 31) {
        ret = false;
    }

    return ret;
}