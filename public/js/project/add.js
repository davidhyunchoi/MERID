$().ready(function() {
    $("#createproject").validate({
        rules: {
            name: "required",
            orchestraName: "required",
            institution: "required",
            description: "required",
            rationale: "required",
            rToRComments: "required",
            pToPComments: "required"
        },
        messages: {
            name: "Please fill in the project name!",
            orchestraName: "Please fill in the orchestra name!",
            institution: "Institution is required!",
            description: "Please fill in project description!",
            rationale: "Please fill in project rationale!",
            rToRComments: "You must check one of the following settings!",
            pToPComments: "You must check one of the following settings!"
        }
    });
});