$(document).ready(function() {
    loadUsers();
});
	
loadUsers = function() {
    $("#tableData").html('<img src="/images/icons/loading.gif" />');

    $.post('/ajax/load-table/', {tableName: 'user'}, function(response) {
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
    $(".submit").click(function() {
        $("p.overlay").html('');
        var data = {
            username: $("input[name='username']").val(),
            password: $("input[name='password']").val(),
            last_name: $("input[name='last_name']").val(),
            first_name: $("input[name='first_name']").val(),
            email: $("input[name='email']").val(),
            user_type: $("select[name='user_type']").val()
        }

        var required = ['username', 'password', 'last_name', 'first_name', 'email', 'user_type'];
        var error = '';

        for (var i=0; i<required.length; i++) {
            if (String.trim(data[required[i]]) == '') {
                error = error + '\'' + required[i] + '\' should not be empty<br />';
            }
        }

        if (error != '') {
            $("p.overlay").html(error);
        } else {
            $.post('/ajax/add-user/', data, function (response) {
                if (response.success) {
                    window.location.href = '/admin/user';
                } else {
                    $("p.overlay").html(response.errorMessage);
                }
            }, "json");
        }
    });

    $("#user-update").click(function() {
        $("p.overlay").html('');
        var data = {
            id: $(this).attr('data'),
            username: $("input[name='username']").val(),
            last_name: $("input[name='last_name']").val(),
            first_name: $("input[name='first_name']").val(),
            email: $("input[name='email']").val(),
            user_type: $("select[name='user_type']").val()
        }

        var required = ['username', 'last_name', 'first_name', 'email', 'user_type'];
        var error = '';

        for (var i=0; i<required.length; i++) {
            if (String.trim(data[required[i]]) == '') {
                error = error + '\'' + required[i] + '\' should not be empty<br />';
            }
        }

        if (error != '') {
            $("p.overlay").html(error);
        } else {
            $.post('/ajax/update-user/', data, function (response) {
                if (response.success) {
                    window.location.href = '/admin/user';
                } else {
                    $("p.overlay").html(response.errorMessage);
                }
            }, "json");
        }
    });

    $(".remove").click(function() {
        $("p.overlay").html('');

        if (confirm("Are you sure to remove the selected user?")) {
            $.post('/ajax/remove-user/', {id: $(this).attr('data')}, function (response) {
                if (response.success) {
                    window.location.href = '/admin/user';
                } else {
                    $("p.overlay").html(response.errorMessage);
                }
            }, "json");
        }
    });
}

