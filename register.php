<?php
include('header.php');
?>
	
	<div class="wrapper">
	<div class="content">
	<div class="register-form"  >
	<form action = "scripts/signup-script.php" method="post">
				<h2> Registration </h2><br>
				<input class="bigger-custom-input" type="text" name="user" placeholder = "User-Name"><br>
				<input class="bigger-custom-input" type="text" name="email" placeholder = "Email adress"><br>
				<input class="bigger-custom-input" type="password" name="pwd" placeholder = "Password"><br>
				<input class="bigger-custom-input" type="password" name="pwdrepeat" placeholder = "Repeat Password"><br>
				<br>
				
				<label for="type"><span class="white_text">Choose the type of your account: </span> </label>
				<br><br>
				<select name="type" id="account_type">
					<option value="">Select an option</option>
					<option value="manager">Project Manager</option>
					<option value="developer">Developer</option>

				</select>
				<div>
				<button class="sign-up_button" type="submit" name="submit">Sign-Up</button>
				</div>
				
	</form>
	</div>
	<?php 
	if(isset($_GET['error']))
	{
		$errorList=array();
		$errorList['emptyField'] = "<p class='error'>No fields can be empty.</p>";
		$errorList['invalidUsername'] = "<p class='error'>The username can contain letters and digitis and it must be atleast of length 4.</p>";
		$errorList['usernameExists'] = "<p class='error'>Username already exists.</p>";
		$errorList['invalidEmail'] = "<p class='error'>The email entered is invalid.</p>";
		$errorList['invalidPassword'] = "<p class='error'>The password must be at least 8 symbols and the two passwords fileds must match.</p>";
		$errorList['registerSuccess'] = "<p class='sign-upSuccess'>Account creation was succesfull, you may now log-in</p>";
		
		if(isset($errorList[$_GET['error']]))
		{
			echo $errorList[$_GET['error']];
		}
	}
	?>
	</div>
	
	</div>
	</div>
	</body>
	
<html>