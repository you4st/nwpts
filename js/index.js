$(document).ready(function() {

});

function bindActions() {
    // bind a click event for the "submit" button on the password reset overlay
    $(".reset").bind("click", sendPasswordResetRequest);
}

function sendPasswordResetRequest() {
    alert("GGG");
}