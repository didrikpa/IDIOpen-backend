<?
    $title = "Run";

    include_once("../include_html/defs.php");
    include_once("../include_html/runfunc.php");

    //if (!isset($_SESSION["$INST.user_id"])) {
    //    _die("You must be logged in to use this functionality");
    //}

    if (!isset($GP['prob_id'])) {
        _die("Problem ID not set.");
    }
    $GP['prob_id'] = intval($GP['prob_id']);

    if (isset($GP['active_user'])) {
        $active_user = $GP['active_user'];
        if (!isset($_SESSION["$INST.user_id"]) ||
                ($active_user != $_SESSION["$INST.user_id"] &&
                    !$_SESSION["$INST.user_isadmin"])) {
            //unset($GP);
            $active_user = FALSE;
            //_die("Banditt");
        }
    }
    else if (isset($_SESSION["$INST.user_id"])) {
        $active_user = $_SESSION["$INST.user_id"];
    } else {
        $active_user = FALSE;
    }
    if ($active_user) {
        $userprobdir = userdir($active_user)."/".$GP['prob_id'];
        ifmkdir($userprobdir);
    }

    $problem = dbquery1("SELECT * FROM problems WHERE id={$GP['prob_id']}");
    $scoretype = $problem['scoretype'];

    selectproblem($GP['prob_id'], $problem['name']);

    include($header);

/*****************************************************************************/    
    // MONGO testing setup

//  $i = 'program'; $c = 'diff';      $s = 'runtime';
//  $i = 'program'; $c = 'validator'; $s = 'runtime';
//  $i = 'program'; $c = 'diff';      $s = 'validator';
//  $i = 'program'; $c = 'validator'; $s = 'validator';
//  $i = 'program'; $c = 'diff';      $s = 'solvetime';
//  $i = 'program'; $c = 'validator'; $s = 'solvetime';

//NO$i = 'text';    $c = 'diff';      $s = 'runtime';
//NO$i = 'text';    $c = 'validator'; $s = 'runtime';
//  $i = 'text';    $c = 'diff';      $s = 'validator';
//  $i = 'text';    $c = 'validator'; $s = 'validator';
//  $i = 'text';    $c = 'diff';      $s = 'solvetime';
//  $i = 'text';    $c = 'validator'; $s = 'solvetime';

