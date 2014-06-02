<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<link rel="stylesheet" type="text/css" href="Stylesheet.css">
</head>

<?php include 'header.php'; ?>
<body>
<div id="R_title" style="text-align:center">
   <b>Survey Title</b>
<div id="R_user" style="text-align:right">Researcher
</div>
</div>
<div id="R_prompt" value="">
   <b>Survey Prompt.</b>
</div>

<div id="R_body">
<!--video+5 buttons-->
<div id="R_videoButtons"> 
   <!--YouTube player-->
   <div id="player">
   </div>
   <!--five video buttons-->
   <!--GRAEME, change the following videoId, startTime and endTime for different videos-->
   <div id="R_fiveVideos">
      <input type="button" id="R_video1" value="left"  
      onclick="newVideo('TCOCowngD5s', '312','374');return false;">
      <input type="button" id="R_video2" value="right" 
      onclick="newVideo('TCOCowngD5s', '132','244');return false;">
      <input type="button" id="R_video3" value="back" 
      onclick="newVideo('TCOCowngD5s', '219','254');return false;">
      <input type="button" id="R_video4" value="front" 
      onclick="newVideo('TCOCowngD5s', '254','344');return false;">
      <input type="button" id="R_video5" value="composite" 
      onclick="newVideo('TCOCowngD5s', '212','301');return false;">
   </div>
</div>

<!--everything div-->
<div id="R_Comment_div"> 
      <form name="question">
      <input type="button" id="R_seek" value="Seek to:" onclick="seekTo();return false;"> 
      <input type="text" id="R_seekToTime" onkeypress="enter_Seek(event);" value="hh:mm:ss" onfocus="this.value= ''">
      <input type="button"  id="R_start" value="Start Comment:" onclick="setStartTime();return false;">
      <input type="text" id="R_startTime_tb" onkeypress="enter_Time();">
      <input type="button" id="R_end" value="End Comment:" onclick="setEndTime();return false;">
      <input type="text" id="R_endTime_tb" onkeypress="enter_Time();"><br> 
      Please add a comment in the below textbox. You can select a start and stop <br> 
      time by clicking on the respective buttons above at the time you want. <br>
      <textarea type="comment" id="R_comment" onkeypress="enter_Comment(event);" value=""></textarea><br><br>
      <input type="submit" id="R_submit_comment" value="Add comment" onclick="appendComment();return false;"><br><br>
      </form>
</div>
</div>

<!--comments+submit_all button-->
<div id="R_comments"> 
   <div id="R_submittedComments">
      <div id="R_submittedComments_div"><b><u>User Comments:</u></b><br><br>
      <div id="R_U_submittedComments"><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>(Click on the below buttons to get the <br>comments from the specific users.)
      </div>
      <div id="R_getComments">
      <input type="button" id="R_buttonGetComments" value="Get comments!" onclick="updateComments();return false;">
      </div>	
      <div id="R_Checkbox_div">
      <input type="checkbox" id="R_checkbox_A" onclick="add_UserA()"/>User A
      <input type="checkbox" id="R_checkbox_B" onclick="add_UserB()"/>User B
      <input type="checkbox" id="R_checkbox_All" onclick="check_All()"/>All Users
      </div>
      </div>
      <div id="R_CommentsOnUser_div"><u><b>Comments Area:</b></u>
      <div id="R_CommentsOnUser">
      </div>
      </div>
   </div>
   <button id="R_submit_all" value="submit all" onclick="submitAll();return false;">Submit all!<br><br>
   </div>
</div>

<?php include 'footer.php'; ?>

