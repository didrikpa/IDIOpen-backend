<h1>
Edit info
</h1>
<?php
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
					$headers = "From:idi@open.no";
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
               } 
}

?>

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <table>

<form method="post" action="theindex.php?page=register">

<table>

  <tr>
    <th><em>Team name</em>:</th>
    <td>
     <input type="text" name="teamname" value="<?echo $teamname?>" /><?echo $teamname_message?>
     </td>
</tr>
</table>

<table>
  <tr>
    <th>&nbsp;</th>
    <th>Member names</th>
    <th>Email</th>
    <th>Grade</th>
    <th>&nbsp;</th>
  </tr>
  <tr>
    <td>1</td>
    <td>
      <input type="input" name="member1name" value="<?echo $member1name?>" />
    </td>
    <td>
      <input type="input" name="member1mail" value="<?echo $member1mail?>" />
    </td>
    <td>
      <select name="member1grade">
        <option value="1" <?php if($member1grade==1) echo "selected=\"selected\""?>>1st grade</option>
        <option value="2" <?php if($member1grade==2) echo "selected=\"selected\""?>>2nd grade</option>
        <option value="3" <?php if($member1grade==3) echo "selected=\"selected\""?>>3de grade</option>
        <option value="4" <?php if($member1grade==4) echo "selected=\"selected\""?>>4th grade</option>
        <option value="5" <?php if($member1grade==5) echo "selected=\"selected\""?>>5th grade</option>
        <option value="0" <?php if($member1grade==0) echo "selected=\"selected\""?>>Pro</option>
      </select>
    </td>
    <td><?echo $member1_message?></td>
  </tr>
  <tr>
    <td>2</td>
    <td>
      <input type="input" name="member2name" value="<?echo $member2name?>" />
    </td>
    <td>
      <input type="input" name="member2mail" value="<?echo $member2mail?>" />
    </td>
    <td>
      <select name="member2grade">
         <option value="1" <?php if($member2grade==1) echo "selected=\"selected\""?>>1st grade</option>
         <option value="2" <?php if($member2grade==2) echo "selected=\"selected\""?>>2nd grade</option>
         <option value="3" <?php if($member2grade==3) echo "selected=\"selected\""?>>3de grade</option>
         <option value="4" <?php if($member2grade==4) echo "selected=\"selected\""?>>4th grade</option>
         <option value="5" <?php if($member2grade==5) echo "selected=\"selected\""?>>5th grade</option>
         <option value="0" <?php if($member2grade==0) echo "selected=\"selected\""?>>Pro</option>
       </select>
     </td>
     <td><?echo $member2_message?></td>
   </tr>
   <tr>
     <td>3</td>
     <td>
       <input type="input" name="member3name" value="<?echo $member3name?>" />
     </td>
     <td>
       <input type="input" name="member3mail" value="<?echo $member3mail?>" />
     </td>
     <td>
       <select name="member3grade">
         <option value="1" <?php if($member3grade==1) echo "selected=\"selected\""?>>1st grade</option>
         <option value="2" <?php if($member3grade==2) echo "selected=\"selected\""?>>2nd grade</option>
         <option value="3" <?php if($member3grade==3) echo "selected=\"selected\""?>>3de grade</option>
         <option value="4" <?php if($member3grade==4) echo "selected=\"selected\""?>>4th grade</option>
         <option value="5" <?php if($member3grade==5) echo "selected=\"selected\""?>>5th grade</option>
         <option value="0" <?php if($member3grade==0) echo "selected=\"selected\""?>>Pro</option>
       </select>
     </td>
     <td><?echo $member3_message?></td>
   </tr>
</table>
                                                                                         
<input type="submit" name="submit" value="submit" />
</form>
