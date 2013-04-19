<h1>
Edit info
</h1>

<p>

<?php
#The following code is executed if the user is logged in
if(isset($_SESSION) && array_key_exists('loggedInUser',$_SESSION) && $_SESSION['loggedInUser']!=null){
		
#This function checks if the emailaddress provided already exists in the database
function validateEmail($mail,$conn, $teamname){
    if($mail==null){
        return false;
    } else {
        $query = "SELECT usermail FROM User WHERE usermail='" . $mail . "' AND team NOT LIKE '" . $teamname . "'";
        $result = mysql_query($query, $conn); 
        $matches =  mysql_num_rows($result);
        $mailOK = preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $mail);
       return ($matches==0 && $mailOK);

    }
}

#checks if the submit button of the form has been pushed
$submitted = getRequest('submit',$conn)!=null;
$submittedPassRequest = getRequest('submitPass',$conn)!=null;
$submission_ok=true;


#Getting values from DB
#Getting the team name for the logged in user
   $queryTeam = "SELECT team from User WHERE usermail = '" . $_SESSION['loggedInUser'] . "'";
   $teamNameResult = mysql_query($queryTeam,$conn);
   $teamRow = mysql_fetch_array($teamNameResult);
   $teamName = $teamRow['team'];

   #Getting the team members corresponding the the logged in user
   $queryMembers = "SELECT * from User WHERE team='" . $teamName. "'";
   $teamMembersResult = mysql_query($queryMembers);
   $members["initated"] = "";
   $counter = 1;
   while($teamMembersRow = mysql_fetch_array($teamMembersResult)){
     #setting the variables for each of the team members from the database
     $members["member" .$counter . "name"] = $teamMembersRow['fullName'];
     $members["member" .$counter . "mail"] =$teamMembersRow['usermail'];
     $members["member" .$counter . "grade"] =$teamMembersRow['studyYear'];
     $counter++;
   }

    # Fetching the names, mails and grads for all of the teammembers from the database 
   $member1name = $members["member1name"];
   $member1mail = $members["member1mail"];
   $member1grade = $members["member1grade"];
   if(isset($members["member2mail"])){
    $member2name = $members["member2name"];
    $member2mail = $members["member2mail"];
    $member2grade = $members["member2grade"];
    $member2mail_ok = validateEmail($member2mail,$conn,$teamName);
   }
   if(isset($members["member3mail"])){
    $member3name = $members["member3name"];
    $member3mail = $members["member3mail"];
    $member3grade = $members["member3grade"];
    $member3mail_ok = validateEmail($member3mail, $conn,$teamName);
   } 
   
#Fecthing the new attributes for the team members from the form posted
#if(isset($_REQUEST['member1name'])){
#   $member1newname = $_REQUEST['member1name'];
#}

#if(isset($_REQUEST['member1mail'])){
#    $member1newmail =$_REQUEST['member1mail']; 
#    $member1newmail_ok = validateEmail($member1newmail, $conn, $teamName);
#}   
 
#if(isset($_REQUEST['member1grade'])){
#   $member1newgrade = $_REQUEST['member1grade'];
#}

if(isset($_REQUEST['member2name'])){
    $member2newname = $_REQUEST['member2name'];
}

if(isset($_REQUEST['member2mail'])){
    $member2newmail = strip_tags($_REQUEST['member2mail']); 
    $member2newmail_ok = validateEmail($member2newmail, $conn, $teamName);
}   
 
if(isset($_REQUEST['member2grade'])){
   $member2newgrade = $_REQUEST['member2grade'];
}

if(isset($_REQUEST['member3name'])){
    $member3newname = $_REQUEST['member3name'];
}

if(isset($_REQUEST['member3mail'])){
    $member3newmail = strip_tags($_REQUEST['member3mail']); 
    $member3newmail_ok = validateEmail($member3newmail, $conn, $teamName);
}   
 
if(isset($_REQUEST['member3grade'])){
   $member3newgrade = $_REQUEST['member3grade'];
}


#Redistributes the members, if members are missing
#   if(isset($member2newmail) && $member2newname==null && $member2newmail==null){
#    $member2newmail = $member3newmail;
#    $member2newname = $member3newname;
#    $member3newmail="";
#    $member3newname="";
#   }
#   if(isset($member1newmail) && $member1newname==null && $member1newmail==null) {
#     $member1newname = $member2newname;
#     $member1newmail = $member2newmail;
#     $member2newname = null;
#     $member2newmail = null;
#   }

