<h1>
Register
</h1>
<p>
<b>Registration is closed.</b>
</p>

<p>
<?php

$registration_active = false;
function validateEmail($mail,$conn){
	if($mail==null)return false;
	$query = "SELECT usermail FROM User WHERE usermail='" . $mail . "'";
	$result = mysql_query($query, $conn);
	$matches = (mysql_num_rows($result)==0);
       # $mailOK = preg_match( "/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email);
       # return $matches && $mailOK;
	//echo "blablabla" . $matches;
       return $matches; 
        }

function validateTeamName($teamName,$conn){
        if($teamName==null) return false;
        //$teamNameOK = preg_match("/[^a-z]/i",$teamName)
        $teamNameOK = true;
        $backpos = strpos($teamName,'\\');
        //echo("backpos: " . $backpos);
        //echo("backpos: " . ($backpos === false)); #is_bool($backpos) && !$backpos));
        $teamNameOK = ($backpos === false); #is_bool($backpos) && !$backpos);
        if($teamNameOK){
            return true;
        }else{
            return false;
}


}

#check if something is submitted and if everything is ok.

$submitted = getRequest('submit',$conn)!=null;

#if a submission has been made.

$submission_ok = true;
$teamname_ok = true;

$teamname = getRequest("teamname",$conn);
$member1name = getRequest("member1name",$conn);
$member1mail = getRequest("member1mail",$conn);
$member1grade = getRequest("member1grade",$conn);
$member2name = getRequest("member2name",$conn);
$member2mail = getRequest("member2mail",$conn);
$member2grade = getRequest("member2grade",$conn);
$member3name = getRequest("member3name",$conn);
$member3mail = getRequest("member3mail",$conn);
$member3grade = getRequest("member3grade",$conn);

if(!validateTeamName($teamname,$conn))
{
  $teamname_ok = false;
}

if($member2name==null && $member2mail==null)
{
	$member2name = $member3name;
	$member2mail = $member3mail;
        $member3name = null;
	$member3mail = null;
}
if($member1name==null && $member1mail==null)
{
	$member1name = $member2name;
	$member1mail = $member2mail;
	$member2name = null;
	$member2mail = null;
}

if($teamname==null)
{
	$submission_ok = false;
}

$query = "SELECT * FROM Team WHERE teamname='" . $teamname . "'";
$result = mysql_query($query, $conn);
if($submission_ok && mysql_num_rows($result)>0 || !preg_match('/[a-z]/i',$teamname) )
{
	//echo $submission_ok . " " . mysql_num_rows($result) . " " . preg_match('/[a-z]/i', $teamname) . " " . $teamname . "<br />";
	$submission_ok = false;
	$teamname_ok = false;
}

$member1mail_ok = validateEmail($member1mail,$conn);
$member2mail_ok = validateEmail($member2mail,$conn);
$member3mail_ok = validateEmail($member3mail,$conn);

/*echo "<br />Tn: " . ($teamname_ok === true);
echo "<br/>Sub: " . $submission_ok;
*/

