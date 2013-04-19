<?
    include_once("../include_html/defs.php");

    $possible_actions = array("showclars" => false,
                              "newclar" => true,
                              "addclar" => true,
                              "delclar" => true,
                              "newreply" => true,
                              "modreply" => true,
                        );

    if (!isset($GP['action'])) {
        $GP['action'] = "showclars";
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

    $event = selectevent($GP['event_id']);
    if (!$_SESSION["$INST.user_isadmin"] && time() < $event['start']) {
        _die("This event has not yet started, and you are not an admin");
    }

    $title = "Clarifications for {$event['name']}";
    include($header);

    $commit_error = '';
    if ($GP['action'] == 'addclar') {
        $GP['clar_request'] = mysql_real_escape_string(cleanformstringnohtml(trim($GP['clar_request'])));
        if ($GP['clar_request'] == ''){
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
    } else if (in_array($GP['action'], array('delclar', 'modreply'))) {
        if (!isset($GP['clar_id'])) {
            $commit_error = 'Clarification ID not set';
        }
        if ($commit_error == '') {
            if (!$_SESSION["$INST.user_isadmin"]) {
                $commit_error .= 'You must be an admin for '.$GP['action'];
            }
        }
        $GP['clar_id'] = intval($GP['clar_id']);
        if ($commit_error == '') {
            if ($GP['action'] == 'delclar') {
                dbquery("DELETE FROM clarifications 
                         WHERE id={$GP['clar_id']}");
            } else if ($GP['action'] == 'modreply') {
                $GP['clar_answer'] = sqlite_escape_string(cleanformstring(trim($GP['clar_answer'])));
                if ($GP['clar_answer'] == '') {
                    $commit_error .= 'Answer was empty';
                }
                if ($commit_error == '') {
                    dbquery("UPDATE clarifications
                             SET answer='{$GP['clar_answer']}',
                                 answerdate=".time().",
                                 answerby={$_SESSION["$INST.user_id"]}
                             WHERE id={$GP['clar_id']}");
                }
            } else {
                _die("What to do with action {$GP['action']}?");
            }
        }
        $GP['action'] = 'showclars';
    }
    
    echo '
        <div class="pcont">';
    if ($GP['action'] == 'newclar') {
        echo '
            <h2>New clarification request</h2>
            <p>
            <form action="clarifications.php?event_id='.$GP['event_id'].'" 
                    method="POST">
                <input type="hidden" name="action" value="addclar" />
                <textarea cols="80" rows="10" name="clar_request"></textarea>
                <br />
                <br />
                <input type="submit" value="Submit" />
            </form>
            </p>';
    }
    elseif ($GP['action'] == 'newreply') {
        $clar = dbquery1("SELECT *
                          FROM clarifications
                          INNER JOIN users
                          ON clarifications.requestby = users.id 
                          AND clarifications.id={$GP['clar_id']}");
        echo '
            <h2>Answer clarification request</h2>
            <p>
                Added by: <b>'.$clar['users.name'].'</b> 
                ('.mydate($clar['clarifications.requestdate']).')
            </p>
            <p>
                '.$clar['clarifications.request'].'
            </p>
            <p>
            <form action="clarifications.php?event_id='.$GP['event_id'].'" 
                    method="POST">
                <input type="hidden" name="action" value="modreply" />
                <input type="hidden" name="event_id" value="'.$GP['event_id'].'" />
                <input type="hidden" name="clar_id" value="'.$GP['clar_id'].'" />
                <textarea cols="80" rows="10" name="clar_answer">'
                    .$clar['clarifications.answer'].'</textarea>
                <br />
                <br />
                <input type="submit" value="Submit" />
            </form>
            </p>';
    }
    else if ($GP['action'] == 'showclars') {
        echo '
            <!--
            <p>
                Request a <a href="clarifications.php?action=newclar&amp;event_id='.$GP['event_id'].'">new
                clarification</a>.
            </p>
            -->
            <h2>Clarifications</h2>';
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
                Added by: '.$clar['users.name'].' 
                ('.mydate($clar['clarifications.requestdate']).')';
                if ($_SESSION["$INST.user_isadmin"]) {
                    echo ' (<a href="clarifications.php?action=newreply&amp;event_id='
                        .$GP['event_id'].'&amp;clar_id='.$clar['clarifications.id'].'"
                        >reply</a>)';
                    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(<a href="clarifications.php?action=delclar&amp;event_id='
                        .$GP['event_id'].'&amp;clar_id='.$clar['clarifications.id'].'"
                        >delete</a>)';
                }
                echo '
            </p>
            <p>
                <i>'.$clar['clarifications.request'].'</i>
            </p>';
                if ($clar['clarifications.answer'] != NULL) {
                    $replier = dbquery1("SELECT *
                                         FROM users
                                         WHERE id={$clar['clarifications.answerby']}");
                    echo '
            <div style="padding-left: 30px">
            <p>
                Reply by: '.$replier['name'].'
                ('.mydate($clar['clarifications.answerdate']).')
            </p>
            <p>
                <i>'.$clar['clarifications.answer'].'</i>
            </p>
            </div>';
                }
                if ($i++ < count($clars) - 1) {
                    echo '
            <hr/>';
                }
            } 
        } else {
            echo '
            <p><i>No clarification requests for this event.</i></p>';
        }
    }
    echo '
        </div>';


    /*
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
    */

    include($footer);

?>

