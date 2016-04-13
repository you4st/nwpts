$(document).ready(function() {
    loadStudents();
});
	
loadStudents = function() {
    $("#tableData").html('<img src="/images/icons/loading.gif" />');

    $.post('/ajax/load-table/', {tableName: 'student'}, function(response) {
        if (response.success) {
            $("#tableData").html(response.tableData);
            bindOverlay();
            bindActions();
        } else {
            $("#tableData").html('');
            $("p.error").html(response.errorMessage);
        }
    }, "json");
};

bindActions = function() {
    $(".update").on("change paste keyup", function() {
        $("p.overlay").html('');
        var birthDate = $("input[name='birth_date']").val();
        var startYear = $("input[name='start_year']").val();
        var pattern = /^[0-9]+$/;

        if (birthDate.length == 10 && startYear.length == 4) {
            birthDate = birthDate.replace(/[^0-9.]/g, "");
            if (birthDate.length == 8 && pattern.test(birthDate) && pattern.test(startYear)) {
                var studentId = startYear.substr(2) + birthDate.substr(2);
                $("input[name='student_id']").val(studentId);
            } else {
                $("p.overlay").html("Please enter a valid birth date and admission year...");
            }
        }
    });

    $(".submit").click(function() {
        $("p.overlay").html('');
        var data = {
            last_name: $("input[name='last_name']").val(),
            first_name: $("input[name='first_name']").val(),
            email: $("input[name='email']").val(),
            phone: $("input[name='phone']").val(),
            street: $("input[name='street']").val(),
            city: $("input[name='city']").val(),
            state: $("select[name='state']").val(),
            zip: $("input[name='zip']").val(),
            birth_date: $("input[name='birth_date']").val(),
            student_id: $("input[name='student_id']").val(),
            start_year: $("input[name='start_year']").val(),
            start_semester: $("select[name='start_semester']").val(),
            grad_year: $("input[name='grad_year']").val(),
            major: $("select[name='major']").val()
        }

        $.post('/ajax/add-student/', data, function(response) {
            if (response.success) {
                window.location.href = '/admin/student';
            } else {
                $("p.overlay").html(response.errorMessage);
            }
        }, "json");
    });

    $("#student-update").click(function() {
        $("p.overlay").html('');
        var data = {
            id: $(this).attr('data'),
            last_name: $("input[name='last_name']").val(),
            first_name: $("input[name='first_name']").val(),
            email: $("input[name='email']").val(),
            phone: $("input[name='phone']").val(),
            street: $("input[name='street']").val(),
            city: $("input[name='city']").val(),
            state: $("select[name='state']").val(),
            zip: $("input[name='zip']").val(),
            birth_date: $("input[name='birth_date']").val(),
            student_id: $("input[name='student_id']").val(),
            start_year: $("input[name='start_year']").val(),
            start_semester: $("select[name='start_semester']").val(),
            grad_year: $("input[name='grad_year']").val(),
            major: $("select[name='major']").val()
        }

        $.post('/ajax/update-student/', data, function(response) {
            if (response.success) {
                window.location.href = '/admin/student';
            } else {
                $("p.overlay").html(response.errorMessage);
            }
        }, "json");
    });

    $(".remove").click(function() {
        $("p.overlay").html('');

        if (confirm("Are you sure to remove the selected student?")) {
            $.post('/ajax/remove-student/', {id: $(this).attr('data')}, function (response) {
                if (response.success) {
                    window.location.href = '/admin/student';
                } else {
                    $("p.overlay").html(response.errorMessage);
                }
            }, "json");
        }
    });
}

