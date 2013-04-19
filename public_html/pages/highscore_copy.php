<?
    include_once("../include_html/defs.php");
    include_once("../include_html/highscorefunc.php");

    $GP['event_id'] = "1";
    //echo ''.$_SESSION['loggedInTeam'].' is logged in';    
    if (!isset($GP['event_id'])) {
        _die("Event ID not set.");
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

    if (isset($GP['event_id'])) {
        $GP['event_id'] = intval($GP['event_id']);
        selectevent($GP['event_id']);
        $high = eventhigh($GP['event_id'], $GP['loctype'], $GP['teamtype'],true);
    }

    if (isset($GP['event_id'])) {
        $event = $high['event'];
        $eventprobs = $high['eventprobs'];
        $userscore = $high['userscore'];

        echo '
            <h1>Highscore for '.$event['name'].'</h1>';
        if ($event['start'] > time() && !$_SESSION["$INST.user_isadmin"]) {
	    echo '
                <p>
                    The event has not started yet. 
                </p>';
        }
        else {

            if(time()>$event['end'])echo '<p>The event has ended. These are the final scores.</p>';

            echo '
                <table>
                    <tr>
                        <td>Show for location:</td>';
            foreach ($LEGAL_LOCTYPE as $loc) {
                if ($loc != $GP['loctype']) {
                    echo '
                        <td>
                        <a href="theindex.php?page=highscore&event_id='.$GP['event_id'].
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
                        <a href="theindex.php?page=highscore&event_id='.$GP['event_id'].
                        '&amp;loctype='.$GP['loctype'].'&amp;teamtype='.$tea.
                        '">'.$tea.'</a>
                        </td>';
                } else {
                    echo "
                        <td>$tea</td>";
                }
            }
            // Fill array with accepted submissions
            $array = array();
            foreach ($userscore as $us) {
                if(!isset($us[1]))break;
                $u = $us[1];
                $teamname = $u['name'];

                foreach ($eventprobs as $e) {
                    $pid = $e['problems.id'];
                    if(!isset($u['score'][$pid]))continue;
                    $score = floor($u['score'][$pid]/60);
                    $accepted = array("name" => $u['name'], "problem" => chr(($e['eventproblems.number']+64)), "score" => $score);
                    $array[] = $accepted;
                }
            }
            $array = array_values($array);

            // Sort submission array
            for($i = 0;;$i += 1) {
                if(!isset($array[$i]))break;
                for($j = $i-1;$j>=0;$j -= 1) {
                    $cmp = compare($array[$j],$array[$j+1]);
                    if($cmp>0) {
                        $tmp = $array[$j];
                        $array[$j] = $array[$j+1];
                        $array[$j+1] = $tmp;
                    } else {
                        break;
                    }
                }
            }

            echo '
                    </tr>
                </table><br>
                <table class="highscore">
                    <tr>
                        <th class="place">&nbsp;</th>
                        <th class="team">Team</th>
                        <th class="solved">Solved</th>
                        <th class="time">Time</th>';
            foreach ($eventprobs as $e) {
                echo '
                        <th class="problem">'
                            . chr(($e['eventproblems.number']+64)).'
                        </th>';
            }
            echo '
                    </tr>';
            $rnum = 0;
            $lastPlace = -1;
            foreach ($userscore as $us) {
                if(!isset($us[1]))break;
                $num = $us[0];
                $u = $us[1];
                $rnum += 1;
                
                //echo '- - - - - -  '.$_SESSION['teamName'].'<br>';
                
                if(isset($_SESSION['teamName']) && $u['name']==$_SESSION['teamName']) {    
                    $c =  ' class="active"';
                } else if ($rnum % 2) {
                    $c = ' class="zebra"';
                }
                else {
                    $c = "";
                }
                if($u['solved']==0 && $lastPlace ==-1)$lastPlace = $rnum;
                if($u['solved']==0)$num = $lastPlace;
                echo '
                    <tr'.$c.'>
                    <td class="place"><strong>'.$num.'</strong></td>
                        <td class="team"><div>';
                if(isset($_SESSION['teamName']) && $u['name']==$_SESSION['teamName'])echo '<strong><i>';
                
                echo ''.replcSpecChar($u['name']).'';

                if(isset($_SESSION['teamName']) && $u['name']==$_SESSION['teamName'])echo '</i></strong>';
                
                echo '</div></td>
                        <td class="solved">
                                '.$u['solved'].'</td>
                        <td class="time">' . floor($u['total']/60) . '</td>';
                foreach ($eventprobs as $e) {
                    $pid = $e['problems.id'];
                    $solved = isset($u['score'][$pid]);
                    $tried = isset($u['tries'][$pid]);
                   
                    $score = (isset($u['score'][$pid]) ? floor($u['score'][$pid]/60) : '--');
                    $tries = (isset($u['tries'][$pid]) ? $u['tries'][$pid] : '0');

                    echo '
                        <td class="problem'.($solved ? ' accepted' : '' ) . (!$solved && $tried ? ' rejected' : '') . '">'
                                . $tries . '/' . $score . '</td>';
                }
                echo '
                    </tr>';
            }
            echo '
                </table>';
        }
    }

function compare($accA, $accB) {
    if($accA["score"]>$accB["score"])return -1;
    if($accA["score"]<$accB["score"])return -1;
    if (strcmp($accA["name"],$accB["name"]) != 0)return strcmp($accA["name"], $accB["name"]);
    return strcmp($accA["problem"],$accB["problem"]);
}


function replcSpecChar($string){
  $string = ereg_replace("æ", "&aelig;", $string);
  $string = ereg_replace("ø", "&oslash;", $string);
  $string = ereg_replace("å", "&aring;", $string);
  $string = ereg_replace("Æ", "&AElig;", $string);
  $string = ereg_replace("Ø", "&Oslash;", $string);
  $string = ereg_replace("Å", "&Aring;", $string);
    
  return $string;
}


?>
