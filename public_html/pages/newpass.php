<h1> Forgotten password </h1>

		<?php
		#If the username attribute is present in the CGI, this code is executed, genreating a new password and mailing it
		if(isset($_REQUEST) && array_key_exists("username",$_REQUEST) &&  $_REQUEST["username"] != null){
		$user = $_REQUEST["username"];
		$user = strip_tags($user);
		$user = mysql_real_escape_string($user,$conn);
		$query = "SELECT usermail FROM User WHERE usermail = '" . $user . "'";
		$result = mysql_query($query,$conn);
		$detected = 0;
		while($row = mysql_fetch_array($result)){
			if($_REQUEST["username"] == $row['usermail']){
				#Indicates a user with the given username has been detected
				$detected =1;
				#Retrives the teamname
				$queryTName = "SELECT team from User WHERE usermail='" . $row['usermail'] . "'";
				$resultTName = mysql_query($queryTName, $conn) or die ('Error fetching teamname');
				$rowTName = mysql_fetch_array($resultTName);
				$teamName = $rowTName['team'];
				
				#Prepares the retriving of  fellow team-members
				$queryTeamMembers = "Select * from User WHERE team = '" . $teamName . "'";
				$resultTeamMembers = mysql_query($queryTeamMembers, $conn) or die ('Error fetching team members');
				#Generates a new password
				$newpasswd = substr(md5(rand()), 0, 6);
				#Submits the new password to the database
				$queryPass = "UPDATE Team SET password = '" . md5($newpasswd) . "' WHERE teamname='" . $teamName  . "'";
				mysql_query($queryPass, $conn) or die ('Error while updating password in editUser.php');
				#Genreates and sends the email to the user and its teammebers
					$subject = "Password changed on IDI Open";
					$headers = "From:no-reply-IDI_Open@idi.ntnu.no";
					$body = "Your teams password has been changed by ". $_REQUEST["username"] . 
							" Your new password is: " . $newpasswd;
					while($rowTeamMembers = mysql_fetch_array($resultTeamMembers)){
							mail($rowTeamMembers['usermail'],$subject,$body,$headers);
					}
					echo "<p>New password sent to you and your team-members</p>";
			}
		}
		if($detected!=1){
			echo "<p>Sorry, the username you entered could not be found</p>";
		}
		}else{

		#Print the form required for entering a username, The page is reloaded with the username, an the code in the top is executed.
		?>
		<p> Enter your username to get your password sent to your registered email </p>
		<form method="POST" action="theindex.php?page=newpass">
			Username:  <br />
			<input type="text" name="username" value="<?php if(isset($_COOKIE["userIdiOpen"]))echo $_COOKIE["userIdiOpen"]; ?>" ><br />
			<input type="Submit" value="Email me"><br />
		</form>
		<?php }
		
		
		?>
