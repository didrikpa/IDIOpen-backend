<?
    
    echo "<h1>IDI Open 2013 - Clarifications</h1>";

    include_once("../include_html/defs.php");

    $possible_actions = array("showclars" => false,
                              "newclar" => true,
                              "addclar" => true
                        );
    $GP['event_id'] = $eventid;
    if (!isset($GP['action'])) {
        $GP['action'] = "showclars";
    }
	
    if (!in_array($GP['action'], array_keys($possible_actions))) {
        echo "<p>Invalid action : {$GP['action']}</p>";
    }
    else if(!isset($_SESSION) || !isset($_SESSION["$INST.user_id"])) {
        echo "<p>You must be logged in to use this functionality.</p>";
    }
    else if (!isset($GP['event_id'])) {
        echo "<p>Event ID was not set</p>";
    } else {
	
	    $GP['event_id'] = intval($GP['event_id']);

	    if (!isset($_SESSION) || !isset($_SESSION["$INST.user_id"])) {
	        echo "<p>You must be logged in to use this functionality</p>";
	    } else {

		    $event = selectevent($GP['event_id']);
			
		    if (time() < $event['start'] || time()>$event['end']) {
		        echo "<p>There is no ongoing event at the moment.</p>";
		    } else {

			    //$title = "Clarifications for {$event['name']}";
			    //include($header);

			    $commit_error = '';
			    if ($GP['action'] == 'addclar') {
			        $GP['clar_request'] = sqlite_escape_string(cleanformstringnohtml(trim($GP['clar_request'])));
			        if ($GP['clar_request'] == '') {
			            $commit_error .= 'Empty clarification';
			        }
			        if ($commit_error == '') {
			            dbquery("INSERT INTO clarifications
			                        (event_id, request, requestdate, requestby)
			                     VALUES ({$GP['event_id']},
			                             '{$GP['clar_request']}',
			                             ".time().",
			                             {$_SESSION["$INST.user_id"]})");
			        }
			        $GP['action'] = 'showclars';
			    }
			    
			    echo '
			        <div class="pcont">';
					
			    if ($GP['action'] == 'newclar') {
			        echo '
			            <p><strong>Submit a clarification:</strong></p>
			            <p>
			            <form action="theindex.php?page=clarifications" 
			                    method="POST">
			                <input type="hidden" name="action" value="addclar" />
			                <textarea cols="60" rows="10" name="clar_request"></textarea>
			                <br />
			                <br />
			                <input type="submit" value="Submit" />
			            </form>
			            </p>';
			    }
				
				
			    else if ($GP['action'] == 'showclars') {
			        echo '
			           
			            <p>
			                Request a <a href="theindex.php?page=clarifications&amp;action=newclar">new
			                clarification</a>.
			            </p>
			            
			            ';
			        $clars = dbquery("SELECT *
			                          FROM clarifications
			                          INNER JOIN users
			                          ON clarifications.requestby = users.id 
			                          AND clarifications.event_id={$GP['event_id']}
			                          ORDER BY clarifications.requestdate");

			        if (count($clars)) {
			            $i = 0;
			            foreach ($clars as $clar) {
			                echo '
			            <p>
			               <i><strong>'.nl2br($clar['clarifications.request']).'<br/> by '.$clar['users.name'].', '.mydate($clar['clarifications.requestdate']).'</i></strong>
			            </p>';
			                if ($clar['clarifications.answer'] != NULL) {
			                    $replier = dbquery1("SELECT *
			                                         FROM users
			                                         WHERE id={$clar['clarifications.answerby']}");
			                    echo '
			            <div style="padding-left: 30px">
			            <p>
			                Judges Reply:<br> 
			                <i>'.$clar['clarifications.answer'].'</i>
			            </p>
			            </div>';
			                }
			                if ($i++ < count($clars) - 1) {
			                    echo '<br>
			            ';
			                }
			            }
			        } else {
			            echo '
			            <p><i>No answered clarifications for this event.</i></p>';
			        }
			    }
			    echo '
			        </div>';
			}
		}
	}

    //include($footer);

?>

