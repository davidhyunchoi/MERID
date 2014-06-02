$().ready(function() {
        var projectId = document.getElementById("projectId").textContent ;
    $("#editproject").validate({
        rules: {
            name: "required",
            orchestraName: "required",
            institution: "required",
            description: "required",
            rationale: "required"
        },
        messages: {
            name: "Please fill in the project name!",
            orchestraName: "Please fill in the orchestra name!",
            institution: "Institution is required!",
            description: "Please fill in project description!",
            rationale: "Please fill in project rationale!"
        }
    });
    
    $( "#confirm-dialog" ).dialog({
      autoOpen: false,
      height: 300,
      width: 350,
      modal: true,
      buttons: {
        "Delete Project": function() {
                $.ajax({
                dataType: 'json',
                type: 'POST',
                url: '/project/delete',
                data: {id :projectId }, 
                success:function(data){  
                    window.location.replace("/project/viewAll");               
                },
                error:function(){
                
                }
        });
        $( this ).dialog( "close" );
        },
        Cancel: function() {
          $( this ).dialog( "close" );
        }
      }
    });
    
     $("a#deleteProject").unbind('click');
    $( "a#deleteProject" )
      .click(function() {
        $( "#confirm-dialog" ).dialog( "open" );
      });
});