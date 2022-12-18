$(".toggle-password").on('click', function() {

    var eye = $("#eye");
    if(eye.attr("class") === "fe fe-eye"){
        eye.attr("class", "fe fe-eye-off");
    }else{
        eye.attr("class", "fe fe-eye");
    }

    var input = $("#password");
    if (input.attr("type") === "password") {
        input.attr("type", "text");
    } else {
        input.attr("type", "password");
    }
});
