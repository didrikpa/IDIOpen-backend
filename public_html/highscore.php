<?
    include_once("../include_html/defs.php");
    include_once("../include_html/highscorefunc.php");

    if (!isset($GP['prob_id']) && !isset($GP['event_id'])) {
        _die("Problem or event ID not set.");
    }
    if (isset($GP['prob_id']) && isset($GP['event_id'])) {
        _die("Both problem and event ID not set.");
    }

    $LEGAL_LOCTYPE = array('all');
    foreach (array_keys($FIELD_TEXT['loctype']) as $l) {
        $LEGAL_LOCTYPE[] = $l;
    }
    if (!isset($GP['loctype'])) {
        $GP['loctype'] = $LEGAL_LOCTYPE[0];
    }
    if (!in_array($GP['loctype'], $LEGAL_LOCTYPE)) {
        _die("Illegal loctype {$GP['loctype']}");
    }
    $LEGAL_TEAMTYPE = array('all');
    foreach (array_keys($FIELD_TEXT['teamtype']) as $l) {
        $LEGAL_TEAMTYPE[] = $l;
    }
    if (!isset($GP['teamtype'])) {
        $GP['teamtype'] = $LEGAL_TEAMTYPE[0];
    }
    if (!in_array($GP['teamtype'], $LEGAL_TEAMTYPE)) {
        _die("Illegal teamtype {$GP['teamtype']}");
    }

    if (isset($GP['prob_id'])) {
        $GP['prob_id'] = intval($GP['prob_id']);
        selectproblem($GP['prob_id']);
        $high = problemhigh($GP['prob_id']);
    }
    else if (isset($GP['event_id'])) {
        $GP['event_id'] = intval($GP['event_id']);
        selectevent($GP['event_id']);
        $high = eventhigh($GP['event_id'], $GP['loctype'], $GP['teamtype']);
    }

    $title = 'Highscore';
    include($header);

    if (isset($GP['prob_id'])) {
        foreach ($high['results'] as $lang => $resu) {
            if ($high['dosplit']) {
                echo '
                <h3>Highscore for <code>'.$lang.'</code></h3>';
            }
            echo '
                <table>
                    <tr>
                        <th>#</th>
                        <th>Team / User</th>
                        <th>Score</th>
                        <th>Program</th>
                    </tr>';
            $rnum = 0;
            foreach ($resu as $res) {
                $rnum += 1;
                if ($rnum % 2) {
                    $c = "#eeeeee";
                }
                else {
                    $c = "#ffffff";
                }
                echo '
                    <tr style="background-color: '.$c.'">
                        <td>'.$res['place'].'</td>
                        <td>'.$res['username'].'</td>
                        <td style="text-align: right">'
                            .sprintf($high['scorepatt'], 
                                     $res['score']).'</td>
                        <td>'.$res['bestprogram'].'</td>
                    </tr>';
            }
            echo '
                </table>';
        }
    }
    else if (isset($GP['event_id'])) {
        $event = $high['event'];
        $eventprobs = $high['eventprobs'];
        $userscore = $high['userscore'];

        echo '
            <h2>Highscore for event '.$event['name'].'</h2>';
        if ($event['start'] > time() && !$_SESSION["$INST.user_isadmin"]) {
            echo '
                <p>
                    This event has not started yet. 
                </p>';
        }
        else {
            echo '
                <table>
                    <tr>
                        <td>Show for location:</td>';
            foreach ($LEGAL_LOCTYPE as $loc) {
                if ($loc != $GP['loctype']) {
                    echo '
                        <td>
                        <a href="highscore.php?event_id='.$GP['event_id'].
                        '&amp;loctype='.$loc.'&amp;teamtype='.$GP['teamtype'].
                        '">'.$loc.'</a>
                        </td>';
                } else {
                    echo "
                        <td>$loc</td>";
                }
            }
            echo '
                    </tr>
                    <tr>
                        <td>Show for team type:</td>';
            foreach ($LEGAL_TEAMTYPE as $tea) {
                if ($tea != $GP['teamtype']) {
                    echo '
                        <td>
                        <a href="highscore.php?event_id='.$GP['event_id'].
                        '&amp;loctype='.$GP['loctype'].'&amp;teamtype='.$tea.
                        '">'.$tea.'</a>
                        </td>';
                } else {
                    echo "
                        <td>$tea</td>";
                }
            }
            echo '
                    </tr>
                </table>';
            if ($event['scoretype'] == 'fromproblem') {
                echo '
                <p>
                    Scores are normalised to 1 for best score.
                </p>';
                $scorepatt = "%.3lf";
            }
            else {
                echo '
                <br />';
                $scorepatt = "%.0lf";
            }
            echo '
                <table style="border-style: none;">
                    <tr>
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                        <th style="border-right: solid 1px;">&nbsp;</th>';
            foreach ($eventprobs as $e) {
                echo '
                        <th colspan="2" style="text-align: center; 
                                               border-right: solid 1px;">';
                if (isset($_SESSION["$INST.user_id"])) {
                    echo '
                       <a href="run.php?prob_id='.$e['problems.id'].'">';
                }
                echo 'Problem ' .($e['eventproblems.number']);
                if (isset($_SESSION["$INST.user_id"])) {
                    echo '</a>';
                } 
                if (!$SINGLE_EVENT || $_SESSION["$INST.user_isadmin"]) {
                    echo '
                            <br />
                            <a href="highscore.php?prob_id='.$e['problems.id'].'">'
                            .'Highscore</a>';
                }
                echo '
                        </th>';
            }
            echo '
                    </tr>
                    <tr>
                        <th>#</th>
                        <th>Team / User</th>
                        <th style="border-right: solid 1px;">Solved</th>';
            foreach ($eventprobs as $e) {
                echo '
                        <th>Score</th>
                        <th style="border-right: solid 1px;">Tries</th>';
            }
            if ($event['penalty'] > 0.0) {
                echo '
                        <th>Sum score</th>
                        <th>Sum penalty</th>';
            }
            echo '
                        <th>Total</th>
                    </tr>';
            $rnum = 0;
            foreach ($userscore as $us) {
                $num = $us[0];
                $u = $us[1];
                $rnum += 1;
                if ($rnum % 2) {
                    $c = "#eeeeee";
                }
                else {
                    $c = "#ffffff";
                }
                echo '
                    <tr style="background-color: '.$c.'">
                        <td>'.$num.'</td>
                        <td>'.$u['name'].'</td>
                        <td style="border-right: solid 1px; text-align: center;">
                                <b>'.$u['solved'].'</b></td>';
                foreach ($eventprobs as $e) {
                    $pid = $e['problems.id'];
                    if (isset($u['score'][$pid])) {
                        echo '
                            <td style="text-align: right; margin: 0px; ">'
                            .sprintf($scorepatt, $u['score'][$pid]).'</td>';
                    }
                    else {
                        echo '
                            <td>&nbsp;</td>';
                    }
                    if (isset($u['tries'][$pid])) {
                        echo '
                            <td style="text-align: center; border-right: solid 1px;">
                                '.$u['tries'][$pid].'
                            </td>';
                    }
                    else {
                        echo '
                            <td style="border-right: solid 1px;">&nbsp;</td>';
                    }
                }
                if ($event['penalty'] > 0.0) {
                    echo '
                        <td style="text-align: center;">'
                            .sprintf($scorepatt, $u['sum']).'</td>
                        <td style="text-align: center;">'
                            .sprintf($scorepatt, $u['penal']).'</td>';
                }
                echo '
                        <td style="text-align: center;"><b>'
                            .sprintf($scorepatt, $u['total']).'</b></td>
                    </tr>';
            }
            echo '
                </table>';
        }
    }

    include($footer);

?>