#Checks if the Emails validates, refering to the validateEmail in register.php

#If a request for the change of the password has been made, the password is changed, and mails are sent 
  if($submittedPassRequest && isset($_REQUEST['oldpass']) && isset($_REQUEST['newpass']) && isset($_REQUEST['newpass2'])){
  $oldPass = $_REQUEST['oldpass'];
  $newPass1 = $_REQUEST['newpass'];
  $newPass2 = $_REQUEST['newpass2'];
		if($newPass1 != null && $newPass2 != null && $newPass1 == $newPass2){
			$queryName = "SELECT * FROM User WHERE usermail='" . $member1mail .  "'";
			$resultName = mysql_query($queryName, $conn);
			$rowName = mysql_fetch_array($resultName);
			$teamName = $rowName['team'];
			$queryCheck = "SELECT * FROM Team WHERE teamname='" . $teamName . "' AND password='" . md5($oldPass) . "'";
			$resultCheck = mysql_query($queryCheck, $conn);
			$rowCheck = mysql_fetch_array($resultCheck);
			if($rowCheck != null){
					$queryPass = "UPDATE Team SET password = '" . md5($newPass1) . "' WHERE teamname='" . $teamName  . "'";
					mysql_query($queryPass) or die ('Error while updating password in editUser.php');
					$subject = "Password changed on IDI Open";
					$headers = "From:idi@open.no";
					$body = "Your teams password has been changed by ". $_SESSION['loggedInUser'] . 
							" Your new password is: ". $newPass1;
					$queryMembers = "SELECT usermail from User WHERE team='" . $teamName . "'";
					$resultMembers = mysql_query($queryMembers, $conn);
					while($rowMembers =mysql_fetch_array($resultMembers)){
                                                        if($rowMembers['usermail']!=null){
							    mail($rowMembers['usermail'],$subject, $body,$headers);
					                }
                                        }
			}
		}
           
           echo "Your new password has been created. <br>A mail has been sent to your and your temamates containing it.";
           return;
           
  }
    
