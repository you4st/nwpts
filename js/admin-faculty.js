$(document).ready(function() {
    loadStudents();
});
	
loadStudents = function() {
    $("#tableData").html('<img src="/images/icons/loading.gif" />');

    $.post('/ajax/load-table/', {tableName: 'faculty'}, function(response) {
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
                var facultyId = startYear.substr(2) + birthDate.substr(2);
                $("input[name='faculty_id']").val(facultyId);
            } else {
                $("p.overlay").html("Please enter a valid birth date and start year...");
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
            birth_date: $("input[name='birth_date']").val(),
            faculty_id: $("input[name='faculty_id']").val(),
            start_year: $("input[name='start_year']").val()
        }

        $.post('/ajax/add-faculty/', data, function(response) {
            if (response.success) {
                window.location.href = '/admin/faculty';
            } else {
                $("p.overlay").html(response.errorMessage);
            }
        }, "json");
    });

    $("#faculty-update").click(function() {
        $("p.overlay").html('');
        var data = {
            id: $(this).attr('data'),
            last_name: $("input[name='last_name']").val(),
            first_name: $("input[name='first_name']").val(),
            email: $("input[name='email']").val(),
            phone: $("input[name='phone']").val(),
            birth_date: $("input[name='birth_date']").val(),
            faculty_id: $("input[name='faculty_id']").val(),
            start_year: $("input[name='start_year']").val()
        }

        $.post('/ajax/update-faculty/', data, function(response) {
            if (response.success) {
                window.location.href = '/admin/faculty';
            } else {
                $("p.overlay").html(response.errorMessage);
            }
        }, "json");
    });

    $(".remove").click(function() {
        $("p.overlay").html('');

        if (confirm("Are you sure to remove the selected faculty?")) {
            $.post('/ajax/remove-faculty/', {id: $(this).attr('data')}, function (response) {
                if (response.success) {
                    window.location.href = '/admin/faculty';
                } else {
                    $("p.overlay").html(response.errorMessage);
                }
            }, "json");
        }
    });
}