<!--scripts-->
<script>
   var tag = document.createElement('script');
   tag.src = "https://www.youtube.com/player_api";
   var firstScriptTag = document.getElementsByTagName('script')[0];
   firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
   var player;
   var startTime = 122; 
   var endTime = 152; 
   videoId: 'TCOCowngD5s'; 
   var UserA = 0;
   var UserB = 0;
   var UserAll = 0;

   function onYouTubePlayerAPIReady() {
      player = new YT.Player('player', {
      });
   }
 
   function startPlayer() { 
	var videoId= 'o1dBg__wsuo#t=20';
	player.loadVideoById({'videoId': videoId, 'startSeconds': startTime, 'endSeconds': endTime});
	
	// set start time values	
        var sTime = document.question.R_startTime_tb;
	var Sseconds = Math.floor((startTime%60)*10)/10;
  	var Sminutes = Math.floor(startTime/60);
	var Shours   = Math.floor(startTime/3600);

	//double digits for hours/minutes/seconds  
	if (Shours < 1) {
		Shours = "";
		if (Sminutes < 1) {
			Sminutes = "";
			if (Sseconds < 1) {
				Sseconds = "0";
			}
		}
	}
	else {
		Shours = Shours + ":";
		if (Eminutes < 10) {
			Sminutes = "0" + Sminutes;
		}
		if (Sseconds < 10) {
			Sseconds = "0" + Sseconds;
		}
	}
	sTime.value = Shours + Sminutes + Sseconds;

	// set end time values
        var eTime = document.question.R_endTime_tb;
  	var Eseconds = Math.floor((endTime%60)*10)/10;
  	var Eminutes = Math.floor(endTime/60);
	var Ehours   = Math.floor(endTime/3600);

	//double digits for hours/minutes/seconds  
	if (Ehours < 10) {
		Ehours = "";
		if (Ehours < 10) {
			Eminutes = "";
			if (Eseconds == 0) {
				Eseconds = "0";
			}
		}
	}
	else {
		Ehours = Ehours + ":";
		if (Eminutes < 10) {
			Eminutes = "0" + Eminutes;
		}
		if (Eseconds < 10) {
			Eseconds = "0" + Eseconds;
		}
	}
	eTime.value = Ehours + Eminutes + Eseconds;
    }

   //append comments in div
   var i = 0;
   var m, n;
   var temp_s1, temp_s2, temp_e1, temp_e2, temp_vs1, temp_vs2, temp_ve1, temp_ve2, temp_c1, temp_c2;
   var str = "";
   var s = new Array(); //start times
   var e = new Array(); //end times
   var view_s = new Array(); //viewable start times
   var view_e = new Array(); //viewable end times
   var c = new Array(); //comments

   function appendComment() {
	enter_Time();
	//append comments sorted by startTime, then endTime
	var myComment = document.question.R_comment.value;
	//make viewable startTime
	var view_startTime;
	var a = startTime.split(':');
	if ((+a[0])=="00" && ((+a[1]))=="00") {
		if ((+a[2]) < 10) {
			view_startTime = "0" + (+a[2]);
		}
		else {
			view_startTime = (+a[2]);
		}
	}
	else if ((+a[0])=="00") {
		if ((+a[2]) < 10 && ((+a[1])) < 10) {
			view_startTime = "0" + (+a[1]) + ":0" + (+a[2]);
		}
		else if ((+a[2]) < 10) {
			view_startTime = (+a[1]) + ":0" + (+a[2]);
		}
		else if ((+a[1]) < 10) {
			view_startTime = "0" + (+a[1]) + ":" + (+a[2]);
		}
		else {
			view_startTime = (+a[1]) + ":" + (+a[2]);
		}
	}
	else {
		view_startTime = startTime;
	}
	//make viewable endTime
	var view_endTime;
	var b = endTime.split(':');
	if ((+b[0])=="00" && ((+b[1]))=="00") {
		if ((+b[2]) < 10) {
			view_endTime = "0" + (+b[2]);
		}
		else {
			view_endTime = (+b[2]);
		}
	}
	else if ((+b[0])=="00") {
		if ((+b[2]) < 10 && ((+b[1])) < 10) {
			view_endTime = "0" + (+b[1]) + ":0" + (+b[2]);
		}
		else if ((+b[2]) < 10) {
			view_endTime = (+b[1]) + ":0" + (+b[2]);
		}
		else if ((+b[1]) < 10) {
			view_endTime = "0" + (+b[1]) + ":" + (+b[2]);
		}
		else {
			view_endTime = (+b[1]) + ":" + (+b[2]);
		}
	}
	else {
		view_endTime = endTime;
	}
	
	//append comments with startTime/endTime
	if (myComment != "") {
		var myDiv = document.getElementById("R_CommentsOnUser");
		var len = s.length;
		str = "";
		m = 0;
		var newComment = document.question.R_comment.value;
		for (i=0; i<=len; i++) {
			if (len>0) {
				if (startTime == s[i] && endTime == e[i] && m==0) {
					s[i] = s[i];
					e[i] = e[i];
					view_s[i] = view_s[i];
					view_e[i] = view_e[i];
					//get comment
					c[i] = c[i];	
					n = 0;
				}
				else if (n == 0) {
				//insert new start/end times
				temp_s1 = s[i];
				s[i] = startTime;
				temp_vs1 = view_s[i];
				view_s[i] = view_startTime;

				temp_e1 = e[i];
				e[i] = endTime;
				temp_ve1 = view_e[i];
				view_e[i] = view_endTime;
				//insert comment
				temp_c1 = c[i];
				c[i] = newComment;
				m = 1; //inserted new values
				n = 1; 
				}
				else if ((startTime < s[i] && m==0) || (startTime == s[i] && endTime < e[i] && m==0)) { 
					//insert new start/end times
					temp_s1 = s[i];
					s[i] = startTime;
					temp_vs1 = view_s[i];
					view_s[i] = view_startTime;

					temp_e1 = e[i];
					e[i] = endTime;
					temp_ve1 = view_e[i];
					view_e[i] = view_endTime;
					//insert comment
					temp_c1 = c[i];
					c[i] = newComment;
					m = 1; //inserted new values
				}	
				else if (m==1) {
					//move start times after new adddition
					temp_s2 = s[i];
					s[i] = temp_s1;
					temp_s1 = temp_s2;

					temp_vs2 = view_s[i];
					view_s[i] = temp_vs1;
					temp_vs1 = temp_vs2;
 					//move end times after new addition
					temp_e2 = e[i];
					e[i] = temp_e1;
					temp_e1 = temp_e2;
					
					temp_ve2 = view_e[i];
					view_e[i] = temp_ve1;
					temp_ve1 = temp_ve2;				
					//get comment
					temp_c2 = c[i];
					c[i] = temp_c1;;
					temp_c1 = temp_c2;
				}
				else if (i==len) {
					//append new start/end times to timeline
					s[i] = startTime;
					view_s[i] = view_startTime;
					e[i] = endTime;	
					view_e[i] = view_endTime;
					//get comment
					c[i] = newComment;
				}
				else {
					//keep values in same order
					s[i] = s[i];
					view_s[i] = view_s[i];
					e[i] = e[i];
					view_e[i] = view_e[i];
					//get comment
					c[i] = c[i];
				}
			}
			else {
				//first value in timeline
				s[0] = startTime;
				e[0] = endTime;
				c[0] = "<i>" + newComment + "</i>";
				view_s[0] = view_startTime;
				view_e[0] = view_endTime;
			}
		}
		var string;
		for (j=0; j <= len; j++) {
			if (s[j] == "" && e[j] == "") {
				string = "";
			}
			else {
				string = "<b>" + "<u>" + view_s[j] + " - " + view_e[j] + ": " + "</u>" + "</b>" + "<br>";
			}
			if (j == 0) {
				str = str + string + c[j] + "<br>";
			}
			else if (s[j-1] == s[j] && e[j-1] == e[j]) {
				str = str + "<i>" + c[j] + "</i>" + "<br>";
			}
			else {
				str = str + "<hr>" + string + "<i>" + c[j] + "</i>" + "<br>" ;
			}		
		}
		myDiv.innerHTML = str;
		R_comment.value = "";
		myDiv.scrollTop = myDiv.scrollHeight; 
   	}
   }	

   function submitAll() {
	//open & write to new window
	var ResearcherWindow = window.open("","Researcher_Comments","width=100,height=10");
	var s = document.getElementById("R_CommentsOnUser").innerHTML;
	ResearcherWindow.document.write(s);
   }

   function setStartTime() { 
	//set startTime in hh:mm:ss
	var num = player.getCurrentTime();
	var seconds = Math.floor((num%60)*10)/10;
	var minutes = Math.floor(num/60);
	var hours   = Math.floor(num/3600);
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
	document.question.R_startTime_tb.value = sTime; 
	startTime.value = document.question.R_startTime_tb.value;
   }

   function setEndTime() { 
	//set endTime in hh:mm:ss
	var num = player.getCurrentTime();
	var seconds = Math.floor((num%60)*10)/10;
	var minutes = Math.floor(num/60);  
	var hours   = Math.floor(num/3600);
	var eTime;
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
	document.question.R_endTime_tb.value = eTime; 
	endTime.value = document.question.R_endTime_tb.value;
   }

   function seekTo() {
	//convert time to seconds only
	var seekTime = document.question.R_seekToTime.value;
	var a = seekTime.split(':');
	var time = (+a[0]) * 3600 + (+a[1]) * 60 + (+a[2]); 
	player.seekTo(time, true); 
   } 
   // ENTER 
   function enter_Seek(e) {  
	if (e.keyCode == 13){
		document.getElementById('R_seek').click();
	}
   }

   function enter_Comment(e) {
	document.getElementById('R_comment').click();
	enter_Time();
   } 

   function enter_Time() {
	//set startTime/endTime variables
	startTime = document.question.R_startTime_tb.value;
	startTime.value = document.question.R_startTime_tb.value;
	endTime = document.question.R_endTime_tb.value;
	endTime.value = document.question.R_endTime_tb.value;
   }

   function newVideo(videoId, startSeconds, endSeconds) {
	//load new video
	player.loadVideoById({'videoId': videoId, 'startSeconds': startSeconds, 'endSeconds': endSeconds});
	var myDiv = document.getElementById("R_CommentsOnUser");
	myDiv.scrollTop = myDiv.scrollHeight; 
	//set start time in hh:mm:ss	
	var start = startSeconds;
	var seconds = Math.floor((start%60)*10)/10;
	var minutes = Math.floor(start/60);
	var hours   = Math.floor(start/3600);
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
	document.question.R_startTime_tb.value = sTime; 
	startTime.value = document.question.R_startTime_tb.value;
	//set end time in hh:mm:ss
	var end = endSeconds;
	var seconds = Math.floor((end%60)*10)/10;
	var minutes = Math.floor(end/60);  
	var hours   = Math.floor(end/3600);
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
	document.question.R_endTime_tb.value = eTime; 
	endTime.value = document.question.R_endTime_tb.value;
   }
	
   function add_UserA() {
	if (UserA == 0) {
		UserA = 1;
	}
	else {
		UserA = 0;
	}
   }

   function add_UserB() {
	if (UserB == 0) {
		UserB = 1;
	}
	else {
		UserB = 0;
	}
   }

   function check_All() {
	if (UserAll == 0) {
		UserAll = 1;
		if (UserA == 0) {
			document.getElementById("R_checkbox_A").click();
		}
		if (UserB == 0) {
			document.getElementById("R_checkbox_B").click();
		}
	}
	else {
		UserAll = 0;
		if (UserA == 1) {
			document.getElementById("R_checkbox_A").click();
		}
		if (UserB == 1) {
			document.getElementById("R_checkbox_B").click();
		}
	}
   }
   function updateComments() {
	if (UserAll == 1) {
		//add comments from both
		document.getElementById("R_U_submittedComments").innerHTML="AllUsers' comments"; //get comment from window!!! create THIS window!!!
	}
	else if (UserA == 1 && UserB == 0) {
		//add comments from A
		document.getElementById("R_U_submittedComments").innerHTML="UserA's comments"; //get comments from window!!!
	}
	else if (UserA == 0 && UserB == 1) {
		//add comments from B
		document.getElementById("R_U_submittedComments").innerHTML="UserB's comments"; //get comments from window!!!
	}
	else if (UserA == 1 && UserB == 1) {
		//add comments from both users
		document.getElementById("R_U_submittedComments").innerHTML = "UserA's comments <br> UserB's comments"; //get comments from window!!! create THIS window!!!
	}
   }
</script>

</body>
</html>
