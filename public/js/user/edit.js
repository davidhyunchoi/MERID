$().ready(function() {
                $("#edit").validate({
		rules: {
			name: "required",
			username: {
				required: true
			},
			password: {
				minlength: 6
			},
			confpass: {
				minlength: 6,
				equalTo: "#password"
			},
			email: {
				required: true,
				email: true
			}
		},
		messages: {
			name: "Please enter your full name!",
			username: {
				required: "Please enter a username!"
			},
			password: {
				minlength: "Your password must be at least 6 characters long!"
			},
			confpass: {
				minlength: "Your password must be at least 6 characters long!",
				equalTo: "Your password is not the same as above!"
			},
			email: {
				email: "Please enter a valid email address!"
			}
                    }
	});
        });