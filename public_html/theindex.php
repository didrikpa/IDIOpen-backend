<?php

	# used to store error messages.
	$errors["instanceof"] = "";
	
	#db connection
	/*Gammel database, ikke i bruk lenger, bruk den andre istedenfor (den er IDI Open "sin", 
	og tilhører ikke en enkelt student).
	#$dbhost = "mysql.stud.ntnu.no";
	#$dbuser = "forberg_open";
	#$dbpass = "paddemedhatt";	
	#$dbname = "forberg_open12"; */
	$dbhost = "mysql.idi.ntnu.no";
	$dbuser = "openidi_adm";
	$dbpass = "ngi5Gooi";
	$dbname = "p_openidi";
	$conn = mysql_connect($dbhost, $dbuser,$dbpass) or die ('Error Connection to mysql');
	mysql_select_db($dbname,$conn);
	
	# function for getting a db-safe request value
	function getRequest($request,$conn){
                if(!isset($_REQUEST) || !array_key_exists($request,$_REQUEST)){
                        return null;
                }
		$req = $_REQUEST[$request];
		$req = strip_tags($req);
		$req = mysql_real_escape_string($req,$conn);
		if($req==null || $req==""){
			$req=null;
		}
		return $req;
	}

	function isValidPage($page){
		$dir = opendir("./pages");
		while ($file = readdir($dir)) { 
			if($file == $page . ".php"){
				return true;
			}
		}
		return false;
	}

	# session management.
	session_start();
	if(isset($_SESSION) && array_key_exists('loggedInUser',$_SESSION) && $_SESSION['loggedInUser'] != null){
		if(isset($_REQUEST) && array_key_exists("logout",$_REQUEST) && $_REQUEST["logout"]==1){
			session_destroy();
			header('Location: theindex.php?page=news');
		}
	 }else if(isset($_REQUEST) && array_key_exists("username",$_REQUEST) && isset($_REQUEST["login_form"])){
		if($_REQUEST["username"] != null){
			$user = $_REQUEST["username"];
			$user = strip_tags($user);
			$user = mysql_real_escape_string($user,$conn);
			$query = "SELECT * FROM User WHERE usermail = '" . $user . "' AND accountType='user'";
			$result = mysql_query($query,$conn);
			$detected = 0;
			while($row = mysql_fetch_array($result)){
				$team = $row['team'];
				if($team != null){
					$detected = 1;
					$queryTeam = "Select * from Team WHERE teamname='" . $team . "'";
					$result2 = mysql_query($queryTeam,$conn);
				}
				while($detected != 0 && $row2 = mysql_fetch_array($result2)){
							if($row2['password'] == md5($_REQUEST["password"])){
								#logs on to the idiOpen system
                                                                $detected = 1;
								$inTwoMonths = 60 * 60 * 24 * 60 + time();
								setCookie('userIdiOpen', $row['usermail'], $inTwoMonths);
								$_SESSION['loggedInUser'] =$row['usermail'];
								$_SESSION['teamName'] = $team;
                                                                # Logs on to Playground using the teams username on that site
                                                                $playGroundLogOn = $row2['playgroundUser'];
                                                                $playGroundPass = 'whatever';
                                                                include "../include_html/defs.php";
                                                        
                                                
                                                        }else{
							$errors["wrong username"]="";
							}
				}
			}
			if($detected != 1){
				$errors["wrong username"] = "";
			}
		}
	}

	#pages to load
	$sidebar = "sidebar.php";
	if(isset($_REQUEST["page"])){
		if(isValidPage($_REQUEST["page"])){
			$content = "./pages/" . $_REQUEST["page"] . ".php";
		} else {
			$content = "./pages/404.php";
		}
	}else{
		$content = "./pages/404.php";
	}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="style.css">
                <title>IDI Open 2013 </title>
	</head>
	<body>
		<!--<? echo strtotime("20 april 2013 11:00:00"); ?>-->
		<div id="mainbar">
			<div id="wrapper">
				<?php include $content ?>
			</div>
			<p class="footer">
				&copy;2013 IDI Open Organizing Committee
			</p>
		</div>
		<!--
		
		-->
		<div id="logo">&nbsp;</div>
		<div id="sidebar">
			<?php include $sidebar ?>
		</div>
	</body>
</html>
