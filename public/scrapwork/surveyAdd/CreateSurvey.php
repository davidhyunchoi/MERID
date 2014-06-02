<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<link rel="stylesheet" type="text/css" href="Stylesheet.css">
</head>

<?php include 'header.php'; ?>

<body>
<div id="CS_titleOfPage" style="text-align:center">
   <b>Create Survey</b>
<div id="CS_user" style="text-align:right">Researcher
</div>
</div>

<div id="CS_body">
<!--video+5 buttons-->
<div id="CS_videoButtons"> 
<!--YouTube player-->
<div id="player">
</div>

<!--five video buttons-->
   <!--GRAEME, change the following videoId, startTime and endTime for different videos-->
   <div id="CS_fiveVideos">
      <input type="button" id="CS_video1" value="left"  
      onclick="newVideo('TCOCowngD5s', '312','374');return false;">
      <input type="button" id="CS_video2" value="right" 
      onclick="newVideo('TCOCowngD5s', '132','244');return false;">
      <input type="button" id="CS_video3" value="back" 
      onclick="newVideo('TCOCowngD5s', '219','254');return false;">
      <input type="button" id="CS_video4" value="front" 
      onclick="newVideo('TCOCowngD5s', '254','344');return false;">
      <input type="button" id="CS_video5" value="composite" 
      onclick="newVideo('TCOCowngD5s', '212','301');return false;">
   </div>
</div>
</div>

<!--prompt & title-->
<div id="prompt_title_div">
<div id="CS_Title_div">  
   <form name="f">
Please add the survey's <b>title</b> into the following space.<br>
   <textarea type="comment" id="CS_title"></textarea><br><br>
</div>
<div id="CS_Prompt_div">  
   Please add the survey's <b>prompt</b> into the following space.<br>
   <textarea type="comment" id="CS_prompt"></textarea><br><br>
</div></form>

<!--submit survey button-->
<button id="CS_submit_all" value="submit all" onclick="submitAll();return false;">Submit survey!<br><br>
</div>

   <div id="CS_Checkbox_div"><br>Check the users you would like to send the survey to: <br>
   <input type="checkbox" id="CS_checkbox_A" onclick="add_UserA()"/>User A <br>
   <input type="checkbox" id="CS_checkbox_B" onclick="add_UserB()" />User B <br>
   <input type="checkbox" id="CS_checkbox_All" onclick="check_All()"/>All above Users
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
   var CS_UserA = 0;
   var CS_UserB = 0;
   var CS_UserAll = 0;

   function onYouTubePlayerAPIReady() {
      player = new YT.Player('player', {
      });
   }

   function submitAll() {
	//write survey title to window
	var titleWindow = window.open("","CS_Title","width=1,height=1");
        var myTitle = document.f.CS_title.value;
	titleWindow.document.write(myTitle);
	//write propmt to window
	var promptWindow = window.open("","CS_Prompt","width=1,height=1");
        var myPrompt = document.f.CS_prompt.value;
	promptWindow.document.write(myPrompt);
	//write survey users to window
	var usersWindow = window.open("","CS_Users","width=1,height=1");
	usersWindow.document.write(CS_UserA);
	usersWindow.document.write(CS_UserB);
	usersWindow.document.write(CS_UserAll);
   }

   function newVideo(videoId, startSeconds, endSeconds) {
	//load new video
	player.loadVideoById({'videoId': videoId, 'startSeconds': startSeconds, 'endSeconds': endSeconds});
   }

    function add_UserA() {
	if (CS_UserA == 0) {
		CS_UserA = 1;
	}
	else {
		CS_UserA = 0;
	}
   }

   function add_UserB() {
	if (CS_UserB == 0) {
		CS_UserB = 1;
	}
	else {
		CS_UserB = 0;
	}
   }

   function check_All() {
	if (CS_UserAll == 0) {
		CS_UserAll = 1;
		if (CS_UserA == 0) {
			document.getElementById("CS_checkbox_A").click();
		}
		if (CS_UserB == 0) {
			document.getElementById("CS_checkbox_B").click();
		}
	}
	else {
		CS_UserAll = 0;
		if (CS_UserA == 1) {
			document.getElementById("CS_checkbox_A").click();
		}
		if (CS_UserB == 1) {
			document.getElementById("CS_checkbox_B").click();
		}
	}
   }
</script>

</body>
</html>
