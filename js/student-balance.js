$(document).ready(function() {
    var student_id = $('input[name="student_id"]').val();
    var currentYear = $('input[name="currentYear"]').val();
    var currentSemester = $('input[name="currentSemester"]').val();
    var year = $('select[name="year"]').val();
    var semester = $('select[name="semester"]').val();

    loadBalances(student_id, year, semester);

    $("select").on("change", function() {
        year = $('select[name="year"]').val();
        semester = $('select[name="semester"]').val();
        loadBalances(student_id, $("select[name='year']").val(), $("select[name='semester']").val());
    });
});

loadBalances = function(id, year, semester) {
    $("#tableData").html('<img src="/images/icons/loading.gif" />');
    $("p.error").html('');
    var data = {
        student_id: id,
        year: year,
        semester: semester,
        admin: 0
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
}
