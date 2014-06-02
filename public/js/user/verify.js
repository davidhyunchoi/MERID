$().ready(function() {
    $("#verificationForm").validate({
        rules: {
     
            password: {
                required: true,
                minlength: 6
            },
            confpass: {
                required: true,
                minlength: 6,
                equalTo: "#password"
            },
            code: {
                required: true
            }
        },
        messages: {

            password: {
                required: "Please provide a password!",
                minlength: "Your password must be at least 6 characters long!"
            },
            confpass: {
                required: "Please confirm your password!",
                minlength: "Your password must be at least 6 characters long!",
                equalTo: "Your password is not the same as above!"
            },
            code: {
                required: "Please enter your verification code!"
            }
        }
    });
});