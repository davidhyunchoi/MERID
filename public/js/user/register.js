$().ready(function() {
    $("#signUp").validate({
        rules: {
            name: "required",
            username: {
                required: true
            },
            email: {
                required: true,
                email: true
            },
            tos: "required"
        },
        messages: {
            name: "Please enter your full name!",
            username: {
                required: "Please enter a username!"
            },
            email: {
                email: "Please enter a valid email address!"
            },
            tos: "Please agree to our terms of use!"
        }
    });
});


$(document).ready(function() {
    $("#email").change(function(event)
    {
        var email_regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/i;
        $email = $('#email').val();
        if ((email_regex.test($email)))
        {
            $.ajax({
                dataType: 'json',
                type: 'POST',
                url: '/user/formValidate',
                data: {
                    email: $email
               },
                success: function(data) {
                    // successful request; do something with the data
                    // alert('Successful return! '+ data);
                    if (jQuery.parseJSON(JSON.stringify(data)).usable === 1)
                    {
                        $('#error').html('');
                    }
                    else
                    {
                        $('#error').html('This email already exists! Please select another.');
                    }
                },
                error: function() {
                    // failed request; give feedback to user
                    // alert('Unsuccessful return!');
                }
            });
        }
        else
        {
            $('#error').html('');
        }
    });
});