if($registration_active && $teamname_ok && $submission_ok
	&&
	($member1name!=null && $member1mail_ok)
	&&
	(
		($member2name==null && $member2mail==null)
		|| 
		($member2name!=null && $member2mail_ok && $member2mail!=$member1mail)
	)
	&&
	(
		($member3name==null && $member3mail==null)
		|| 
		($member3name!=null && $member3mail_ok && $member3mail!=$member1mail && $member3mail!=$member2mail)
	)
)
{
	#perform submission
	$createdPass = substr((md5(rand())), 0, 6);
    $query = "INSERT INTO Team VALUES('" . $teamname . "', '" . md5($createdPass) . "', '". $member1mail . "')";
	$headers = "From:no-reply-IDI_Open@idi.ntnu.no";
	$teamType = "student";
        mysql_query($query, $conn);
	
	if($member1name!=null){
        if($member1grade >5){
            $teamType="pro"; }

		$queryUser1 = "INSERT INTO User VALUES('" . $member1mail . "'," . $member1grade . ",'" . $member1name . "','user', '" . $teamname . "')";
                mysql_query($queryUser1,$conn);
		$body = "Welcome to IDI Open. Your team, " . $teamname . " has now been created. All of the team members are now able to log on to IDIOpen by using the following password: ".
				$createdPass . 
				" Your personal username is: " . $member1mail ;
	        mysql_query($queryUser1,$conn);			
		mail($member1mail,'Welcome to IDI Open', $body, $headers);
	}
	if($member2name!=null){
        if($member2grade >5){$teamType="pro";}
		$queryUser2 = "INSERT INTO User VALUES('" . $member2mail . "'," . $member2grade . ",'" . $member2name . "','user', '" . $teamname . "')";
		mysql_query($queryUser2,$conn);
			$body = "Welcome to IDI Open. Your team, " . $teamname . " has now been created. All of the team members are now able to log on to IDIOpen by using the following password: ".
				$createdPass . 
				" Your personal username is: " . $member2mail ;
	        mysql_query($queryUser2,$conn);
            mail($member2mail,'Welcome to IDI Open', $body, $headers);
	}
	if($member3name!=null){
        if($member3grade>5){$teamType="pro";}
		$queryUser3 = "INSERT INTO User VALUES('" . $member3mail . "'," . $member3grade . ",'" . $member3name . "','user', '" . $teamname . "')";		
		mysql_query($queryUser3,$conn);
			$body = "Welcome to IDI Open. Your team, " . $teamname . " has now been created. All of the team members are now able to log on to idiOpen by using the following password: ".
				$createdPass . 
				" Your personal username is: " . $member3mail ;
		mysql_query($queryUser3,$conn);	
                mail($member3mail,'Welcome to IDI Open', $body, $headers);
	}
	//mail("flyrev@gmail.com",'New IDI Open team',$body,$headers);
	//mail("ondkloss@gmail.com",'New IDI Open team',$body,$headers);
	//mail("stian.forberg@gmail.com",'New IDI Open team', $body,$headers);
	mail("x10an14@gmail.com", 'New IDI Open 2013 team', $body, $headers);



        #Submits the team to the Playground register page
       $GP2['action'] = 'adduser';
       $GP2['user_login'] = $member1mail;
       $GP2['user_name'] = $teamname;
       $GP2['user_loctype'] = 'onsite';
       $GP2['user_teamtype'] = $teamType;
       $GP2['user_password'] = 'whatever';
       include 'old/user2.php';


?>
	Registration successful
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
		if($teamname==null){
			$teamname_message = "must specify a team name";
		}
		if($teamname_ok==false){
			$teamname_message = "illegal team name";
		}
		if($member1name==null){
			$member1_message = "must specify a team member 1";
		}
		if($member1name!=null && $member1mail==null){
			$member1_message = "must specify an email address";
		}
		if(!$member1mail_ok && $member1mail!=null){
			$member1_message = "mail address already used, or illegal";
		}
		if($member2name==null && $member2mail!=null){
			$member2_message = "missing member 2 name";
		}
		if($member2name!=null && $member2mail==null){
			$member2_message = "must specify an email address";
		}
		if((!$member2mail_ok && $member2mail!=null) || $member2mail==$member1mail){
			$member2_message = "mail address already used, or illegal";
		}
		if($member3name==null && $member3mail!=null){
			$member3_message = "missing member 3 name";
		}
		if($member3name!=null && $member3mail==null){
			$member3_message = "must specify an email address";
		}
		if((!$member3mail_ok && $member3mail!=null) || $member3mail==$member1mail || $member3mail==$member2mail){
			$member3_message = "mail address already used, or illegal";
		}
	}

?>

<p><em>Register with a team name and at least one team member. Note that by registering for this site, you also sign up for the contest.</em></p>

<p><em>If you are going to participate online (not being present at NTNU), please use brackets and specify what city you are participating from in your team name (example: [&aring;rhus] Team name).</em></p>

<form method="post" action="theindex.php?page=register">

<table>

<tr>
 <th><em>Team name</em>:</th>
 <td>
  <input type="text" name="teamname" maxlength="250" value="<?echo $teamname?>" /><?echo $teamname_message?>
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
   <option value="1">1st grade</option>
   <option value="2">2nd grade</option>
   <option value="3">3de grade</option>
   <option value="4">4th grade</option>
   <option value="5">5th grade</option>
   <option value="0" selected="selected">Pro</option>
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
   <option value="1">1st grade</option>
   <option value="2">2nd grade</option>
   <option value="3">3de grade</option>
   <option value="4">4th grade</option>
   <option value="5">5th grade</option>
   <option value="0" selected="selected">Pro</option>
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
   <option value="1">1st grade</option>
   <option value="2">2nd grade</option>
   <option value="3">3de grade</option>
   <option value="4">4th grade</option>
   <option value="5">5th grade</option>
   <option value="0" selected="selected">Pro</option>
  </select>
 </td>
 <td><?echo $member3_message?></td>
</tr>
</table>
 
<input type="submit" name="submit" value="submit"/>
</form>

<?
}

?>

</p>
