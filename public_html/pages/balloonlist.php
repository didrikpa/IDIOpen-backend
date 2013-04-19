<?
    include_once("../include_html/defs.php");
    include_once("../include_html/highscorefunc.php");

    $GP['event_id'] = $eventid;
    //echo ''.$_SESSION['loggedInTeam'].' is logged in';    
    if (!isset($GP['event_id'])) {
        _die("Event ID not set.");
    }

    $LEGAL_LOCTYPE = array('all');
    foreach (array_keys($FIELD_TEXT['loctype']) as $l) {
        $LEGAL_LOCTYPE[] = $l;
    }
    $GP['loctype'] = "onsite";
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
            <h1>Acc. Submissions - '.$event['name'].'</h1>';
        if ($event['start'] > time() && !$_SESSION["$INST.user_isadmin"]) {
	    echo '
                <p>
                    The event has not started yet. 
                </p>';
        }
        else {

            if(time()>$event['end'])echo '<p>The event has ended.</p>';

            echo '
                <table class="highscore">
                    <tr>
                        <th class="place">&nbsp;</th>
                        <th class="team">Team</th>
                        <th class="solved">Problem</th>
                        <th class="time">Time</th>
                    </tr>';
            
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
                    if($cmp<0) {
                        $tmp = $array[$j];
                        $array[$j] = $array[$j+1];
                        $array[$j+1] = $tmp;
                    } else {
                        break;
                    }
                }
            }

            $rnum = count($array);
            $lastPlace = -1;
            foreach ($array as $acc) {
                
                $c = "";
                if ($rnum % 2)$c = ' class="zebra"';
                echo '
                    <tr'.$c.'>
                    <td class="place"><strong>'.$rnum.'</strong></td>
                        <td class="team"><div>';
                echo ''.replcSpecChar($acc["name"]).'';
                echo '</div></td>
                        <td class="solved">
                                '.$acc["problem"].'</td>
                        <td class="time">' . $acc["score"] . '</td>';
                echo '
                    </tr>';
                $rnum -= 1;
            }
            echo '
                </table>';
        }
    }

function compare($accA, $accB) {
    if($accA["score"]>$accB["score"])return 1;
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