//    $problem['inputtype'] = $i;
//    $problem['checktype'] = $c;
//    $problem['scoretype'] = $s;
/*****************************************************************************/    

    $systemprobdir = "$DATAPATH/problems/{$GP['prob_id']}";
    $allevents = dbquery("SELECT events.start, events.end, 
                                 events.id, events.name, events.scoretype
                          FROM events 
                          INNER JOIN eventproblems
                          ON eventproblems.probid = {$problem['id']}
                          AND eventproblems.eventid = events.id");
    $found = FALSE;
    foreach ($allevents as $e) {
        if ($e['events.start'] <= time() && time() <= $e['events.end']) {
            if ($found) {
                _die("Cannot not determine score from time, as multiple events
                      use this problem.");
            }
            $found = true;
            $event = array('id' => $e['events.id'],
                           'name' => $e['events.name'],
                           'start' => $e['events.start'],
                           'end' => $e['events.end']);
            if ($e['events.scoretype'] == 'eventtime') {
                $scoretype = 'eventtime';
            }
        }
    }
    if (!isset($event)) {
        $event = array('id' => 0);
    }

    //$isowner = false;
    $isowner = isset($_SESSION["$INST.user_id"]) &&
            (($problem['addedby'] == $_SESSION["$INST.user_id"]) ||
                            $_SESSION["$INST.user_isadmin"]);

    /*
    if (!$isowner && $problem['ispublic'] == 0) {
        $query = "SELECT * 
                  FROM events
                  INNER JOIN eventproblems
                  ON eventproblems.probid = {$problem['id']}
                  AND events.id = eventproblems.eventid
                  INNER JOIN problems 
                  ON problems.id = {$problem['id']}
                  AND problems.addedby = events.addedby";
        $started = FALSE;
        $events = dbquery($query);
        foreach ($events as $e) {
            if ($e['events.start'] <= time()) {
                $started = TRUE;
                break;
            }
        }
        if ($started) {
            dbquery("UPDATE problems 
                     SET ispublic=1
                     WHERE id={$problem['id']}");
            $problem['ispublic'] = TRUE;		
        }
        else {
            _die("This problem is not yet public");
        }
    }
    */

    $RUN_ID = rand();
    $RUN_TIMESTAMP = time();

    $RUN_PATH = '/cgi-bin/run.py';

    if ($INST == 'test_playground') {
        $RUN_SERVER = '129.241.103.115';
        //$RUN_SERVER = '129.241.103.149';
    }
    else {  
        //$RUN_SERVER = '129.241.103.115';
        //$RUN_SERVER = '129.241.103.149';

	// If anything fails, use alan02 :)
	// 	$RUN_SERVER = 'alan02.idi.ntnu.no';

	// Use a random run server.
	$RUN_SERVER = 'alan0' + rand(1,4) + '.idi.ntnu.no';
    }
    $RUN_PORT = 80;

    # FIXME take from $problem
    $TIMETEST_MINTIME = 5.0;
    # likewise?
    $TIMETEST_REPEAT = 10;

    $TOTAL_RUN_TIMEOUT = 120; # must be more than TIMEOUT of xmlrpc_server/killscript
    # FIXME what is this?
    $INQUEUE_TIMEOUT = 15.0;
    $SIMULTANEOUS_RUNS = 1;
    $LOCK_FILE = "/tmp/algdat_runlock";
    $LOCK_LOG_FILE = "/tmp/workout_runlock_log";
    $lockRequested = false; # used in release_runlock

    register_shutdown_function('cleanup');

    if (!isset($GP['action'])) {
        $GP['action'] = "";
    }

    $SERVER_LANG = array();
    $resp_get_lang = xmlrpc('get_lang');
    foreach ($resp_get_lang as $k64 => $a64) {
        $k = base64_decode($k64);
        $SERVER_LANG[$k] = array();
        foreach ($a64 as $k264 => $v64) {
            $k2 = base64_decode($k264);
            if (is_array($v64)) {
                $v = array();
                foreach ($v64 as $v3) {
                    $v[] = base64_decode($v3);
                }
            }
            else {
                $v = base64_decode($v64);
            }
            $SERVER_LANG[$k][$k2] = $v;
        }
    }
    if ($problem['inputtype'] == 'text') {
        $SERVER_LANG['txt'] = array(
                'ext' => array('txt'),
                'run' => '',
                'compile' => '',
                'name' => 'Text');
    }
    $LANG_EXT = array();
    foreach ($SERVER_LANG as $k => $v) {
        $ext = $v['ext'];
        foreach ($ext as $e) {
            $LANG_EXT[$e] = $k;
        }
    }

    if ($problem['inputtype'] == 'text') {
        if (isset($GP['programlang']) && 
                $GP['programlang'] != 'txt') {
            _die("Input must be text, not {$GP['programlang']}");
        }
        $UPLOAD_LANG = array('txt');
    }
    else {
        if (isset($GP['programlang']) && 
                !array_key_exists($GP['programlang'], $SERVER_LANG)) {
            _die("Language {$GP['programlang']} is not supported");
        }
        $UPLOAD_LANG = array_keys($LANG_EXT);
    }
    sort($UPLOAD_LANG);

    if ($isowner) {
        echo '
            <p class="debug">What is written in this colour is only visible
            for the problem owner or administrators.</p>';
    }

    if ($problem['publicdate'] > time()) {
        echo '
        <p style="font-size: 30px; color: red">
            This problem is not yet public.
        </p>';
        if (!$isowner) {
            _die("You are not allowed to view this problem.");
        }
    }
    $output = "";
    if ($GP['action'] == "upload" && $_SESSION["$INST.user_id"] == $active_user) {
        $output = save("uploadfile");
    }
    elseif ($GP['action'] == "delete" && $_SESSION["$INST.user_id"] == $active_user) {
        delete($GP['deletename']);
    }
    elseif ($GP['action'] == "run") {
        $validatorrun = FALSE;
        if (!isset($GP['programname']) || $GP['programname'] == "") {
            _die('<p>You must specify the program to run.</p>');
        }
        if ($problem['inputtype'] == 'program') {
            $output = run($userprobdir, $GP['programname'], $GP['programlang'], 
                          NULL, $problem['checktype'] == 'pipevalidator');
            $validatorrun = $problem['checktype'] == 'pipevalidator';
        }
        else if ($problem['inputtype'] == 'text') {
            $output = array($problem['inputfile'] => array(
                    'output' =>
                        file_get_contents("$userprobdir/{$GP['programname']}"),
                    'error' => '',
            ));
        }
        else {
            _die("Illegal inputtype: '{$problem['inputtype']}'");
        }
        if (is_array($output)) {
            $output = errorreport($output);
        }
        if (is_array($output)) {
            if ($problem['checktype'] == 'diff') {
                $output = diff($output);
            }
            else if ($problem['checktype'] == 'validator') {
                foreach (array_keys($output) as $ds) {
                    $output[$ds]['useroutput'] = $output[$ds]['output'];
                }
                $output = run($systemprobdir, $problem['validatorfile'], 
                              NULL, $output);
                $validatorrun = TRUE;
            }
            else if ($problem['checktype'] == 'pipevalidator') {
                // do nothing
            }
            else {
                _die("Illegal checktype: '{$problem['checktype']}'");
            }
        }
        if (is_array($output)) {
            $output = errorreport($output);
        }
        if (is_array($output)) {
            if ($scoretype == 'validator' && !$validatorrun) {
                foreach (array_keys($output) as $ds) {
                    $output[$ds]['useroutput'] = $output[$ds]['output'];
                }
                $output = run($systemprobdir, $problem['validatorfile'], 
                              NULL, $output);
            }
            $output = evaluate($output);
        }
    }
    elseif ($GP['action'] !== "") {
        $output = "Unknown action '{$GP['action']}' or unset '\$programname'";
    }

    if($output != ""){

        $RETVALTAB = array(
            ''            => 'Completed',
            'usercrash'   => 'Crashed or out of resources',
            'usertimeout' => 'Timed out or out of memory',
            'compile'     => 'Compliation error',
            'internal'    => 'Internal error',
            'unknown'     => 'Unkown',
        );

        $verbose = false;

        if ($isowner) {
            $verbose = true;
            $do_link = true;
        }
        echo '
        <h2>Result</h2>';
        /*
        if (isset($_SESSION["$INST.user_isadmin"])) {
            echo '
                <span class="debug">';
            _p($output);
            echo '
                </span>';
        }
        */
        echo '
        <table bgcolor="#ccccff">';
        if (isset($output) && !is_array($output)) {
            echo '
            <tr>
                <td>'.$output.'</td>
            </tr>';
        }
        else if (isset($output['message']) && $output['message'] != '') {
            echo '
            <tr>
                <td>'.$output['message'].'</td>
            </tr>';
        }
        else {
            $tests = array(
                'num_tests' => 0,
                'passed' => 0,
                'crashed' => 0,
                'timedout' => 0,
                'score' => 0.0,
                'error' => 0,
            );
            foreach ($output as $key => $info) {
                $tests['num_tests'] += 1;
                if ($tests['score'] === NULL || $info['score'] === NULL) {
                    $tests['score'] = NULL;
                }
                else {
                    $tests['score'] += $info['score'];
                }
                if ($info['correct'] === true ) {
                    $tests['passed'] += 1;
                }
                if ($info['error'] === 'usercrash') {
                    $tests['crashed'] += 1;
                }
                elseif ($info['error'] === 'usertimeout') {
                    $tests['timedout'] += 1;
                }
                if ($info['error'] != '' && $_SESSION["$INST.user_isadmin"]) {
                    echo '
                        <p class="debug">
                            Error for test '.$key.'
                        </p>
                        <pre class="debug">'
                            .$info['errormessage'].
                        '</pre>';
                }
            }
            $verbose = 1;
            if ($verbose) {
                echo "
            <tr>";
            if (count($output) > 1) {
                echo "
                <th>Test</th>";
            } 
            echo "
                <th>Termination</th>
                <th>Correct</th>
                <th>Score</th>
            </tr>";
                $num = 0;
                foreach ($output as $key => $info) {
                    $num += 1;
                    echo "
            <tr>";
            if (count($output) > 1) {
                echo "
                <td># $num</td>";
            }
            echo "
                <td>";
                    if (array_key_exists($info['error'], $RETVALTAB)) {
                        echo $RETVALTAB[$info['error']];
                    }
                    else {
                        echo "
                    Unknown return value '{$info['error']}'";
                    }
                    echo "
                </td> 
                <td>";
                    if ($info['correct'] === true) {
                        echo "Yes";
                    }
                    elseif ($info['error'] == '') {
                        echo "No";
                    }
                    else {
                        echo '-';
                    }
                    echo "
                </td>
                <td>
                    ".($info['score'] === NULL ? "NULL" : $info['score'])."
                </td> 
            </tr>";
                } 
            }
            if ($active_user == $_SESSION["$INST.user_id"] && !$isowner) {
                $tmp = dbquery1("SELECT sum(tries), max(score)
                                 FROM runs 
                                 WHERE userid=$active_user
                                 AND probid={$problem['id']}
                                 AND eventid={$event['id']}");
                $totaltries = $tmp['sum(tries)'];
                $maxscore = $tmp['max(score)'];
                $tmp = dbquery("SELECT *
                                FROM runs 
                                WHERE userid=$active_user
                                AND probid={$problem['id']}
                                AND lang='{$GP['programlang']}'
                                AND eventid={$event['id']}");
                if (count($tmp) > 0) {
                    $langtries = $tmp[0]['tries'];
                    $langscore = $tmp[0]['score'];
                    $langbestprogram = $tmp[0]['bestprogram'];
                }
                else {
                    $langtries = 0;
                    $langscore = NULL;
                    $langbestprogram = "";
                }
                if ($maxscore === NULL) {
                    $newtries = $langtries + 1;
                    $totaltries += 1;
                }
                else {
                    $newtries = $langtries;
                }
                $hedidpass = false;
                $firstpass = false;
                $scoreisbetter = false;
                $newscore = $langscore;
                $newbestprogram = $langbestprogram;
                if ($tests['num_tests'] == $tests['passed']) {
                    $hedidpass = true;
                    $firstpass = ($langscore === NULL);
                    if ($langscore === NULL 
                            || $tests['score'] < $langscore) {
                        $scoreisbetter = true;
                        $newscore = $tests['score'];
                        $newbestprogram = getbackupname("$userprobdir/backup", $GP['programname']);
                    }
                }
                else if ($langscore === NULL) {
                    $newscore = NULL;
                    $newbestprogram = "";
                }
                if ($newscore === NULL) {
                    $newscore = "NULL";
                }
                if ($langscore === NULL) {
                    $langscore = "NULL";
                }
                dbquery("DELETE FROM runs 
                         WHERE eventid = {$event['id']}
                         AND probid = {$problem['id']}
                         AND userid = $active_user
                         AND lang = '{$GP['programlang']}'");
                dbquery("INSERT INTO runs 
                         (eventid, probid, userid, lang, 
                          score, tries, bestprogram)
                         VALUES ({$event['id']},
                                 {$problem['id']},
                                 $active_user,
                                 '{$GP['programlang']}',
                                 $newscore,
                                 $newtries,
                                 '$newbestprogram')");

                $feedback = "";
                if ($hedidpass) {
                    if (!$firstpass) {
                        $feedback .= "Not updating score.";
                    } else if ($totaltries > 1) {
                        $feedback .= "Tries before success: " . ($totaltries - 1);
                    }
                }
                /*
                if ($event['id'] != 0) {
                    $feedback .= "(For event {$event['name']})<br />";
                }
                $feedback .= "Score: " . ($tests['score'] === NULL ? 
                                        "NULL" : $tests['score']) .".<br />
                             Your previous best: $langscore.";
                if ($problem['inputtype'] == 'program') {
                    $feedback .= " (With language 
                                  <code>{$GP['programlang']}</code>)";
                }
                $feedback .= "<br />
                             Tries before sucsess: $newtries.";
                if ($problem['inputtype'] == 'program') {
                    $feedback .= " (With this language) <br />
                                  Total before success: $totaltries.";
                }
                */
            }
            else {
                $feedback = "Not updating score (owner/admin/other user).";
            }
            if ($feedback != '') {
                echo '
                <tr><td>&nbsp;</td></tr>
                <tr>
                    <td colspan="4">
                        '.$feedback.' 
                    </td>
                </tr>';
            }
        }
        echo '
        </table>';
    }

    echo '
        <div class="pcont">';
    if (!$active_user) {
        echo '
        <p><i>
            Note that you must be logged in to submit and run your 
            solutions.
        </i></p>';
    }
    if ($SINGLE_EVENT && !$_SESSION["$INST.user_isadmin"]) {
        echo '
            <h2>'.$problem['name'].'</h2>';
        echo $problem['desc'];
    } else {
        echo '
        <h2>Rules</h2>
        <p>
            The following is not allowed on this system:
        </p>
        <ul>
            <li>Accessing the network,</li>
            <li>reading and writing from and to disk,</li>
            <li>talking to other processes,</li>
            <li>similar stuff.</li>
        </ul>
        <p>
            If your program tries anything like this, it will usually hang.
            When the program is "sleeping", it will not be killed before in
            a couple of minutes, which will lead to other people having to
            wait longer.
        </p>
        <p>
            NB: Notice that programs may only contain 7-bit ASCII. This means no
            &aelig;, &oslash; or &aring;.
        </p>
        <h2>Problem description</h2>
        <table>
            <tr>
                <th>Problem name</th>
                <td>'.$problem['name'].'</td>
            </tr>';
    /*
        if ($event['id'] == 0) {
            echo '
            <tr>
                <th>&nbsp;</th>
                <td><a href="highscore.php?prob_id='.$problem['id'].'">Highscore</a></td>
            </tr>';
        }
        if ($event['id'] != 0) {
            echo '
            <tr>
                <th>In event</th>
                <td><a href="showevent.php?event_id='.$event['id'].'">'.$event['name'].'</a></td>
            </tr>
            <tr>
                <th>&nbsp;</th>
                <td><a href="highscore.php?event_id='.$event['id'].'">Highscore</a></td>
            </tr>';
        }
     */
        echo '
            <tr>
                <th>Input type</th>
                <td>'.$problem['inputtype'].'</td>
            </tr>
            <tr>
                <th>Score type</th>
                <td>'.$SCORE_EXP[$scoretype].'</td>
            </tr>';
        if ($problem['inputtype'] != 'text') {
            echo '
            <tr>
                <th>Timeout</th>
                <td>'.$problem['timeout'].' seconds</td>
            </tr> ';
        }
        echo '
        </table>
        <p>
            <b>Description</b>
        </p>
        '.$problem['desc'];
    }

    if ($active_user) {
        $sourcefiles = filelist($userprobdir);
        sort($sourcefiles);

        if (count($sourcefiles) > 0) {
            echo '
            <h2>Run program</h2>
            <table>
                <tr>
                    <th></th>
                    <th>Filname</th>
                    <th>Language</th>
                    <th>Uploaded date</th>
                    <th>Size</th>';
            if ($active_user == $_SESSION["$INST.user_id"]) {
                echo "
                    <th></th>";
            } 
            echo "
                </tr>";
            foreach ($sourcefiles as $sf) {
                preg_match('/\.([^\.]+)$/', $sf[0], $match);
                $ext = $match[1];
                echo '
                <tr>
                    <td>
                        <form method="post" action="">
                            <input type="hidden" name="action" value="run" />
                            <input type="hidden" name="programname" 
                                    value="'.$sf[0].'" />
                            <input type="hidden" name="active_user" 
                                value="'.$active_user.'" />
                            <input type="submit" value="Run" />
                            <input type="hidden" name="programlang" 
                                    value="'.$LANG_EXT[$ext].'" />
                        </form>
                    </td>
                    <td>
                        <a href="showcode.php?prob_id='.$problem['id']
                            .'&amp;active_user='.$active_user
                            .'&amp;source='.$sf[0]
                            .'">'.$sf[0].'</a>
                    </td>
                    <td>'.$SERVER_LANG[$LANG_EXT[$ext]]['name'].'</td>
                    <td>'.$sf[1].'</td>
                    <td style="text-align: right">'.$sf[2].' b</td>';
                if ($active_user == $_SESSION["$INST.user_id"]) {
                    echo '
                    <td><form method="post" action="">
                        <input type="submit" value="Delete" />
                        <input type="hidden" name="deletename" value="'.$sf[0].'" />
                        <input type="hidden" name="action" value="delete" />
                        </form>
                    </td>';
                }
                echo '
                </tr>';
            }
            echo '
            </table>';
        }

        if ($active_user == $_SESSION["$INST.user_id"]) {
            echo '
            <h2>Upload program</h2>
            <form method="post" action="" enctype="multipart/form-data">
                <p>
                    <input type="hidden" name="action" value="upload" />
                    <input type="file" name="uploadfile" />
                    <input type="submit" value="Upload" />
                </p>
            </form>';
            if (!$SINGLE_EVENT || $_SESSION["$INST.user_isadmin"]) {
                echo '
            <p>
                Accepting files ending in ' . implode(', ', $UPLOAD_LANG) . '
            </p>';
            }
        }
        
        if ($_SESSION["$INST.user_isadmin"]) {
            echo '
            <h2>Select user</h2>
            <p>
                You may view other users programs. 
            </p>
            <form method="post" action="run.php?prob_id='.$GP['prob_id'].'"
                    enctype="multipart/form-data">
                <p>
                    <select name="active_user">';
            /*
            echo '
                        <option value="'.$active_user.'"></option>';
             */
            /*
            $hasdeliv = dbquery("SELECT DISTINCT users.id, users.realname
                                 FROM runs
                                 INNER JOIN users
                                 ON probid = {$GP['prob_id']}
                                 AND runs.userid = users.id");
             */
            $users = dbquery("SELECT DISTINCT users.id, users.name
                              FROM users
                              ORDER BY users.name");
            $hasdeliv = array();
            foreach ($users as $u) {
                $f = userdir($u['users.id']) . "/" . $GP['prob_id'];
                if (file_exists($f)) {
                    $hasdeliv[] = $u;
                }
            }
            foreach ($hasdeliv as $u) {
                echo '
                        <option value="'.$u['users.id'].'"' .
                ($u['users.id'] == $active_user ? ' selected="selected"' : '').'>'
                .$u['users.name'].'</option>';
            }
            echo '
                    </select>
                    <input type="submit" value="Select" />
                </p>
            </form>';
        }

        echo '
            </div>';

        if ($problem['inputtype'] == 'program' &&
                (!$SINGLE_EVENT || $_SESSION["$INST.user_isadmin"])) {
            echo '
            <h2>Supported languages</h2>
            <!--
            <p>
                Note the redundancy of both sending the input on stdin and 
                specifying a file name as first parameter to the program.
            </p>
            -->
            <table>
                <tr>
                    <th>Name</th>
                    <th>Extension</th>
                    <th>Used compilation</th>
                    <th>Run with</th>
                </tr>';

            $order = array_keys($SERVER_LANG);
            sort($order);
            foreach ($order as $k) {
                if ($k == 'txt') {
                    continue;
                }
                $lang = $SERVER_LANG[$k];
                echo "
                <tr>
                    <td>" . $lang['name'] . "</td>
                    <td><code>" . implode(". ", $lang['ext']) . "</code></td>
                    <td><code>" . $lang['compile'] . "</code></td>
                    <td><code>" . $lang['run'] . " &lt; {INPUT}</code></td>
                </tr>";
            }
            echo '
            </table>';
        }

        if ($problem['checktype'] == 'pipevalidator') {
            preg_match('/^(.+)\.([^\.]+)$/', $problem['validatorfile'], $match);
            $basename = $match[1];
            $ext = $match[2];
            $lang = $LANG_EXT[$ext];
            $valcom = $SERVER_LANG[$lang]['run'];
            $valcom = preg_replace('/{FILENAME}/', $problem['validatorfile'], $valcom);
            $valcom = preg_replace('/{BASENAME}/', $basename, $valcom);
            echo "
            <h2>Validator setup</h2>
            <p>
                Example validator run command:
            </p>
            <pre>\t$valcom \"java UserProgram\" &lt; input.txt</pre>
            <p>
                The validator will then run the program as:
            </p>
            <pre>\tjava UserProgram &lt; input.txt</pre>
            <p>
                The validator <code>{$problem['validatorfile']}</code>:
            <pre>";
            $content = htmlspecialchars(file_get_contents(
                         "$systemprobdir/{$problem['validatorfile']}"));
            $content = preg_replace('/^/', "\t", $content);
            $content = preg_replace("/\n/", "\n\t", $content);
            echo $content;
            echo "</pre>
            ";
        }
    }

    include($footer);

?>
