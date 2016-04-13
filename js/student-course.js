$(document).ready(function() {
    var student_id = $('input[name="student_id"]').val();
    var currentYear = $('input[name="currentYear"]').val();
    var currentSemester = $('input[name="currentSemester"]').val();
    var year = $('select[name="year"]').val();
    var semester = $('select[name="semester"]').val();
    var allowEnroll = (currentYear == year && currentSemester == semester) ? 1 : 0;

    loadCourses(student_id, year, semester, allowEnroll);

    $("select").on("change", function() {
        year = $('select[name="year"]').val();
        semester = $('select[name="semester"]').val();
        allowEnroll = (currentYear == year && currentSemester == semester) ? 1 : 0;
        loadCourses(student_id, $("select[name='year']").val(), $("select[name='semester']").val(), allowEnroll);
    });
});

loadCourses = function(id, year, semester, allowEnroll) {
    $("#tableData").html('<img src="/images/icons/loading.gif" />');
    $("p.error").html('');
    var data = {
        student_id: id,
        year: year,
        semester: semester,
        allowEdit: '0',
        admin: '0',
        allowEnroll: allowEnroll
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

    $("#student-course-new").click(function() {
        $("p.overlay").html('');
        var data = {
            student_id: $('input[name="student_id"]').val(),
            current_year: $('input[name="currentYear"]').val(),
            current_semester: $('input[name="currentSemester"]').val(),
            course_id: $('select[name="new_course_id"]').val(),
            year: $('input[name="currentYear"]').val(),
            semester: $('input[name="currentSemester"]').val(),
            allowEdit: '0',
            admin: '0',
            allowEnroll: '1'
        };

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
}
