<?
    include_once("../include_html/defs.php");

    $possible_actions = array("showevent" => false,
                              "foobar" => true);

    if (!isset($GP['action'])) {
        $GP['action'] = "showevent";
    }
    if (!in_array($GP['action'], array_keys($possible_actions))) {
        _die("Invalid action : {$GP['action']}");
    }
    if (!isset($GP['event_id'])) {
        _die("Event ID was not set");
    }
    $GP['event_id'] = intval($GP['event_id']);


    if ($possible_actions[$GP['action']] == true) {
        if (!isset($_SESSION) || !isset($_SESSION["$INST.user_id"])) {
            _die("You must be logged in to use this functionality");
        }
    }

    $event = dbquery1("SELECT * FROM events WHERE id={$GP['event_id']}");

    selectevent($GP['event_id'], $event['name']);

    $title = "Event {$event['name']}";
    include($header);
    
    echo '
        <div class="pcont">
            <h2>'.$event['name'].'</h2>
            <table>
                <tr>
                    <td>Event starts:</td>
                    <td> '.mydate($event['start']).'</td>
                </tr>
                <tr>
                    <td>Event ends:</td>
                    <td>'.mydate($event['end']).'</td>
                </tr>
                <tr>
                    <td>Score type:</td>
                    <td>'.$SCORE_EXP[$event['scoretype']].'</td>
                </tr>
                <tr>
                    <td>Penalty per failure:</td>
                    <td>'.$event['penalty'].'</td>
                </tr>
            </table>
            <p>
                '.$event['desc'].'
            </p>
            <p>
                <br />
            </p>
        </div>';

    if ($event['end'] < time()) {
        echo '
            <p class="pcont">
                This event is over. You can still
                solve the problems, but you will not end up on the highscore
                table for the event.
            </p>';
    }
    if ($event['start'] > time() && !$_SESSION["$INST.user_isadmin"]) {
        echo '
            <p><i>
                This event has not started yet. A list of problems will be 
                available '.mydate($event['start']).'.
            </i></p>';
    }
    else {
        $query = "SELECT * FROM eventproblems 
                  INNER JOIN problems 
                  ON eventproblems.eventid = {$event['id']}
                  AND eventproblems.probid = problems.id
                  ORDER BY eventproblems.number";
        $problems = dbquery($query);
        if (count($problems) > 0) {
            echo '
                <table>
                    <tr>
                        <th>#</th>
                        <th>Problem name</th>
                        <th>Made public</th>
                    </tr>';
            foreach ($problems as $pn => $p) {
                echo '
                    <tr>
                        <td>'.($pn+1).'</td>';
                if ($p['problems.publicdate'] <= time() 
                        || (isset($_SESSION["$INST.user_id"]) 
                            && $p['problems.addedby'] == 
                                                $_SESSION["$INST.user_id"])
                        || $_SESSION["$INST.user_isadmin"]) {
                    echo '
                        <td>
                            <a href="run.php?prob_id='.$p['problems.id'].'">'
                            .$p['problems.name'].'</a>
                        </td>';
                }
                else {
                    echo '
                        <td>
                            '.$p['problems.name'].'
                        </td>';
                }
                echo '
                        <td>
                            '.mydate($p['problems.publicdate']).'
                        </td>
                    </tr>';
            }
            echo '
                </table>';
        }
    }

    include($footer);

?>
