<p>
 <b>Next contest:</b>
 <br>
 <i>
   IDI Open '13,
   <br> April 20th 11:00
  <br>
</p>

	<div class= "login">
	<?php
		#If the user is allready logged in, the welcome snippet is presented
		if(isset($_SESSION) && array_key_exists('loggedInUser',$_SESSION) && $_SESSION['loggedInUser']!=null){
                         include_once("../include_html/defs.php");
                        $clars = dbquery("SELECT *
                                                  FROM clarifications
                                                  INNER JOIN users
                                                  ON clarifications.requestby = users.id 
                                                  AND clarifications.event_id=" . $eventid .
                                                  "ORDER BY clarifications.requestdate");
			echo "&nbsp;&nbsp;Welcome, <b>" . $_SESSION["teamName"] . "</b>".
                                "<p><a href=\"theindex.php?page=submit\">Make Submission</a></p>".
                                "<p><a href=\"theindex.php?page=highscore\">Highscore Table</a></p>".
                            "<p><a href=\"theindex.php?page=clarifications\">Clarifications <strong>(".count($clars).")</strong></a></p>".
                                "<ul><li><a href =\"theindex.php?page=edituser2\">Edit team settings</a></li>".
				"<li><a href =\"theindex.php?page=news&logout=1\">Log Out</a></li></ul>";
		#If the user is not logged in, a log in form is presented
		} else { ?>
			<h1> Log in </h1>
			<ul>
			<form method="POST" action="theindex.php?page=news">
                        <input type="hidden" name="sidebarlogin"  value="yes">
			<li>
			<?php
			#If the user has a cookie for this site, the value for the username is the one he had when last successfully loggin in
			if(isset($_COOKIE) && array_key_exists("userIdiOpen",$_COOKIE) && $_COOKIE["userIdiOpen"]!= null){
				echo "<input type=\"text\" name=\"username\" value = \"" . $_COOKIE["userIdiOpen"] . "\" />";
			}else{
				echo "<input type=\"text\" name=\"username\" value = \"Username\" />";
			}?>
			</li>

			<li>
			<?php
			#If a cookie is not present
			if(!isset($_COOKIE) || !array_key_exists('userIdiOpen',$_COOKIE) || $_COOKIE["userIdiOpen"]!= null){ ?>
				<input type="password" name ="password" value="" /> 
			<?php } else {?>
				<input type="password" name ="password" value="password" /> 
				<?php }?>
			</li>
			<li>
				<input type="Submit" value="Log in" name="login_form" />
			</li>
				</form>
				<?php
				#if an error of any sort occurs during login, it is registered
					if(array_key_exists("wrong username", $errors)){
						echo "<li id =\"error\">Wrong username or password</li>";
					}
				?>
				
				<li>
				
				<a href="theindex.php?page=newpass">Lost password?</a>
				</li>
				
				</ul>
				<?
		}
	?>
	</div>
	<h1>Menu</h1>
	<ul>
		<li><a href="theindex.php?page=news">News</a></li>
		<li><a href="theindex.php?page=description">Information</a></li>
		<li><a href="theindex.php?page=rules">Rules</a></li>
		<li><a href="theindex.php?page=tips">Tips</a></li>
		<li><a href="theindex.php?page=faq">FAQ</a></li>
		<li><a href="theindex.php?page=history">History</a></li>
		<li><a href="theindex.php?page=register">Register</a></li>
		<li><a href="theindex.php?page=highscore">Score Table</a></li>
                <li><a href="theindex.php?page=teams">Teams and members</a></li>
	</ul>
	<p class="footer"></p>
        <!-- sponsor image --> 
        <img id="sponsors" src="img/sponsors.png" usemap="#sponsor_links" alt="Sponsor Images"/>
        <map id="sponsor_links" name="sponsor_links">
            <area shape="rect" coords="18,25,182,88" href="http://jobs.yahoo.no" target="_blank"/>
            <!--<area shape="rect" coords="18,25,182,88" href="theindex.php?page=sponsorfb"/>-->
            <area shape="rect" coords="14,110,182,150" href="http://careers.microsoft.com/careers/en/no/devcenter.aspx" target="_blank"/>
            <area shape="rect" coords="14,170,187,200" href="http://www.arm.com/about/careers/index.php" target="_blank"/>
        </map>
        <!-- -->


