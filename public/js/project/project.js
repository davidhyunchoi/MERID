$().ready(function() {
$('#time').datetimepicker()
	.datetimepicker({step:10});

$("#time_newfeed").click(function(){

    var time = $('#time').val(); 
    var date = time.split(" ")[0];
    date = date.replace(/\//g,'-');
    time = time.split(" ")[1];
    time = time +":00";
    var datetime = date+" "+time;
    $.ajax({
               dataType: "json",
                type: "POST",
                url: "/project/getNewsBasedOnTime",
                data: { datetime:datetime}, 
                cache: false,
                success: function(data){
                    //alert(datetime);
                    $('#logoutTime').html("<p>Newsfeed from the selected time : <b>"+datetime+"</b></p>");
                   $('#dynamicNews').html(" ");
            var text ="";         
           var surveysWithNewPComments = data.surveysWithNewPComments;
            keysbyindex = Object.keys(surveysWithNewPComments);
           if(keysbyindex.length>0){
            text = text +"<br/><h4> Surveys with new participant comments</h4>";
            for (var key in surveysWithNewPComments) {
                text = text +"<b> "+ surveysWithNewPComments[key] +"<a style=\"margin-left:20px\" href=\"survey/view/"+key+ "\">View Survey</a>";
                text =text + "<br/>";
            }
           }
           
          var surveysWithNewRComments = data.surveysWithNewRComments; 
          keysbyindex = Object.keys(surveysWithNewRComments);
           if(keysbyindex.length>0){
               text =text +"<br/><h4> Surveys with new researcher comments</h4>";
                                      
               for (var key in surveysWithNewRComments) {
                 text = text +"<b> "+ surveysWithNewRComments[key] +"<a style=\"margin-left:20px\" href=\"survey/view/"+key+ "\">View Survey</a>";
                  text =text + "<br/>";
               }
           }
           

            $('#dynamicNews').append(data.text);
            $('#dynamicNews').append(text);
               },
                failure:function(){alert("Failure!!");
                }
            });         
         $("#U_comment").val('');

    return false;
});
});