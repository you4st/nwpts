$(document).ready(function() {

    loadCourses();

	$("#new-course").click(function () {
		var numRow = 0;
		var relations = '';

		$("#course-table").find('tr').each(function () {
			numRow++;
		});

		var row = $('<tr id="' + (numRow - 1) + '"></tr>');

		row.html('<td><div class="duty-old hide"></div><div class="duty-new"><input type="text" name="duty_name" /><span class="button-light change">변경</span></div></td>'
			+ '<td align="center"><a class="down">move down</a>&nbsp;&nbsp;/&nbsp;&nbsp;<a class="up">move up</a></td>'
			+ '<td align="center"><input type="checkbox" name="remove" value="1"></td>');

		row.insertBefore($(this).closest('tr'));

		bindActions();
	});
});
	
loadCourses = function() {
    $("#tableData").html('<img src="/images/icons/loading.gif" />');

    $.post('/ajax/load-table/', {tableName: 'course'}, function(response) {
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
    $(".edit").click(function() {
        id_str = $(this).attr("id").split('_');
        var id = id_str[0];

        $("#" + id).hide();
        $("#" + id + "_edit").show();
	});

    $(".delete").click(function() {
        id_str = $(this).attr("id").split('_');
        var id = id_str[0];

        if (confirm("Are you sure to remove the selected course?")) {
            $.post('/ajax/remove-course/', {id: id}, function(response) {
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
        id_str = $(this).attr("id").split('_');
        var id = id_str[0];
        var data = {
            id: id,
            name: $("#" + id + "_name").val(),
            course_id: $("#" + id + "_course_id").val(),
            description: $("#" + id + "_description").val(),
            credit: $("#" + id + "_credit").val()
        }

        $.post('/ajax/update-course/', data, function(response) {
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

    $("#new-course").click(function() {
        $("#new_line").hide();
        $("#new_course_form").show(500);
    });

    $("#new_add").click(function() {
        var data = {
            name: $("#new_name").val(),
            course_id: $("#new_course_id").val(),
            description: $("#new_description").val(),
            credit: $("#new_credit").val()
        }

        $.post('/ajax/add-course/', data, function(response) {
            if (response.success) {
                $("#tableData").html(response.tableData);
                bindActions();
            } else {
                $("p.error").html(response.errorMessage);
            }
        }, "json");
    });

    $("#new_cancel").click(function() {
        $("#new_course_form").hide();
        $("#new_line").show(500);
    });
}

