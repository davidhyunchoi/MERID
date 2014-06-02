<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<link rel="stylesheet" type="text/css" href="Stylesheet.css">
</head>
<body>

<?php include 'header.php'; ?>
<div id="A_deleteInfo"><br>
Click the checkboxes to mark the pages to be deleted: <br>
<div id="A_Checkbox_div">
   <input type="checkbox" id="A_checkbox_CS" onclick="delete_CreateSurvey();"/>Create Survey (title and prompt)<br>
   <input type="checkbox" id="A_checkbox_A" onclick="delete_UserA();"/>User A's Comments<br>
   <input type="checkbox" id="A_checkbox_B" onclick="delete_UserB();"/>User B's Comments<br>
   <input type="checkbox" id="A_checkbox_R" onclick="delete_Researcher();"/>Researcher's Comments <br><br></div>
To delete all of the saved information, click the checkbox:
<div id="A_Checkbox_all">
   <input type="checkbox" id="A_checkbox_All" onclick="delete_All()"/>All Comments and Survey information<br><br>
   </div> 
<div id="A_delete">
      <input type="button" id="A_delete_Info" value="Delete!" onclick="deleteInfo();">
</div>	
<span id="delete_comments">
</span>
</div>
<?php include 'footer.php'; ?>

<script>
   var CreateSurvey = 0;
   var UserA = 0;
   var UserB = 0;
   var Researcher = 0;
   var UserAll = 0;

   function delete_CreateSurvey() {
	if (CreateSurvey == 0) {
		CreateSurvey = 1;
	}
	else {
		CreateSurvey = 0;
	}
   }

   function delete_UserA() {
	if (UserA == 0) {
		UserA = 1;
	}
	else {
		UserA = 0;
	}
   }

   function delete_UserB() {
	if (UserB == 0) {
		UserB = 1;
	}
	else {
		UserB = 0;
	}
   }
   function delete_Researcher() {
	if (Researcher == 0) {
		Researcher = 1;
	}
	else {
		Researcher = 0;
	}
   }
   function delete_All() {
	if (UserAll == 0) {
		UserAll = 1;
		if (CreateSurvey == 0) {
			document.getElementById("A_checkbox_CS").click();
		}
		if (UserA == 0) {
			document.getElementById("A_checkbox_A").click();
		}
		if (UserB == 0) {
			document.getElementById("A_checkbox_B").click();
		}
		if (Researcher == 0) {
			document.getElementById("A_checkbox_R").click();
		}
	}
	else {
		UserAll = 0;
		if (CreateSurvey == 1) {
			document.getElementById("A_checkbox_CS").click();
		}
		if (UserA == 1) {
			document.getElementById("A_checkbox_A").click();
		}
		if (UserB == 1) {
			document.getElementById("A_checkbox_B").click();
		}
		if (Researcher== 1) {
			document.getElementById("A_checkbox_R").click();
		}
	}
   }
   function deleteInfo() {
	var str = "";
	if (CreateSurvey == 1) { 
		//delete title & prompt pages
		str =  "<br>" + "Deleted the title and prompt of the 'Create Survey' page.";
	}
	if (UserA == 1) {
		//delete UserA comments
		str = str +  "<br>" + "Deleted UserA's comments.";
	}
	if (UserB == 1) {
		//delete UserB comments	
		str = str +  "<br>" + "Deleted UserB's comments.";
	}
	if (Researcher == 1) {
		//delete Researcher comments
		str = str + "<br>" + "Deleted the Researcher's comments.";
	}
	if (UserAll == 1) {
		//delete all info in windows
		str =  "<br>" + "Deleted the information from all of the pages.";
	}
	document.getElementById("delete_comments").innerHTML = str;
   }
</script>
</body>
</html>
