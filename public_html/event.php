<?
    include_once("../include_html/defs.php");

    $possible_actions = array("newevent"   => true, // get form for adding event
                              "editevent"  => true, // get form for editing event
                              "addevent"   => true, // save new event
                              "modevent"   => true, // save updated event
                              "delevent"   => true, // delete event
                              "listevents" => false);

    if (!isset($GP['action'])) {
        $GP['action'] = "listevents";
    }
    if (!in_array($GP['action'], array_keys($possible_actions))) {
        _die("Invalid action : {$GP['action']}");
    }
    if (isset($GP['event_id'])) {
        selectevent($GP['event_id']);
    }

    $title = "Events";
    include($header);

    if ($possible_actions[$GP['action']] == true) {
        if (!isset($_SESSION) || !isset($_SESSION["$INST.user_id"])) {
            _die("You must be logged in to use this functionality");
        }
    }

    if (in_array($GP['action'], array('addevent','modevent',
                                      'newevent','editevent', 
                                      'delevent')) &&
            (!$_SESSION["$INST.user_isadmin"])) {
        _die('You must be an admin to edit events in single-event mode');
    }
    $commit_error = '';
    if ($commit_error == '' && 
            ($GP['action'] == "addevent" || $GP['action'] == "modevent")) {
        $GP["event_name"] = trim($GP["event_name"]);
        $GP["event_desc"] = trim($GP["event_desc"]);
        $GP["event_scoretype"] = trim($GP["event_scoretype"]);
        if ($GP['event_name'] == '') {
            $commit_error .= 'Cannot have empty name.<br />';
        }
        $GP['event_name'] = cleanformstringnohtml($GP['event_name']);
        $GP['event_desc'] = cleanformstring($GP['event_desc']);
        $A = preg_split("/\s+/", $GP['event_problist'], -1, PREG_SPLIT_NO_EMPTY);
        $GP['event_problist_split'] = array();
        foreach ($A as $a) {
            $GP['event_problist_split'][] = intval($a);
        }
        foreach ($GP['event_problist_split'] as $pn) {
            $r = dbquery("SELECT id FROM problems WHERE id=$pn");
            if (count($r) == 0) {
                $commit_error .= "Problem $pn does not exist!<br />";
            }
        }
        $GP['event_problist'] = implode(' ', $GP['event_problist_split']);
        if (!in_array($GP['event_scoretype'], array('fromproblem', 'eventtime'))) {
            $commit_error .= 'Invalid selected score type</br >';
        }
        $GP['event_penalty'] = floatval($GP['event_penalty']);
        $GP['event_start_epoc'] = strtotime($GP['event_start']);
        if ($GP['event_start_epoc'] === FALSE) {
            $commit_error .= "Could not parse start time {$GP['event_start']}<br />";
        }
        $GP['event_end_epoc'] = strtotime($GP['event_end']);
        if ($GP['event_end_epoc'] === FALSE) {
            $commit_error .= "Could not parse end time {$GP['event_end']}<br />";
        }
    }
    if ($commit_error == '' && $GP['action'] == "addevent") {
        $r = dbquery("SELECT * FROM events WHERE name='{$GP['event_name']}'");
        if (count($r) > 0) {
            $commit_error .= "Event with name '{$GP['event_name']}' already 
                              exists.<br />";
        }
    }
    if ($commit_error == '' && $GP['action'] == "modevent") {
        $GP['event_id'] = intval($GP['event_id']);
        $r = dbquery("SELECT * FROM events WHERE id={$GP['event_id']}");
        if (count($r) == 0) {
            $commit_error .= "No such event ID={$GP['event_id']}.<br />";
        }
        else if ($r[0]['addedby'] != $_SESSION["$INST.user_id"] &&
                !$_SESSION["$INST.user_isadmin"]) {
            $commit_error .= "You are not the owner of this event.<br />";
        }
    }
    if ($commit_error != '') {
        echo "Error:<br> $commit_error";
    }
    else if ($GP['action'] == "addevent" || $GP['action'] == "modevent") {
        if ($GP['action'] == "addevent") {
            $query = "INSERT INTO events (name, 
                                          desc,
                                          scoretype,
                                          penalty,
                                          dateadded, 
                                          datemodified,
                                          addedby, 
                                          modifiedby,
                                          start, 
                                          end)
                      VALUES ('{$GP['event_name']}', 
                              '{$GP['event_desc']}',
                              '{$GP['event_scoretype']}', 
                              {$GP['event_penalty']}, 
                              ".time().", 
                              ".time().", 
                              {$_SESSION["$INST.user_id"]}, 
                              {$_SESSION["$INST.user_id"]},
                              {$GP['event_start_epoc']}, 
                              {$GP['event_end_epoc']})";
            dbquery($query);
            $query = "SELECT id FROM events WHERE name='{$GP['event_name']}'";
            $r = dbquery1($query);
            $GP['event_id'] = $r['id'];
        }
        if ($GP['action'] == "modevent") {
            if (!isset($GP['event_id'])) {
                _die("Event ID not set");
            }       
            $query = "UPDATE events SET name='{$GP['event_name']}',
                                        desc='{$GP['event_desc']}',
                                        scoretype='{$GP['event_scoretype']}',
                                        penalty={$GP['event_penalty']},
                                        datemodified=".time().",
                                        modifiedby={$_SESSION["$INST.user_id"]},
                                        start={$GP['event_start_epoc']},
                                        end={$GP['event_end_epoc']}
                      WHERE id={$GP['event_id']}";
            dbquery($query);
        }
        dbquery("DELETE FROM eventproblems 
                 WHERE eventid={$GP['event_id']}");
        $num = 0;
        foreach ($GP['event_problist_split'] as $pn) {
            $num += 1;
            dbquery("INSERT INTO eventproblems (eventid, probid, number)
                     values ({$GP['event_id']}, $pn, $num)");
        }
        if ($GP['action'] == "addevent") {
            echo "
            <h2>Event '{$GP['event_name']}' added</h2>";
        }
        else {
            echo "
            <h2>Event '{$GP['event_name']}' modified</h2>";
        }
        $GP['action'] = 'listevents';
    }
    if ($GP['action'] == 'delevent') {
        if (!isset($GP['event_id'])) {
            _die("Event ID was not set");
        }
        $GP['event_id'] = intval($GP['event_id']);
        $event = dbquery1("SELECT *
                           FROM events 
                           WHERE id={$GP['event_id']}");
        if ($event['addedby'] != $_SESSION["$INST.user_id"] &&
                $_SESSION["$INST.user_isadmin"]) {
            _die("You are not the owner of this event.");
        }
        dbquery("DELETE FROM events
                 WHERE id={$GP['event_id']}");
        dbquery("DELETE FROM eventproblems 
                 WHERE eventid={$GP['event_id']}");
        echo "
            <h2>Event '{$event['name']}' deleted</h2>";
        $GP['action'] = 'listevents';
    }
    if ($GP['action'] == "listevents") {
        echo "
        <h2>Event List</h2>";
        if (isset($_SESSION["$INST.user_id"]) && 
                ($_SESSION["$INST.user_isadmin"])) {
            echo '
        <p>
            Add <a href="?action=newevent">new event</a>.
        </p>';
        }
        echo '
        <table>
            <tr>
                <th>Event name</th>
                <th></th>
                <th></th>
                <th>Starts</th>
                <th>Ends</th>';
        if ($_SESSION["$INST.user_isadmin"]) {
            echo '
                <th>Added by</th>
                <th>Scoring</th>
                <th>Penalty per failure</th>';
        }
        echo '
                <th></th>
            </tr>';
        $events = dbquery("SELECT events.*, users.name 
                           FROM events
                           INNER JOIN users
                           ON events.addedby = users.id
                           ORDER BY events.name");
        foreach ($events as $e) {
            $mayalter = (isset($_SESSION["$INST.user_id"]) 
                        && $e['events.addedby'] == $_SESSION["$INST.user_id"])
                        || $_SESSION["$INST.user_isadmin"];
            echo '
            <tr>
                <td>';
            //if (isset($_SESSION["$INST.user_id"])) {
            echo '
                    <a href="showevent.php?event_id='.$e['events.id'].'">'
                            .$e['events.name'].'</a>';
            //}
            //else {
            //echo $e['events.name'];
            //}
            echo '
                </td>';
            if ($mayalter) {
                echo '
                <td><a href="?action=editevent&amp;event_id='.$e['events.id']
                    .'">Edit</a></td>';
            }
            else {
                echo '
                <td></td>';
            }
            if ($e['events.start'] <= time()) {
                echo '
                <td><a href="highscore.php?event_id='.$e['events.id'].'">Highscore</a></td>';
            }
            else {
                echo '
                <td></td>';
            }
            echo '
                <td>'.mydate($e['events.start']).'</td>
                <td>'.mydate($e['events.end']).'</td>';
            if ($_SESSION["$INST.user_isadmin"]) {
                echo '
                    <td>'.$e['users.name'].'</td>
                    <td>'.$SCORE_EXP[$e['events.scoretype']].'</td>
                    <td>'.$e['events.penalty'].'</td>';
            }
            if ($mayalter) {
                echo '
                <td>
                    <a href="event.php?action=delevent&amp;event_id='
                            .$e['events.id'].'">Delete</a>
                </td>';
                    /*
                    <!-- <form action="" method="post">
                        <p>
                        <input type="hidden" name="action" 
                                value="delevent" />
                        <input type="hidden" name="event_id" 
                                value="'.$e['events.id'].'" />
                        <input type="submit" value="Delete" />
                        </p>
                    </form>
                    --> 
                    */
            }
            else {
                echo '
                <td></td>';
            }
            echo '
            </tr>';
        }
        echo '
        </table>';
    }
    if ($GP['action'] == "editevent") {
        if (!isset($event)) {
            $GP['event_id'] = intval($GP['event_id']);
            $event = dbquery1("SELECT * FROM events WHERE id={$GP['event_id']}");
            $event['desc'] = htmlspecialchars($event['desc']);
            $problems = dbquery("SELECT *
                                 FROM eventproblems 
                                 WHERE eventid={$GP['event_id']}
                                 ORDER BY number");
            $plist = array();
            foreach ($problems as $p) {
                $plist[] = $p['probid'];
            }
            $event['problist'] = implode(' ', $plist);
        }
    }       
    else if ($GP['action'] == "newevent") {
        if (!isset($event)) {
            $event = array(
                "name"          => "Enter name of event. (Will be sorted alphabetically by this)",
                "desc"          => "Description of the event. HTML allowed.",
                "problist"      => "Space separated list of integer problem IDs",
                "penalty"       => "Per failed submission.",
                "scoretype"     => "fromproblem",
                "start"         => time(),
                "end"           => time()+3600*5, 
            );
        }
    }
    else if ($GP['action'] == "addevent" || $GP['action'] == "modevent") {
        $event = array();
        foreach ($GP as $k => $v) { 
            if (preg_match("/event_(.*)/", $k, $m)) {
                $event[$m[1]] = $v;
            }
        }
        if ($GP['action'] == "addevent") {
            $GP['action'] = "newevent";
        }
        else if ($GP['action'] == "modevent") {
            $GP['action'] = "editevent";
        }
    }
    if ($GP['action'] == "editevent" || $GP['action'] == "newevent") {
        if ($GP['action'] == "editevent") {
            echo '
        <h2>Modify event</h2>';
        }
        else if ($GP['action'] == "newevent") {
            echo '
        <h2>Add new event</h2>';
        }
        echo '
        <form action="" method="post">
            <p>';
        if ($GP['action'] == "editevent") {
            echo '
                <input type="hidden" name="action" value="modevent" />
                <input type="hidden" name="event_id" value="'.$event['id'].'" />';
        }
        else if ($GP['action'] == "newevent") {
            echo '
                <input type="hidden" name="action" value="addevent" />';
        }
        echo '
            </p>
            <table>
                <tr>
                    <td>Event name:</td>
                    <td><input type="text" name="event_name" size="40"
                            value="' .$event['name'] .'" />
                    </td>
                </tr>
                <tr>
                    <td>Description:</td>
                    <td>
                        <textarea cols="80" rows="10" name="event_desc">'
                                .$event['desc']
                        .'</textarea>
                    </td>
                </tr>
                <tr>
                    <td>Problem list:</td>
                    <td><input type="text" name="event_problist" size="40"
                            value="' .$event['problist'] .'" />
                    </td>
                </tr>
                <tr>
                    <td>Start time:</td>
                    <td><input type="text" name="event_start" size="40"
                            value="' .mydate($event['start']) .'" />
                    </td>
                </tr>
                <tr>
                    <td>End time:</td>
                    <td><input type="text" name="event_end" size="40"
                            value="' .mydate($event['end']) .'" />
                    </td>
                </tr>
                <tr>
                    <td>Score type:</td>
                    <td>'.HTMLselect('event_scoretype', 
                                     array('fromproblem' => $SCORE_EXP['fromproblem'],
                                           'eventtime' => $SCORE_EXP['eventtime']),
                                     $event['scoretype']).'
                    </td>
                </tr>
                <tr>
                    <td>Penalty per failure:</td>
                    <td><input type="text" name="event_penalty" size="20"
                            value="' .$event['penalty'] .'" />
                    </td>
                </tr>
                <tr>
                    <td>
                    </td>
                    <td>
                        <br />
                        <input type="submit" value="Submit" />
                    </td>
                </tr>
            </table>
        </form>
        <table width="800px"><tr><td>
        <ul>
            <li>
                Run scores for a problem in a running event are spearate from
                those outside the period of the event. So if you run a solution
                on a problem before the event starts, you will show on the
                highscore on the problem, but not on the highscore of the
                event.
            </li>
            <li>
                If someone tries to view a problem which is part of a
                <i>currently running</i> event, and the owner of the event is 
                <i>also</i> the owner of the problem, the problem will be made
                public.
            </li>
        </ul>
        </td></tr></table>';
    }

    include($footer);

?>
