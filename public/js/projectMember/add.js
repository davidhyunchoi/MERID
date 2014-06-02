$().ready(function() {
    $("#signup_form").validate({
        rules: {
            userName: {
                required: true
            },
            email: {
                required: true,
                email: true
            }
        },
        messages: {
            userName: {
                required: "Please enter a username!"
            },
            email: {
                email: "Please enter a valid email address!"
            }
        }
    });

    var emailIds;
        $.ajax({
           dataType: 'json',
           type: 'POST',
           url: '/user/getUserEmailIds',
           success: function(data) {
             emailIds =data.emailIds;
               //alert (emailIds);
             $( "#email" ).autocomplete({
                source: emailIds
             });
           },
           error: function() {
                    // failed request; give feedback to user
                    // alert('Unsuccessful return!');
           }
       });    
 });
            
$(document).ready(function() {   
    var projectId = document.getElementById("projectId").textContent ;
    
    $("#email").change(function(event)
    {
        var email_regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/i;
        $email = $('#email').val();
        if ((email_regex.test($email)))
        {
            $.ajax({
                dataType: 'json',
                type: 'POST',
                url: '/projectMember/validateEmail',
                data: {
                    email: $email,
                    projectId: projectId
               },
                success: function(data) {
                    // successful request; do something with the data
                    // alert('Successful return! '+ data);
                    if (data.message === "success")
                    {
                        $('#error').html('');
                    }
                    else
                    {
                        $('#error').html(data.message);
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