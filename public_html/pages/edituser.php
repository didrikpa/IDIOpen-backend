<h1> Edit user settings </h1> <?php 
#Checks if the user is logged in, redirects to the login-page if not 
if(!defined('SID')){
    session_start();
}
if($_SESSION['loggedInUser']!= null){
	#Loggs the user out if that is what he requested
		if( isset($_REQUEST) && array_key_exists("logout",$_REQUEST) && $_REQUEST["logout"]==1){
		session_destroy();
		header( 'Location: login.php' ) ;
		}
	
	if(isset($_REQUEST["editUser_form"])){
		
  
		$userPost = $_POST["username"];
		$userSess = $_SESSION['loggedInUser'];
		$studyYear = $_POST["studyYear"];
		$fullName = $_POST["fullname"];
		if(($_POST["newPass1"] != null && $_POST["newPass2"] != null) && ($_POST["newPass1"] == $_POST["newPass2"])){
			$oldpass = $_POST["currentPass"];
			$newPass1 = $_POST["newPass1"];
			$newPasse = $_POST["newPass2"];
			$queryName = "SELECT * FROM User WHERE usermail='" . $userPost . "'";
			$resultName = mysql_query($queryName, $conn);
			$rowName = mysql_fetch_array($resultName);
			$teamName = $rowName['team'];
			$queryCheck = "SELECT * FROM Team WHERE teamname='" . $teamName . "' AND password='" . md5($oldpass) . "'";
			$resultCheck = mysql_query($queryCheck, $conn);
			$rowCheck = mysql_fetch_array($resultCheck);
			if($rowCheck != null){
					$queryPass = "UPDATE Team SET password = '" . md5($newPass1) . "' WHERE teamname='" . $teamName  . "'";
					mysql_query($queryPass) or die ('Error while updating password in editUser.php');
					$subject = "Password changed on IDI Open";
					$headers = "From:no-reply-IDI_Open@idi.ntnu.no";
					$body = "Your teams password has been changed by ". $userPost . 
							" Your new password is: ". $newPass1;
					$queryMembers = "SELECT usermail from User WHERE team='" . $teamName . "'";
					$resultMembers = mysql_query($queryMembers, $conn);
					while($rowMembers =mysql_fetch_array($resultMembers)){
							mail($rowMembers['usermail'],$subject, $body,$headers);
					}
			}
		}
		$queryUp = "UPDATE User SET user = '" . $userPost . "',  studyYear = '" . $studyYear . "', fullname='" . $fullName . "' WHERE user ='" . $userSess . "'";
		$resultUp = mysql_query($queryUp,$conn);
		echo "<p>Your profile was successfully updated, <a href=\"theindex.php?page=news\">return to startpage</a></p>";
		
	}else{
	
  $dbhost = "mysql.ntnu.no";
  $dbuser = "chrijon_idiopen";
  $dbpass = "steikepanne";
   
  $conn = mysql_connect("mysql.stud.ntnu.no", $dbuser, $dbpass) or die ('Error Connection to mysql');

  $dbname = "chrijon_idiopen";	
  mysql_select_db($dbname,$conn);
  
  $user = $_SESSION['loggedInUser'];
  $query = "SELECT * FROM User WHERE usermail = '" . $user . "'";
  $result = mysql_query($query,$conn);
  $detected = 0;
  
	while($row = mysql_fetch_array($result)){
	$detected = 1;
		echo 	"<form method=\"POST\" action=\"theindex.php?page=edituser\">".
				"<table>".
					"<tr>".
						"<td>".				
							"Username : ".
						"</td>".
						"<td>".
							"<input type = \"text\" name =\"username\" value=\"" . $row['usermail'] . "\"><br>".
						"</td>".
					"</tr><tr>".
					"<td>".				
							"Teamname : ".
						"</td>".
						"<td>".
							"<i>" . $row['team'] . "</i><br>".
						"</td>".
					"</tr><tr>".
						"<td>".				
							"Full name : ".
						"</td>".
						"<td>".
							"<input type = \"text\" name =\"fullname\" value=\"" . $row['fullName'] . "\"><br>".
						"</td>".
					"</tr><tr>".
						"<td>".
							"Teams current password:".
						"</td><td>".
							"<input type =\"password\" name=\"currentPass\" value =\"\"><br>".
						"</td>".
					"</tr><tr>".
						"<td>".
							"Type a new  password:  ".
						"</td><td>".
							"<input type = \"password\" name=\"newPass1\" value=\"\"><br>".
					"</tr><tr>".
						"<td>".
							"Retype the new password:  ".
						"</td><td>".
							"<input type = \"password\" name=\"newPass2\" value=\"\">".
						"</td>".
					"</tr><tr>".
						"<td>".
							"Select year of study,<br> or professional if suitable: ".
						"</td><td>".
							"<select name=\"studyYear\">".
								"<option value=\"1\" "; if ($row['studyYear']==1){echo " selected=\"selected\" ";} echo " >1</option> ".
								"<option value=\"2\" "; if ($row['studyYear']==2){echo " selected=\"selected\" ";} echo " >2</option>".
								"<option value=\"3\" "; if ($row['studyYear']==3){echo " selected=\"selected\" ";} echo " >3</option>".
								"<option value=\"4\" "; if ($row['studyYear']==4){echo " selected=\"selected\" ";} echo " >4</option>".
								"<option value=\"5\" "; if ($row['studyYear']==5){echo " selected=\"selected\" ";} echo " >5</option>".
								"<option value=\"9\" "; if ($row['studyYear']==9){echo " selected=\"selected\" ";} echo " >Pro</option>".
							"</select>".
						"</td>".
					"</tr><tr>".
						"<td></td><td>".
							"<input type = \"Submit\" value=\"Update user\" name=\"editUser_form\"><br>".
						"</td>".
				"</table>".
				"</form>".
                                "<p><em>Note! When setting a new password, all team members will recieve the new one!</em></p>"
                                ;
	}
	if($detected==0){
		echo "<p>Sorry, no details for your user detected. For security reasons you have been logged out</p>";
		session_destroy();
		}
  }
}
?>
