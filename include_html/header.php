<?
	header('Content-Type: text/html; charset=UTF-8');
	echo '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>PP: '.$title.'</title>
		<style type="text/css">
			p, td, th, h1, h2, h3, span {
				font-family: sans-serif;
			}
			a, a.active, a.visited {
				text-decoration: none;
				//color: blue;
				//background-color: inherit;
			}
			body {
				color: black;
				background-color: #ffffff;
			}
			td, th {
				vertical-align: top;
				text-align: left;
				padding-right: 10px;
				margin: 0px;
			}
			tr {
				margin: 0px;
			}
			.debug {
				color: orange;
				background-color: inherit;
			}
			p {
				text-align: justify;
			}
			h3 {
				margin-top: 20px;
				margin-bottom: 10px;
			}
			.page {
				color: black;
				background-color: #ffffff;
				padding: 20px;
			}
			.pcont {
				width: 900px;
			}
			.menu {
				vertical-align: middle;
				text-align: center;
				color: inherit;
				background-color: #cccccc;
			}
			.menublank {
				vertical-align: middle;
				text-align: center;
			}
			.menulink {
				font-weight: bold;
				color: #5a1000;
				background-color: inherit;
			}
			.menudisabled {
				font-weight: bold;
				color: #999999;
				background-color: inherit;
			}
		</style>
	</head>
	<body>
		<div class="page">';
	echo '
		<table border="0px" class="pcont">
			<tr>
				<td style="width: 33%;"></td>
				<td style="width: 33%;"></td>
				<td style="width: 33%;"></td>
			</tr>
			<tr>';
	if (isset($_SESSION["$INST.user_id"])) {
		echo '
				<td class="menu">
					Logged in as <a class="menulink" 
						href="user.php?action=edituser&amp;user_id='
						.$_SESSION["$INST.user_id"].'">'
						.$_SESSION["$INST.user_login"].'</a>
				</td>
				<td class="menu">
					<form action="index.php" method="post">
						<input type="hidden" name="action" value="logout" />
						<input type="submit" value="Logout" />
					</form>
				</td>
				<td class="menu">';
					/*<a class="menulink" 
						href="user.php?action=edituser&amp;user_id='
						.$_SESSION["$INST.user_id"].'">Edit user</a>*/
				echo '</td>';
	}
	else {
		echo '
				<td class="menu" colspan="2">
					<form action="" method="post">
						<p style="text-align: center">
							<input type="hidden" name="action" value="login" />
							Login: <input type="text" name="login_user" size="10" />
							Pass: <input type="password" name="login_pass" size="10" />
							<input type="submit" name="login" value="Login" />';
		if (isset($passwordmailjustsent)) {
			echo '
				New password sent';
		}
		else {
			echo '
							<input type="submit" name="mailpass" value="E-mail new password" />';
		}
		echo '
						</p>
					</form>
				</td>
				<td class="menu">';
		echo '			<span class="menudisabled">Register</span>
				</td>';
		//echo '
		//	<a class="menulink"
		//			href="user.php?action=newuser">Register</a></td>';
	}
	if ($SINGLE_EVENT) {
		echo '
		</tr>
			<tr>
				<td class="menu">
					<img alt="FAST - Event sponsor" src="gfx/fast2.gif"/>
				</td>
				<td class="menu">
					<img align="center" alt="IDI Open 2007" src="gfx/open07.png"/>
				</td>
				<td class="menu">
					<img alt="Medallia - Problem sponsor" src="gfx/medallia_grey.gif"/>
				</td>
			</tr>';
	} else {
		echo '
		</tr>
			<tr>
				<td colspan="3" class="menu">
					<span style="font-size: 40px">'.$INST_NAME.' - Admin</span>
				</td>
			</tr>';
	}
	echo '
			<tr>
				<td class="menu">
				<a class="menulink" href="index.php">News</a>';
	if ($_SESSION["$INST.user_isadmin"]) {
		echo '
				(<a class="menulink" href="index.php?action=editnews">edit</a>)';
	}
	echo '
				</td>
				<td class="menu"><a class="menulink" href="user.php">List teams</a></td>';
	if (true) {
		if (isset($_SESSION["$INST.event_id"]) && 
				($_SESSION["$INST.user_isadmin"] || 
					$_SESSION["$INST.event_start"] <= time())) {
			$clars = dbquery1("SELECT count(id)
							   FROM clarifications
							   WHERE event_id={$_SESSION["$INST.event_id"]}");
			echo '
				<td class="menu">
					<a class="menulink" 
						href="clarifications.php?event_id='.$_SESSION["$INST.event_id"].'">Clarifications</a> 
						('.$clars['count(id)'].')
				</td>';
		} else {
			echo '
				<td class="menu">
					<span class="menudisabled">Clarifications</span>
				</td>';
		}
	} else {
		echo '
				<td class="menu"><a class="menulink" 
						href="http://lists.idi.ntnu.no/mailman/listinfo/playground">Mailing list</a>
					/
					<a class="menulink"
						href="http://feiten.idi.ntnu.no/wiki/index.php/SnippetList">Snippets</a></td>';
	}
	echo '
			</tr>
			<tr>
				<td class="menu">
					<a class="menulink" href="event.php">Select event</a>
				</td>';
	if (isset($_SESSION["$INST.event_id"])) {
		echo '
				<td class="menu">
						   <a class="menulink" href="showevent.php?event_id='
							.$_SESSION["$INST.event_id"].
							'">';
		//if ($SINGLE_EVENT) {
		//	echo 'Problem list';
		//} else {
			echo $_SESSION["$INST.event_name"].'</a>';
		//}
		if ($_SESSION["$INST.user_isadmin"]) {
			echo '
						   (<a class="menulink" href="event.php?action=editevent&amp;event_id='
							.$_SESSION["$INST.event_id"].
							'">edit</a>)';
		}
		echo '
				</td>
				<td class="menu"><a class="menulink" 
						href="highscore.php?event_id='.
						$_SESSION["$INST.event_id"].'">Event highscore</a>
				</td>';
	} else {
		echo '
				<td class="menu"><span class="menudisabled">Event overview</span></td>
				<td class="menu"><span class="menudisabled">Event highscore</span></td>';
	}
	echo '
			</tr>';
	if (!$SINGLE_EVENT || $_SESSION["$INST.user_isadmin"]) {
		echo '
			<tr>
				<td class="menu"><a class="menulink" href="problem.php">Select problem</a></td>';
		if (isset($_SESSION["$INST.prob_id"])) {
			echo '
				<td class="menu">
						   <a class="menulink" href="run.php?prob_id='
							.$_SESSION["$INST.prob_id"].
							'">'.
							$_SESSION["$INST.prob_name"].'</a>';
			if ($_SESSION["$INST.user_isadmin"]) {
				echo '
						   (<a class="menulink" href="problem.php?action=editproblem&amp;prob_id='
							.$_SESSION["$INST.prob_id"].
							'">edit</a>)';
			}
			echo '
				</td>
				<td class="menu"><a class="menulink" 
						href="highscore.php?prob_id='.
						$_SESSION["$INST.prob_id"].'">Problem highscore</a>
				</td>';
		} else {
			echo '
				<td class="menu"><span class="menudisabled">Submit solution</span></td>
				<td class="menu"><span class="menudisabled">Problem highscore</span></td>';
		} 
		echo '
			</tr>';
	}
	echo'
			</table>';
?>
