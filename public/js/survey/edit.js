$().ready(function() {
    
    var surveyId = document.getElementById("surveyId").textContent ;
    
    $("#editsurvey").validate({
        rules: {
            title: "required",
            prompt: "required"
        },
        messages: {
            title: "Survey name is required!",
            prompt: "You need a prompt for this survey!"
        }
    });
    
    $( "#confirm-dialog" ).dialog({
      autoOpen: false,
      height: 300,
      width: 350,
      modal: true,
      buttons: {
        "Delete Survey": function() {
                $.ajax({
                dataType: 'json',
                type: 'POST',
                url: '/survey/delete',
                data: {id :surveyId }, 
                success:function(data){  
                    window.location.replace("/project/view/"+data.id);               
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
    
     $("a#deleteSurvey").unbind('click');
    $( "a#deleteSurvey" )
      .click(function() {
        $( "#confirm-dialog" ).dialog( "open" );
      });
});