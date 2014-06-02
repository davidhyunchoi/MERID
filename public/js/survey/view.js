$(document).ready(function(){
    
    var currentUser = document.getElementById("user_id").textContent ;
    var primaryResearcher = document.getElementById("res_id").textContent ;
    var surveyId = document.getElementById("surveyId").textContent ;
    
    var masterVideo = document.getElementById("masterVideo").textContent;
    var masterSTime = document.getElementById("masterSTime").textContent;
    var masterETime = document.getElementById("masterETime").textContent;

    $("#selectall").click(function () {
          $('.checkbox1').prop('checked', $(this).prop('checked'));
    });

     $(".checkbox1").click(function(){
 
        if($(".checkbox1").length == $(".checkbox1:checked").length) {
            $("#selectall").prop("checked", "checked");
        } else {
            $("#selectall").removeAttr("checked");
        }
 
    });
    
    $(document).on('click','#rSelectall', function(e) {
    //$("#rSelectall").on('click',function () {
          $('.rcheckbox').prop('checked', $(this).prop('checked'));
    });
    
    $(document).on('click','.rcheckbox', function(e) {
     //$(".rcheckbox").on('click',function(){
        if($(".rcheckbox").length == $(".rcheckbox:checked").length) {
            $("#rSelectall").prop("checked", "checked");
        } else {
            $("#rSelectall").removeAttr("checked");
        }
 
    });
    
    $("textarea#U_comment").one("keypress", function (){
    setStartTime();
  });
  
    $("#U_submit_comment").unbind('click');
    $( "#U_submit_comment" ).on('click',function() {
    // $("#U_submit_comment").click(function(){
         var st = $('#U_startTime_tb').val();
         var et = $('#U_endTime_tb').val();
         var com = $("#U_comment").val();
         if ($("#U_startTime_tb").val() == "") {
                $("#U_startTime_tb").css({"border-color": "red", 
                                         "border-width":"4px", 
                                         "border-style":"solid"});
                $("#U_Comment_Times").append("<p id=\"startComAlert\"><b>Please enter a Start Comment time.</b></p>");
                $("#startComAlert").css({"color": "red"});
                return false;
            } else {
                $("#U_startTime_tb").removeAttr("style");
                $("#startComAlert").remove();
            }
            
         if ($("#U_endTime_tb").val() == "") {
                $("#U_endTime_tb").css({"border-color": "red", 
                                         "border-width":"4px", 
                                         "border-style":"solid"});
                $("#U_Comment_Times").append("<p id=\"startComAlert\"><b>Please set an End Comment time.</b></p>");
                $("#startComAlert").css({"color": "red"});
                $("#defaultEndTime").show();
                return false;
            } else {
                $("#U_endTime_tb").removeAttr("style");
                $("#startComAlert").remove();
            } 
            if ($("#U_comment").val() == "") {
                $("#Comment_Alert").append("<p id=\"ComAlert\"><b>Please Enter some text.</b></p>");
                $("#ComAlert").css({"color": "red"});
                return false;
            } else {
                $("#ComAlert").remove();
            }
            
           if($(".checkbox1:checked").length == 0 && (!($('#p_checkbox').val)) ){
               $("#Comment_Alert").append("<p id=\"ComAlert\"><b>Please Select Researchers.</b></p>");
                $("#ComAlert").css({"color": "red"});
                return false;
            } else {
                $("#ComAlert").remove();
            } 
            var researchIDs = $("#U_Comment_Com input:checkbox:checked").map(function(){
                           return $(this).val();}).get();
            
            var IDs;
            if($('#p_checkbox').val){
               IDs = $('#p_checkbox').val();
            }
            for (i=0;i<researchIDs.length;i++)
            {
                IDs = IDs + "," +researchIDs[i];
            }
            var res = st.split(":");
            startTime=(res[0]*3600) +(res[1]*60)+(res[2]*1);
            res = et.split(":");
            endTime = (res[0]*3600) +(res[1]*60)+(res[2]*1);
            if(et == "9999:9999:9999"){
                et = "not set";
            }
          //alert(st + " " + et + " " + com + " "+IDs);
            $.ajax({
               dataType: "json",
                type: "POST",
                url: "/comment/addResearcherVideoComment",
                data: { surveyId :surveyId ,startTime : startTime, endTime : endTime, text: com, viewers :IDs}, 
                cache: false,
                success: function(data){
                   // alert(data.id);
                    $( "#U_submittedComments" ).append( "<div class=\"submitted_Com researcherComments rTopComment\"><b>"+st+" - "+et+"</b> "+data.userName+"<p id=\"comment"+data.id+"\">"+com +"</p><a href=\"#\" class=\"comment_button\" id=\'"+data.id+"\'>Comment</a><button class=\"edit_button\"  id=\"edit"+data.id+"\">Edit</button></div><div id=\"loadplace"+data.id+"\"></div><div id=\"flash"+data.id+"\" class=\"flash_load\"></div><div class=\"panel\" id=\"slidepanel"+data.id+"\"><form action=\"\" method=\"post\" name=\'"+data.id+"\'><textarea style=\"width:390px;height:23px\" id=\"textboxcontent"+data.id+"\" ></textarea><br /><input type=\"submit\" value=\"Comment_Submit\"  class=\"comment_submit\" id=\'"+data.id+"\'/></form></div>" );
                },
                failure:function(){alert("Failure!!");
                }
            });         
         $("#U_comment").val('');
         return false;
         
     });
     
        $("#defaultEndCommentNo").unbind('click');
        $("#defaultEndCommentNo").click(function () {
            $("#startComAlert").remove();
            $("#defaultEndTime").hide();
            $("#U_Comment_Times").append("<p id=\"startComAlert\"><b>Please select an End Comment time by clicking on the end comment button.</b></p>");
            return false;
        });
        
         $("#defaultEndCommentYes").unbind('click');
        $("#defaultEndCommentYes").click(function () {
            $("#startComAlert").remove();
            $("#defaultEndTime").hide();
            $('#U_endTime_tb').val("9999:9999:9999");
            if ($('#U_submit_comment').val()) {
                $( "#U_submit_comment" ).trigger( "click" );
            }
            else{
            $( "#P_submit_comment" ).trigger( "click" );
        }
            return false;
            
        });
        
    // $(".comment_button").unbind('click');
     $(document).unbind('click').on('click','.comment_button', function(e) {
        var element = $(this);
        var I = element.attr("id");
        var len = $('.pFirstTierComment'+I).length + $('.pSecondTierComment'+I).length + $('.pfSecondTierComment'+I).length + $('.rFirstTierComment'+I).length;
        $(".pFirstTierComment"+I).hide('slow');
        $(".pSecondTierComment"+I).hide('slow');
        $(".pfSecondTierComment"+I).hide('slow');
        $(".rFirstTierComment"+I).hide('slow');        
        if(len > 0){
        $(".show"+I).show('slow');
        }
        else{
           // alert();
        }
        $("#list").show();
        $("#slidepanel"+I).slideToggle(300);
        $(this).toggleClass("active"); 
       // $('html, body').animate({ scrollTop: $("#slidepanel"+I).offset().top}, 2000);
        return false;
    });
    
    $(document).on('click','.comment_showbutton', function(e) {
        var element = $(this);
        var I = element.attr("id");
        $(".pFirstTierComment"+I).show('slow');
        $(".pSecondTierComment"+I).show('slow');
        $(".pfSecondTierComment"+I).show('slow');
        $(".rFirstTierComment"+I).show('slow');      
        $(".show"+I).hide('slow');
        return false;
    });
    $(document).on('click','.comment_submit', function(e) {
        var element = $(this);
        var Id = element.attr("id");

        var test = $("#textboxcontent"+Id).val();
        var dataString = 'textcontent='+ test + '&com_msgid=' + Id;

        if(test=='')
        {
            alert("Please Enter Some Text"+Id);
        }
        else
        {
            $("#flash"+Id).show();
            $("#flash"+Id).fadeIn(400).html('<img src="ajax-loader.gif" align="absmiddle"> loading.....');
            
            $.ajax({
               dataType: "json",
                type: "POST",
                url: "/comment/addResearcherCommentComment",
                data: { surveyId: surveyId, text: test, commentId: Id},
                cache: false,
                success: function(data){
                    $("#loadplace"+Id).append("<div class=\"R_reply_block researcherComments rFirstTierComment"+Id+"\"><b>"+data.userName+"</b><p id=\"comment"+data.id+"\">"+test+"</p><button class=\"edit_button\"  id=\"edit"+data.id+"\">Edit</button></div>");
                    $("#flash"+Id).hide();
                    $("#slidepanel"+Id).hide();                   
                }
            });
        }
        $("#textboxcontent"+Id).val('');
        return false;
        
    });
    
    $(document).on('click','.r_comment_submit', function(e) {
        var element = $(this);
        var Id = element.attr("id");

        var test = $("#textboxcontent"+Id).val();
        var dataString = 'textcontent='+ test + '&com_msgid=' + Id;
        var spanel = $("#sidepanel"+Id);
       var researchIDs = $("#list input:checkbox:checked").map(function(){
                           return $(this).val();}).get();
        var rIDs;
            if($('#r_checkbox').val){
               IDs = $('#r_checkbox').val();
            }
            for (i=0;i<researchIDs.length;i++)
            {
                //IDs[i-1] = researchIDs[i];
                rIDs = rIDs + "," +researchIDs[i];
            }
        
        if(test=='')
        {
            alert("Please Enter Some Text");
        }
        else
        {
            $("#flash"+Id).show();
            $("#flash"+Id).fadeIn(400).html('<img src="ajax-loader.gif" align="absmiddle"> loading.....');
            $.ajax({
               dataType: "json",
                type: "POST",
                url: "/comment/addResearcherCommentComment",
                data: { surveyId: surveyId, text: test, commentId: Id, viewers: rIDs },
                cache: false,
                success: function(data){
                    $("#loadplace"+Id).append("<div class=\"R_reply_block participantSubmittedComments pFirstTierComment"+Id+"\"><b>"+data.userName+"</b><p id=\"comment"+data.id+"\">"+test+"</p><a href=\"#\" class=\"comment_button\" id=\""+data.id+"\">Comment</a><a href=\"#\" class=\"comment_showbutton show"+data.id+"\" id=\""+data.id+"\" style=\"display:none\">Show Replies</a><button class=\"edit_button\"  id=\"edit"+data.id+"\">Edit</button></div><div id=\"loadplace"+data.id+"\"></div><div id=\"flash"+data.id+"\" class=\"flash_load\"></div><div class=\"r_panel\" id=\"slidepanel"+data.id+"\"><form action=\"\" method=\"post\" name=\""+data.id+"\"><textarea style=\"width:390px;height:23px\" id=\"textboxcontent"+data.id+"\" ></textarea><br /><input type=\"submit\" value=\"Comment_Submit\"  class=\"r_r_comment_submit "+Id+"\" id=\""+data.id+"\"/></form></div>");
                    $("#flash"+Id).hide();
                    $("#slidepanel"+Id).hide();  
                }
            });
        }
        $("#textboxcontent"+Id).val('');
        return false;
        
    });
    
    $(document).on('click','.r_r_comment_submit', function(e) {
        var element = $(this);
        var Id = element.attr("id");
        var clsname = element.attr('class');
        var pParentId = clsname.split(/[ ,]+/)[1];
        var test = $("#textboxcontent"+Id).val();
        var dataString = 'textcontent='+ test + '&com_msgid=' + Id;
        
        if(test=='')
        {
            alert("Please Enter Some Text"+Id);
        }
        else
        {
            $("#flash"+Id).show();
            $("#flash"+Id).fadeIn(400).html('<img src="ajax-loader.gif" align="absmiddle"> loading.....');
            
            $.ajax({
               dataType: "json",
                type: "POST",
                url: "/comment/addResearcherCommentComment",
                data: { surveyId: surveyId, text: test, commentId: Id},
                cache: false,
                success: function(data){
                    $("#loadplace"+Id).append("<div class=\"R_reply_reply_block participantSubmittedComments pSecondTierComment"+pParentId+" pfSecondTierComment"+Id+"\"><b>"+ data.userName+"</b><p id=\"comment"+data.id+"\">"+test+"</p><button class=\"edit_button\"  id=\"edit"+data.id+"\">Edit</button></div>");
                    $("#flash"+Id).hide();
                    $("#slidepanel"+Id).hide();  
                }
            });
        }
        $("#textboxcontent"+Id).val('');
        return false;
        
    });
    
    $("#trigger").click(function(){
        var text = $("#input_to_read").val();
        var myData = {textData:text};
        $.ajax({
            dataType: "json",
            type:"POST",                                                
            url:"/survey/answer",
            data:myData,
            success: function(data){
                alert(data.text);
                
               $("#answer").text(data.text);
            },
            failure:function(){alert("Failure!!");
            }
        });
    });
    
    $("#P_submit_comment").unbind('click');
     $( "#P_submit_comment" ).on('click',function() {
    // $("#U_submit_comment").click(function(){
         var st = $('#U_startTime_tb').val();
         var et = $('#U_endTime_tb').val();
         var com = $("#U_comment").val();
         var viewable = $('input[name="viewable"]:checked', '#commentForm').val();
         if ($("#U_startTime_tb").val() == "") {
                $("#U_startTime_tb").css({"border-color": "red", 
                                         "border-width":"4px", 
                                         "border-style":"solid"});
                $("#U_Comment_Times").append("<p id=\"startComAlert\"><b>Please enter a Start-Comment time.</b></p>");
                $("#startComAlert").css({"color": "red"});
                return false;
            } else {
                $("#U_startTime_tb").removeAttr("style");
                $("#startComAlert").remove();
            }
            
         if ($("#U_endTime_tb").val() == "") {
                $("#U_endTime_tb").css({"border-color": "red", 
                                         "border-width":"4px", 
                                         "border-style":"solid"});
                $("#U_Comment_Times").append("<p id=\"startComAlert\"><b>Please set an End Comment time.</b></p>");
                $("#startComAlert").css({"color": "red"});
                $("#defaultEndTime").show();
                return false;
            } else {
                $("#U_endTime_tb").removeAttr("style");
                $("#startComAlert").remove();
            } 
            if ($("#U_comment").val() == "") {
                $("#Comment_Alert").append("<p id=\"ComAlert\"><b>Please Enter some text.</b></p>");
                $("#ComAlert").css({"color": "red"});
                return false;
            } else {
                $("#ComAlert").remove();
            }
            
            if (!$('[name="viewable"]').is(':checked')){
                 $("#View_Alert").append("<p id=\"ComAlert\"><b>Please Select Viewability.</b></p>");
                 $("#ComAlert").css({"color": "red"});
                return false;
            }else {
                $("#ComAlert").remove();
            }      
           
            var res = st.split(":");
            startTime=(res[0]*3600) +(res[1]*60)+(res[2]*1);
            res = et.split(":");
            endTime = (res[0]*3600) +(res[1]*60)+(res[2]*1);
            if(et == "9999:9999:9999"){
                et="not set";
            }
           // alert(st + " " + et + " " + com + " "+ currentUser +" "+viewable);
            $.ajax({
               dataType: "json",
                type: "POST",
                url: "/comment/addParticipantComment",
                data: { surveyId :surveyId ,startTime : startTime, endTime : endTime, text: com, viewable : viewable}, 
                cache: false,
                success: function(data){
                    
                    $( "#U_submittedComments" ).append( "<div class=\"submitted_Com selfComments\"><b>"+st+" - "+et+"</b> "+data.userName+"<p id=\"comment"+data.id+"\">"+com +"</p><button class=\"edit_button\"  id=\"edit"+data.id+"\">Edit</button></div>" );
                },
                failure:function(){alert("Failure!!");
                }
            });         
         $("#U_comment").val('');
         return false;
         
     });
     
    $("[name=pCommentsToggler]").unbind('click');
    $("[name=pCommentsToggler]").click(function(){

        $(".otherComments").toggle();
    });
    
    
   
        $("[name=rCommentsToggler]").click(function(){
        
        var value = $(this).val();
        
        if(value === "researcherComments"){
            $(".researcherComments").show();
            $(".participantActiveComments").hide();
            $(".participantSubmittedComments").hide();
        }
        if(value === "participantActiveComments"){
            $(".participantActiveComments").show();
            $(".participantSubmittedComments").hide();
            $(".researcherComments").hide();
        }
        if(value === "participantSubmittedComments"){
            $(".participantActiveComments").hide();
            $(".participantSubmittedComments").show();
            $(".researcherComments").hide();
        }
        if(value === "AllComments"){
            $(".participantActiveComments").show();
            $(".participantSubmittedComments").show();
            $(".researcherComments").show();
        }
//        $('.otherComment').hide();
//            $("."+$(this).val()).show();
    });
    
    $(".edit_button").unbind('click');
     $(document).on('click','.edit_button', function(e) {              
        var element = $(this);
        var Id = element.attr("id");
        Id = Id.split("edit")[1]; 
        $('#edit-comment-id').val(Id);
        var text = $("#comment"+Id).text();
        $('textarea#edit-comment').val(text);
        $( "#edit-form" ).dialog( "open" );
        event.preventDefault();
    });
    
    $( "#edit-form" ).dialog({
	autoOpen: false,
	height: 200,
	width: 450,
	modal: true,
	buttons: {
            "Submit": function() {
		$.ajax({
                    
                       dataType: "json",
		       type: "POST",
		       url: '/comment/editComment',
		       data: {
                            textData: $("textarea#edit-comment").val(),
                            commentId: $("#edit-comment-id").val()
                       },
		       success: function(data)
		       {
                           
                           $("#comment"+data.Id).text(data.text);
		       },
                       failure: function(){
                           alert("failed");
                       }
                       
		   }); 
                   $( this ).dialog( "close" );
            },
            Cancel: function() {
		$( this ).closest('.ui-dialog-content').dialog( "close" );
            }
	},
	close: function() {
            $("#edit-comment").val("");
            $('#edit-comment-id').val("");
	}
	});
        
   
   
    $("#U_submit_all").unbind('click');
     $( "#U_submit_all" ).on('click',function() { 
     $("#dialog-confirm").html("<p>Are you sure you want to submit comments? This will conclude your work with this survey, and you will not be allowed back to change your answers.</p>");
     $( "#dialog-confirm" ).dialog({
      resizable: true,
      height:270,
      modal: true,
      buttons: {
        "Confirm Submission": function() {
          $.ajax({
                dataType: 'json',
                type: 'POST',
                url: '/survey/SubmitSurvey',
                data: {surveyId :surveyId }, 
                success:function(data){
                    
                  $( "#surveyView" ).replaceWith("<h1 style=\"align:centre\"; >Thank you for submitting the survey!!</h1>");
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
     });
});

function setStartTime() { 
    //set start time in hh:mm:ss
        var num = player.getCurrentTime();
        player.pauseVideo();
        num = Math.floor(num);
        var hours   = Math.floor(num/3600);
        var minutes = Math.floor((num - (hours*3600))/60);
        var seconds = Math.floor(num%60);
        
	var sTime;
	if (hours < 10) {
		hours = "0" + hours;
	}
	if (minutes < 10) {
		minutes = "0" + minutes;
	}
	if (seconds < 10) {
		seconds = "0" + seconds;
	}
   	sTime = hours + ":" + minutes + ":" + seconds;
	startTime = sTime;
	document.question.U_startTime_tb.value = sTime; 
	startTime.value = document.question.U_startTime_tb.value;
   }

   function setEndTime() { 
        //vcom endCom = true;

        //set end time in hh:mm:ss
        
        var num = player.getCurrentTime();
        num = Math.floor(num);
        var hours   = Math.floor(num/3600);
        var minutes = Math.floor((num - (hours*3600))/60);
        var seconds = Math.floor(num%60);
        
	var sTime;
	if (hours < 10) {
		hours = "0" + hours;
	}
	if (minutes < 10) {
		minutes = "0" + minutes;
	}
	if (seconds < 10) {
		seconds = "0" + seconds;
	}
	eTime = hours + ":" + minutes + ":" + seconds;
	endTime = eTime;
	document.question.U_endTime_tb.value = eTime; 
	endTime.value = document.question.U_endTime_tb.value;
   }
 
   var tag = document.createElement('script');
   tag.src = "https://www.youtube.com/player_api";
   var firstScriptTag = document.getElementsByTagName('script')[0];
   firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
   var player;
   var previousOffset;
   function onYouTubePlayerAPIReady() {
      player = new YT.Player('player', {
      events: {
         'onReady': startPlayer 
      }
      });
   }

   function startPlayer() { 
      previousOffset = document.getElementById("masterOffset").textContent;
      var vLink = document.getElementById("masterVideo").textContent;
      var link = vLink.split(/[\&=\s]/)[1];
      player.cueVideoById({'videoId': link, 'startSeconds': document.getElementById("masterSTime").textContent, 'endSeconds': document.getElementById("masterETime").textContent});
   }
    function get(elem) { return parseFloat(elem) || 0; }
    
    function newVideo(videoLink, startSeconds, endSeconds,offset) {
        var videoId = videoLink.split(/[\&=\s]/)[1];
        startSeconds = (get(player.getCurrentTime()) *1) + get(previousOffset)-get(offset);
        endSeconds = get(endSeconds)+ get(previousOffset)- get(offset);
        previousOffset= get(offset);
        //alert(startSeconds + " " + get(player.getCurrentTime()) +" " +endSeconds + " "+ previousOffset);
	player.loadVideoById({'videoId': videoId, 'startSeconds': startSeconds, 'endSeconds': endSeconds});
 }



//
////
////<<<<<<< HEAD
////=======
//    //vcom keep track of whether user has set End Comment time
//    //var endCom = false;
////>>>>>>> d252173817eb3d62a2627e4eb00dbd5cbce8d6c4
//
//   var tag = document.createElement('script');
//   tag.src = "https://www.youtube.com/player_api";
//   var firstScriptTag = document.getElementsByTagName('script')[0];
//   firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
//   var player;
//   var startTime = 122; 
//   var endTime = 152; 
//   videoId: 'TCOCowngD5s'; 
//
//   function onYouTubePlayerAPIReady() {
//      player = new YT.Player('player', {
//      /*events: {
//         'onReady': startPlayer 
//      }*/
//      });
//   }
// 
//   function startPlayer() { 
//	var videoId= 'o1dBg__wsuo#t=20';
//	player.loadVideoById({'videoId': videoId, 'startSeconds': startTime, 'endSeconds': endTime});
//
//	// set start time values	
//        var sTime = document.question.U_startTime_tb;
//	var Sseconds = Math.floor((startTime%60)*10)/10;
//  	var Sminutes = Math.floor(startTime/60);
//	var Shours   = Math.floor(startTime/3600);
//
//	//double digits for hours/minutes/seconds  
//	if (Shours < 10) {
//		Shours = "0" + Shours;
//	}
//	if (Sminutes < 10) {
//		Sminutes = "0" + Sminutes;
//	}
//	if (Sseconds < 10) {
//		Sseconds = "0" + Sseconds;
//	}
//	sTime.value = Shours + ":" + Sminutes + ":" + Sseconds;
//
//	// set end time values
//        var eTime = document.question.U_endTime_tb;
//  	var Eseconds = Math.floor((endTime%60)*10)/10;
//  	var Eminutes = Math.floor(endTime/60);
//	var Ehours   = Math.floor(endTime/3600);
//
//	//double digits for hours/minutes/seconds  
//	if (Ehours < 10) {
//		Ehours = "0" + Ehours;
//	}
//	if (Eminutes < 10) {
//		Eminutes = "0" + Eminutes;
//	}
//	if (Eseconds < 10) {
//		Eseconds = "0" + Eseconds;
//	}
//	eTime.value = Ehours + ":" + Eminutes + ":" + Eseconds;
//    }
//
//   //append comments in div
//   var i = 0;
//   var m;
//   var temp_s1, temp_s2, temp_e1, temp_e2, temp_c1temp_c2;
//   var str = "";
//   var s = new Array(); //start times
//   var e = new Array(); //end times
//   var c = new Array(); //comments
//
//   function appendComment() {
//       
//	//append comments sorted by startTime, then endTime
//	var myComment = document.question.U_comment.value;
//	var myDiv = document.getElementById("U_submittedComments");
//	enter_Time();
//	var len = s.length;
//	str = "";
//	m = 0;
//	var newComment = document.question.U_comment.value;
//	for (i=0; i<=len; i++) {
//		if (len>0) {
//			if ((startTime < s[i] && m==0) || (startTime == s[i] && endTime < e[i] && m==0)) { 
//				//insert new start/end times
//				temp_s1 = s[i];
//				s[i] = startTime;
//				temp_e1 = e[i];
//				e[i] = endTime;
//				//insert comment
//				temp_c1 = c[i];
//				c[i] = newComment;
//				m = 1; //inserted new values
//			}	
//			else if (m==1) {
//				//move start times after new adddition
//				temp_s2 = s[i];
//				s[i] = temp_s1;
//				temp_s1 = temp_s2;
//				//move end times after new addition
//				temp_e2 = e[i];
//				e[i] = temp_e1;
//				temp_e1 = temp_e2;
//				//get comment
//				temp_c2 = c[i];
//				c[i] = temp_c1;;
//				temp_c1 = temp_c2;
//			}
//			else if (i==len) {
//				//append new start/end times to timeline
//				s[i] = startTime;
//				e[i] = endTime;
//				//get comment
//				c[i] = newComment;
//			}
//			else {
//				//keep values in same order
//				s[i] = s[i];
//				e[i] = e[i];
//				//get comment
//				c[i] = c[i];
//			}
//		}
//		else {
//			//first value in timeline
//			s[0] = startTime;
//			e[0] = endTime;
//			c[0] = newComment;
//		}
//	}
//        
//        //if (user is a researcher) {
//            for (j=0; j <= len; j++) {
//                /*var appended_R_Com = "\n\
//                        <div class =\"submitted_Com\" id=\"submitted_Com_id"+j+"\">\n\
//                                <span class=\"timeSection\"\n\
//                                    <p><b>" + s[j] + " - " + e[j] + "</b></p>\n\
//                                </span>\n\
//                                <span class=\"commentSection\">\n\
//                                    <p>" + c[j] + "</p>\n\
//                                </span>\n\
//                                <span class=\"R_reply_button\" onclick =\"addReplyBox($(this).parent(),"+j+");\">\n\
//                                    Reply\n\
//                                </span>\n\
//                        </div>";
//                    
//                    str = str + appended_R_Com;
//                    // vcom delete when above is figured out
//                    /*str = str + 
//                        "<div class=\"submitted_Com\" id=\"submitted_Com_id"+j+"\">\n\
//                            <div class=\"timeSection\"><p><b>" + s[j] + " - " + e[j] + "</b></p></div>"
//                         + "<div class=\"commentSection\"><p>" + c[j] + "</p></div>\n\
//                            <span class=\"R_reply_button\" onclick=\"addReplyBox($(this).parent(),"+j+");\">Reply</span>\n\
//                         </div>";*/
//                    str = str + "\n\
//                        <div class = \"comment_block\" id=\"comment_block_id"+j+"\">\n\
//                            <div class =\"submitted_Com\" id=\"submitted_Com_id"+j+"\">\n\
//                                <div class=\"timeSection\"\n\
//                                    <p><b>" + s[j] + " - " + e[j] + "</b></p>\n\
//                                </div>\n\
//                                <div class=\"commentSection\">\n\
//                                    <p>" + c[j] + "</p>\n\
//                                    <span class=\"R_reply_button\" onclick=\"addReplyBox($(this).parent().parent().parent(),"+j+");\">Reply</span>\n\
//                                </div>\n\
//                            </div>\n\
//                        </div>";
//            }
//        //}
//        
//        /* if (user is participant) {
//            for (j=0; j <= len; j++) {
//                    str = str + "<div class=\"submitted_Com\"><div class=\"timeSection\"><p><b>" + s[j] + " - " + e[j] + "</b></p></div>"
//                          + "<div class=\"commentSection\"><p>" + c[j] + "</p></div></div>";
//            }
//        }*/
//        
//	myDiv.innerHTML = str;
//	U_comment.value = "";
//	myDiv.scrollTop = myDiv.scrollHeight;
//        
//        //clear Start and End Comment time for next comment
//        $("#U_startTime_tb").val("");
//        $("#U_endTime_tb").val("");
//   }
//   
//   //vcom For researcher to reply to participant comments -- adding reply BOX
//   function addReplyBox(currentComment, index) {
//       currentComment.append("\
//            <div class=\"R_reply_emptyblock\">\n\
//                <form>\n\
//                    <table class=\"R_reply_viewableto\">\n\
//                        <tr>\n\
//                            <td>\n\
//                                <input type=\"checkbox\" name=\"R_reply_viewable\" value=\"participants\">Participants<br>\n\
//                            </td>\n\
//                            <td>\n\
//                                <input type=\"checkbox\" name=\"R_reply_viewable\" value=\"participants\">Researchers<br>\n\
//                            </td>\n\
//                        </tr>\n\
//                        <tr>\n\
//                            <td class=\"table-indent\">\n\
//                                <input type=\"radio\" name=\"R_reply_viewable\" value=\"participants\">All<br>\n\
//                                <input type=\"radio\" name=\"R_reply_viewable\" value=\"participants\">Group<br>\n\
//                                <input type=\"radio\" name=\"R_reply_viewable\" value=\"participants\">Individal<br>\n\
//                            </td>\n\
//                            <td class=\"table-indent\">\n\
//                                <input type=\"radio\" name=\"R_reply_viewable\" value=\"participants\">All<br>\n\
//                                <input type=\"radio\" name=\"R_reply_viewable\" value=\"participants\">Group<br>\n\
//                                <input type=\"radio\" name=\"R_reply_viewable\" value=\"participants\">Individal<br>\n\
//                            </td>\n\
//                        </tr>\n\
//                    </table>\n\
//                    <textarea class=\"R_reply_textbox\" onkeypress=\"addReply(event,"+index+");\"></textarea>\n\
//                </form>\n\
//            </div>");
//   }
//   
//   //vcom For research to type reply in reply box -- adding TEXT
//   function addReply(event, index) {
//       if (event.which == 13) {
//           var rReply = $(".R_reply_box").val();
//           $(".R_reply_emptyblock").remove();
//           $("#comment_block_id"+index+"").append("\
//                <div class=\"R_reply_block\">\n\
//                    <span class=\"R_reply_label\"><b>Researcher: </b></span>\n\
//                    <span>"+rReply+"<br>\n\
//                        <span class=\"R_reply_button\" onclick=\"addReplyBox($(this).parent().parent().parent(),"+index+");\">Reply</span>\n\
//                    </span>\n\
//                </div>");
//       }
//   }
//   
//   function submitAll() {
//	//open & write to new window
//	var myWindow = window.open("", "UserComments", "width=450,height=500");
//	var s = document.getElementById("U_submittedComments").innerHTML;
//	var c = s; 
//	myWindow.document.write(c);
//   }
//
//   function setStartTime() { 
//	//set start time in hh:mm:ss
//	var num = player.getCurrentTime();
//	var seconds = Math.floor((num%60)*10/10);
//	var minutes = Math.floor(num/60);
//	var hours   = Math.floor(num/3600);
//	var sTime;
//	if (hours < 10) {
//		hours = "0" + hours;
//	}
//	if (minutes < 10) {
//		minutes = "0" + minutes;
//	}
//	if (seconds < 10) {
//		seconds = "0" + seconds;
//	}
//   	sTime = hours + ":" + minutes + ":" + seconds;
//	startTime = sTime;
//	document.question.U_startTime_tb.value = sTime; 
//	startTime.value = document.question.U_startTime_tb.value;
//   }
//
//   function setEndTime() { 
//        //vcom endCom = true;
//
//        //set end time in hh:mm:ss
//	var num = player.getCurrentTime();
//	var seconds = Math.floor((num%60)*10/10);
//	var minutes = Math.floor(num/60);  
//	var hours   = Math.floor(num/3600);
//	var sTime;
//	if (hours < 10) {
//		hours = "0" + hours;
//	}
//	if (minutes < 10) {
//		minutes = "0" + minutes;
//	}
//	if (seconds < 10) {
//		seconds = "0" + seconds;
//	}
//	eTime = hours + ":" + minutes + ":" + seconds;
//	endTime = eTime;
//	document.question.U_endTime_tb.value = eTime; 
//	endTime.value = document.question.U_endTime_tb.value;
//   }
//   
//   //vcom check submitted input comment times
//   function checkComTimes() {
//       //check comment box
//
//            //check Start Comment time
//            if ($("#U_startTime_tb").val() == "") {
//                $("#U_startTime_tb").css({"border-color": "red", 
//                                         "border-width":"4px", 
//                                         "border-style":"solid"});
//                $("#U_Comment_Times").append("<p id=\"startComAlert\"><b>Please set a Start Comment time.</b></p>");
//                $("#startComAlert").css({"color": "red"});
//            } else {
//                $("#U_startTime_tb").removeAttr("style");
//                $("#startComAlert").remove();
//
//                 //check End Comment time
//                 if ($("#U_endTime_tb").val() == "") {
//                     $("#U_endTime_tb").css({"border-color": "red", 
//                                              "border-width":"4px", 
//                                              "border-style":"solid"});
//                     $("#U_Comment_Times").append("<p id=\"endComAlert\"><b>Would you like to set an End Comment time?</b></p>");
//                     $("#U_Comment_Times").append("<input type=\"submit\" id=\"U_skip_submit\" value=\"No, Skip\" onclick=\"removeSkip();return false;\">");
//                     $("#endComAlert").css({"color": "red", "display": "inline"});
//                 } else {
//                     appendComment();
//                     $("#U_endTime_tb").removeAttr("style");
//                     $("#endComAlert").remove();
//                     //endCom = false;
//                 }
//            }
//  
//   }
//   
//   //vcom handle Skip
//   function removeSkip() {
//       appendComment();
//       $("#U_endTime_tb").removeAttr("style");
//       $("#endComAlert").remove();
//       $("#U_skip_submit").remove();
//    }
//   
//   function seekTo() {
//	//convert time to seconds only
//	var seekTime = document.question.U_seekToTime.value;
//	var a = seekTime.split(':');
//	var time = (+a[0]) * 3600 + (+a[1]) * 60 + (+a[2]); 
//	player.seekTo(time, true); 
//   } 
//
//  // ENTER 
//   function enter_Seek(e) { 
//	if (e.keyCode == 13){
//		document.getElementById('R_seek').click();
//	}
//   }
//
//   function enter_Comment(e) {
//	document.getElementById('R_comment').click();
//	enter_Time();
//   } 
//
//   function enter_Time() {
//	//set startTime/endTime variables
//	startTime = document.question.U_startTime_tb.value;
//	startTime.value = document.question.U_startTime_tb.value;
//	endTime = document.question.U_endTime_tb.value;
//	endTime.value = document.question.U_endTime_tb.value;
//   }
//
//   function newVideo(videoId, startSeconds, endSeconds) {
//	//load new video
//	player.loadVideoById({'videoId': videoId, 'startSeconds': startSeconds, 'endSeconds': endSeconds});
//	var myDiv = document.getElementById("U_submittedComments");
//	myDiv.scrollTop = myDiv.scrollHeight; 
//
//        //set start time in hh:mm:ss
//	var start = startSeconds;
//	var seconds = Math.floor((start%60)*10)/10;
//	var minutes = Math.floor(start/60);
//	var hours   = Math.floor(start/3600);
//	var sTime;
//	if (hours < 10) {
//		hours = "0" + hours;
//	}
//	if (minutes < 10) {
//		minutes = "0" + minutes;
//	}
//	if (seconds < 10) {
//		seconds = "0" + seconds;
//	}
//   	sTime = hours + ":" + minutes + ":" + seconds;
//	startTime = sTime;
//	//document.question.U_startTime_tb.value = sTime; 
//	startTime.value = document.question.U_startTime_tb.value;
//	//set end time in hh:mm:ss
//	var end = endSeconds;
//	var seconds = Math.floor((end%60)*10)/10;
//	var minutes = Math.floor(end/60);  
//	var hours   = Math.floor(end/3600);
//	var sTime;
//	if (hours < 10) {
//		hours = "0" + hours;
//	}
//	if (minutes < 10) {
//		minutes = "0" + minutes;
//	}
//	if (seconds < 10) {
//		seconds = "0" + seconds;
//	}
//	eTime = hours + ":" + minutes + ":" + seconds;
//	endTime = eTime;
//	//document.question.U_endTime_tb.value = eTime; 
//	endTime.value = document.question.U_endTime_tb.value;
//   }