#If the submission are syntaxical correct, the canges are propagated to the database   
 # if not, the form is presented once again
   if($submitted  && ((( $member2newname==null && $member2newmail==null)
                      ||($member2newname!=null && $member2newmail_ok ))
                      && (($member3newname==null && $member3newmail==null)
                      ||($member3newname!=null && $member3newmail_ok  && $member3newmail!=$member2newmail)
                      )
     ))
     {
     #perform submission
     $headers = "From:no-reply-IDI_Open@idi.ntnu.no";
     
    # if($member1newmail!=null && ($member1newmail != $member1mail) || ($member1newname!=$member1name) || ( $member1newgrade != $member1grade)  ){
    #   $queryUser1 = "UPDATE User SET usermail = '" . $member1newmail . "', studyYear = " . $member1newgrade . ", fullName = '" . $member1newname . "',team='" . $teamName . "' WHERE usermail='" . $member1mail . "'";
    #   mysql_query($queryUser1,$conn);
         
         #If this user changed his username, he will have to log on again with the new username
    #     if($member1mail != $member1newmail && $_SESSION['loggedInUser']==$member1mail){
    #        echo "For security reasons you will have to log on again with your new username.<br> Your password is the same as previusly";
    #        echo "<br>You will automatically be redirected to the frontpage in 5 seconds";
    #        session_destroy();
    #        echo "<meta http-equiv='refresh' content='5;url=theindex.php?page=news'>";
    #     }
    # }
    
     
     
     if($member2newname=="" || $member2newname != null){
       if((!isset($member2mail) || $member2mail=="") && $member2newmail!="" && $member2newmail_ok){
        $queryUser2= "INSERT INTO User VALUES('" . $member2newmail . "'," . $member2newgrade . ",'" . $member2newname . "', 'user','" . $teamName ."')";
        mysql_query($queryUser2, $conn);
    } elseif($member2newmail!='' && isset($member2newmail) && $member2newmail_ok && ($member2newmail!=$member2mail || $member2newname != $member2name || $member2newgrade != $member2grade)){
     $queryUser2 =  "UPDATE User SET usermail = '" . $member2newmail . "', studyYear = " . $member2newgrade . ", fullName = '" . $member2newname . "',team='" . $teamName . "' WHERE usermail='" . $member2mail . "'";    
          mysql_query($queryUser2,$conn);
        #If this user changed his username, he will have to log on again with the new username
         if($member2newmail != null && $member2mail != $member2newmail && $_SESSION['loggedInUser']==$member2mail){
            echo "For security reasons you will have to log on again with your new username.<br> Your password is the same as previusly";
            echo "<br>You will automatically be redirected to the frontpage in 5 seconds";
            session_destroy();
            echo "<meta http-equiv='refresh' content='5;url=theindex.php?page=news'>";
        }
     }
         if(($member2newmail==null || $member2newmail=="") && (isset($member2mail) && ($member2mail!="" || $member2mail != null))){
            $queryDelUser = "DELETE FROM User WHERE usermail='" . $member2mail . "'";
            $resultin2 = mysql_query($queryDelUser,$conn) or die('Not able to delete member2');
            }
        }
   
    if($member3newname=="" || $member3newname !=null ){
     if((!isset($member3mail) || $member3mail=="") && $member3newmail!="" ){
        $queryUser3= "INSERT INTO User VALUES('" . $member3newmail . "'," . $member3newgrade . ",'" . $member3newname . "', 'user','" . $teamName ."')";
        mysql_query($queryUser3,$conn);
    } elseif($member3newmail!='' && isset($member3newmail) && ($member3newmail!=$member3mail || $member3newname != $member3name || $member3newgrade!= $member3grade) ){
     $queryUser3 =  "UPDATE User SET usermail = '" . $member3newmail . "', studyYear = " . $member3newgrade . ", fullName = '" . $member3newname . "',team='" . $teamName . "' WHERE usermail='" . $member3mail . "'";                
    mysql_query($queryUser3,$conn);
         #If this user changed his username, he will have to log on again with the new username
           if($member3mail != $member3newmail && $_SESSION['loggedInUser']==$member3mail){
            echo "For security reasons you will have to log on again with your new username. <br>Your password is the same as previusly";
            echo "<br>You will automatically be redirected to the frontpage in 5 seconds";
            session_destroy();
            echo "<meta http-equiv='refresh' content='5;url=theindex.php?page=news'>";
           }
    }
    if(($member3newmail==null || $member3newmail=="") && (isset($member3mail) && ($member3mail!="" || $member3mail != null)) ){
    $queryDelUser2 = "DELETE FROM User WHERE usermail='" . $member3mail . "'";
        mysql_query($queryDelUser2,$conn) or die('Not able to delete member3');
    }
    }                                                                                                                                                                                                                                                                                                                                                                                                                    
                                                                                                                                                                                                                                                                                                                                                                                                                   
                                                                                                                                                                                                                                                                                                                                                                                                                      
?>
<br>The settings for your team has been updated
<?
}
else
{
$teamname_message = "";
$member1_message = "";
$member2_message = "";
$member3_message = "";

if($submitted)
{
#if($member1newname==null){
#$member1_message = "must specify a team member 1";
#}
#if($member1newname!=null && $member1newmail==null){
#$member1_message = "must specify an email address";
#}

#if(!$member1newmail_ok && $member1mail!=null){
#if($member1newmail == ""){
#    $member1_message = "At least one member must exist";
#}else{
#    $member1_message = "mail address already used, or illegal";
#}

if($member2newname==null && $member2newmail!=null){
$member2_message = "missing member 2 name";
}
if($member2newname!=null && $member2newmail==null){
$member2_message = "must specify an email address";
}
if((!$member2newmail_ok && $member2newmail!=null) ||($member2newmail==$member1mail)){
echo $member2newmail_ok;
$member2_message  = "mail address already used, or illegal";
}
if($member3newname==null && $member3newmail!=null){
$member3_message = "missing member 3 name";
}
if($member3newname!=null && $member3newmail==null){
$member3_message = "must specify an email address";
}
if((!$member3newmail_ok && $member3newmail!=null) || 
(($member3newmail==$member1mail)) || 
($member3newmail!='' && ($member3newmail==$member2newmail)))
{

$member3_message = "mail address already used, or illegal";
}
}
?>



<p><em>Edit the settings for your entire team. Note that if you change your password, the entire team will recive a new one</em></p>
 <form method="post" action="theindex.php?page=edituser2">
<table>
    <tr>
        <th><em>Team name</em>:</th>
        <td>
            <input type="text" name="teamname" disabled="true" value="<?echo $teamName?>" /><?echo $teamname_message?>
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
            <input type="input" name="member1name" disabled="true" value="<?echo $member1name?>" />
        </td>
        <td>
        <input type="input" name="member1mail" disabled="true" value="<?echo $member1mail?>" />
</td>
<td>
<select name="member1grade" disabled="true">
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
<input type="input" name="member2name" value="<? if(isset($member2name))echo $member2name;  ?>" />
</td>
<td>
<input type="input" name="member2mail" value="<? if(isset($member2mail))echo $member2mail;  ?>" />
</td>
<td>
<select name="member2grade">
<option value="1" <?php if(isset($member2grade) && $member2grade==1) echo "selected=\"selected\""?>>1st grade</option>
<option value="2" <?php if(isset($member2grade) && $member2grade==2) echo "selected=\"selected\""?>>2nd grade</option>
<option value="3" <?php if(isset($member2grade) && $member2grade==3) echo "selected=\"selected\""?>>3de grade</option>
<option value="4" <?php if(isset($member2grade) && $member2grade==4) echo "selected=\"selected\""?>>4th grade</option>
<option value="5" <?php if(isset($member2grade) && $member2grade==5) echo "selected=\"selected\""?>>5th grade</option>
<option value="0" <?php if(isset($member2grade) && $member2grade==0) echo "selected=\"selected\""?>>Pro</option>
</select>
</td>
<td><?echo $member2_message?></td>
</tr>
<tr>
<td>3</td>
<td>
<input type="input" name="member3name" value="<? if(isset($member3name)){echo $member3name;} ?>" />
</td>
<td>
<input type="input" name="member3mail" value="<? if(isset($member3mail)){echo $member3mail;} ?>" />
</td>
<td>
<select name="member3grade">
<option value="1" <?php if(isset($member3grade) && $member3grade==1) echo "selected=\"selected\""?>>1st grade</option>
<option value="2" <?php if(isset($member3grade) && $member3grade==2) echo "selected=\"selected\""?>>2nd grade</option>
<option value="3" <?php if(isset($member3grade) && $member3grade==3) echo "selected=\"selected\""?>>3de grade</option>
<option value="4" <?php if(isset($member3grade) && $member3grade==4) echo "selected=\"selected\""?>>4th grade</option>
<option value="5" <?php if(isset($member3grade) && $member3grade==5) echo "selected=\"selected\""?>>5th grade</option>
<option value="0" <?php if(isset($member3grade) && $member3grade==0) echo "selected=\"selected\""?>>Pro</option>
</select>
</td>
<td><?echo $member3_message?></td>
</tr>
<tr>
<td colspan="5">
&nbsp;&nbsp;&nbsp;<input type="submit" name="submit" value="submit" />
</td>
</tr>
</table>
</form>

<form method="post" action="theindex.php?page=edituser2">
<table>
<tr>
    <th>
       <em> Team password</em>
    </th>
    <td>
    </td>
</tr>
</table>
<table>
<tr>
</tr>
<tr>
   <td colspan="5">
    If you enter your old password, and the new one two times,<br>
    the entires teams password will be changed.<br>
    A mail will be sent to you and your teammates containing the new password
</td>
</tr>
<tr>
    <td>
    </td>
    <th>
        Old pass.
    </th>
    <th>
        New Pass.
    </th>
    <th>
        New Pass.
    </th>
    <td>
    </td>
    <td>
    </td>
</tr>
<tr>
<td>
</td>
    <td>
        <input type="password" name="oldpass" value="">
    </td>
    <td>
        <input type="password" name="newpass" value="">
    </td>
    <td>
        <input type="password" name="newpass2" value="">
    </td>
    <td>
    </td>
</tr>
<tr>
<td colspan="5">
&nbsp;
</td>
</tr>
<tr>
<td colspan="5">
<input type="Submit" value="Submit" name="submitPass">
</td>
</tr>
</table>


</form>
<?php 
    }
} else {
session_destroy();
echo "Please log in to continue";

}
?>
