<html>
<head>
<link rel="stylesheet" type="text/css" href="splash.css">
<script type="text/javascript" src="loginForm.js"></script>
</head>

<body>

<?php include 'header.php'; ?>

<div id="bodyWrapper">

<div id="aboutWrapper">
<h2>About MERID</h2>
<p>Media Enabled Research Interface and Database<br />
<a class="addedLinks" href="/moreaboutMERID.html">More about MERID</a>
</p>
</div>

<div id="logInWrapper">
<h2>Log In</h2>
<form method="post" action="index.php">
	<p>Username: <input type="text" name= "username"/></p>
	<p>Password: <input type="password" name= "password"/></p>
	<input type="submit" value="Log In" />
</form>

<div class="addedLinks">
Interested in doing research? Request a <a href="/signUp.php">researcher's account</a>
</div>

</div>


<!--<div id="signUpWrapper">
<h2>Sign Up</h2>
<p>* Required</p>
<form method="post" action="newuser.php">
	<div id="signUpFormWrapper">
	
	<p>Username: <input type="text" name= "newUserName" id="newUserName" onKeyUp="validUsername(this.value)"/> <span id="UNmessage">*</span></p>
	<p>Password: <input type="password" name= "newPw1" id="newPw1" onChange="validPassword(this.value)"/> <span id="PWmessage">*</span></p>
	<p>Confirm Password: <input type="password" name= "newPw2" id="newPw2" onKeyUp="checkPass(); return false;"/> <span id="confirmMessage">*</span></p>
	<input type="submit" value="Submit" />
	
	</div>
</form>
</div>-->

</div>

<?php include 'footer.php'; ?>

</body>
</html>