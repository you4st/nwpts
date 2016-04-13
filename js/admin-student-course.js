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
                    loadCourses(id, 'all', 'all');
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
            loadCourses($("select[name='name']").val(), 'all', 'all');
        } else {
            $("select[name='semester']").css("visibility", "visible");
            loadCourses($("select[name='name']").val(), $(this).val(), $("select[name='semester']").val());
        }
    });

    $("select[name='semester']").on("change", function() {
        loadCourses($("select[name='name']").val(), $("select[name='year']").val(), $(this).val());
    });
});
	
loadCourses = function(id, year, semester) {
    $("#tableData").html('<img src="/images/icons/loading.gif" />');
    $("p.error").html('');
    var data = {
        student_id: id,
        year: year,
        semester: semester
    };

    $.post('/ajax/load-course-by-id/', data, function(response) {
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

        if (confirm("Are you sure to remove the selected course?")) {
            $.post('/ajax/remove-student-course/', data, function(response) {
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
            course_id: $("#" + id + "_course_id").val(),
            year: $("#" + id + "_year").val(),
            semester: $("#" + id + "_semester").val(),
            grade: $("#" + id + "_grade").val()
        }

        $.post('/ajax/update-student-course/', data, function(response) {
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

    $("#student-course-new").click(function() {
        $("p.overlay").html('');
        var data = {
            student_id: $('select[name="name"]').val(),
            current_year: $('select[name="year"]').val(),
            current_semester: $('select[name="semester"]').val(),
            course_id: $('select[name="new_course_id"]').val(),
            year: $('select[name="new_year"]').val(),
            semester: $('select[name="new_semester"]').val(),
            grade: $('input[name="new_grade"]').val()
        }

        if (data.course_id == '0') {
            $("p.overlay").html('Please select a course...');
        } else {
            $.post('/ajax/add-student-course/', data, function (response) {
                if (response.success) {
                    $("#tableData").html(response.tableData);
                    $("#close").click();
                    bindActions();
                } else {
                    $("p.overlay").html(response.errorMessage);
                }
            }, "json");
        }
    });
};