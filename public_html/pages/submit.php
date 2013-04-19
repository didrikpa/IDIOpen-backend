<?
    //echo "<h1>IDI Open 2008 - Problems</h1>";
    include_once("../include_html/defs.php");
    
    if (!isset($_SESSION) || !isset($_SESSION["$INST.user_id"])) {
        echo '<h1>IDI Open 2012 - Submit Solution</h1>';
        echo "<p>You must be logged in to use this functionality.</p>";
    }
    else
    {
        $event = selectevent($eventid);
        echo '<h1>'.$event['name'].' - Submit Solution</h1>';
        if($event['start'] >  time()) {
            echo '<p>The event has not started yet.</p>';
        } else if($event['end']<time()) {
            echo '<p>The event has ended. You can view the final scores <a href=http://events.idi.ntnu.no/open12/theindex.php?page=highscore>here</a>.</p>';
        } else {
            $problems = dbquery("SELECT * FROM eventproblems 
                                 INNER JOIN problems 
                                 ON eventproblems.eventid = " . $eventid .
                                "AND eventproblems.probid = problems.id
                                 ORDER BY eventproblems.number");
            $count = 0;
            
            $options = '';
            foreach ($problems as $p) {
                if ($p['problems.publicdate'] <= time()) {
					$count = $count + 1;
                    $options = $options . '<option value="' . $p['problems.id'] . '">' . $p['problems.name'] . '</option>';
                }
            }
            if($count==0)echo '<p>There are currently no problems available for submission (if there is an ongoing event, please contact the judges).</p>';
            else echo '<form method="post" action="theindex.php?page=judge" enctype="multipart/form-data">
							<p><select name="prob_id">' . $options . '
								</select>
								<input type="hidden" name="action" value="submit" />
								<input type="file" name="uploadfile" />
								<input type="submit" value="Submit" />
							</p>
						</form>';
        }
    }
?>
