<?
    $title = "Run";

    include_once("../include_html/defs.php");

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
        $RUN_SERVER = '129.241.103.149';
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
    function cleanup() {
        global $problem, $RUN_ID, $INST;
        // FIXME
        if (isset($_SESSION["$INST.user_id"])) {
            release_runlock($_SESSION["$INST.user_id"], $problem['id'], $RUN_ID); 
        }
    }

    function xmlrpc($method, $params = array()) {
        global $RUN_SERVER, $RUN_PORT, $RUN_PATH;
        $fp = fsockopen($RUN_SERVER, $RUN_PORT, $errno, $errstr);
        $request = xmlrpc_encode_request($method, $params);
        $query = "POST $RUN_PATH HTTP/1.0\n" .
                 "User_Agent: Mongo Banan\n" .
                 "Host: $RUN_SERVER\nContent-Type: text/xml\n" .
                 "Content-Length: ".strlen($request)."\n\n".$request."\n";
        if (!fputs($fp, $query, strlen($query))) {
            _die("Write error");
            return 0;
        }
        $contents = '';
        while (!feof($fp)) {
            $contents .= fgets($fp);
        }
        fclose($fp);
        $i = strpos($contents, "<?xml");
        $contents = substr($contents, $i);
        $ret = xmlrpc_decode($contents);
        if (isset($ret['faultCode']) && $ret['faultCode'] != 0) {
            _die("Error during XMLRPC call: " . $ret['faultString']);
        }
        return $ret;
    }  

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

    function getlang($pname) {
        global $LANG_EXT;
        preg_match('/\.([^\.]+)$/', $pname, $match);
        $ext = $match[1];
        if ( !array_key_exists($ext, $LANG_EXT) ) {
            return FALSE;
        }
        return $LANG_EXT[$ext];
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

    function save($file) {
        global $GP, $userprobdir, $INST;
        global $SERVER_LANG, $LANG_EXT, $HTTP_POST_FILES,
               $FILE_LEGALCHARS, $UPLOAD_LANG;

        if (!isset($HTTP_POST_FILES[$file])) {
            return "No such uploaded file: '$file'";
        }
        $ufile = $HTTP_POST_FILES[$file];
         
        if ($ufile['size'] <= 0) {
            return "File was empty.\n";
        }
        if( $ufile['size'] > 50000 ) {
            return "File size too large. Did you really type all this?";
        }
        $filename = $ufile['name'];
        dolog("{$_SESSION["$INST.user_id"]} uploads for problem " .
              "{$GP['prob_id']} file '$filename'");
        if (!preg_match("/^$FILE_LEGALCHARS+$/", $filename)) {
            return "Illegal file name: '$filename'";
        }

        global $programname;
        $programname = $filename; # save for our "run" form
        
        // check if file has legal extension
        preg_match('/.([^\.]+)$/', $filename, $match);
        $ext = $match[1];
        if ( !in_array($ext, $UPLOAD_LANG)) {
            return "$filename has an illegal file ending;\n"
                    . "The file name must end in ". 
                    implode(', ', $UPLOAD_LANG) .".";
        }

        /*coming here means everything ok.... then save.*/
        # backup any old file
        if (file_exists("$userprobdir/$filename")) {
            $backupdir = "$userprobdir/backup";
            ifmkdir($backupdir);
            $backupname = getbackupname($backupdir, $filename);
            rename("$userprobdir/$filename", "$backupdir/$backupname");
        }
        
        # move new file to permanent location
        umask(0117);
        if (!move_uploaded_file($ufile['tmp_name'], 
                "$userprobdir/$filename")) {
            return "Could not save '$filename'";
        }
         
        return "The uploaded file has been saved. " 
                . "You must run the program to get a score.";
    }

    function delete($filename) {
        global $userprobdir;
        if (file_exists("$userprobdir/$filename")) {
            $backupdir = "$userprobdir/backup";
            ifmkdir($backupdir);
            $backupname = getbackupname("$userprobdir/backup", $filename);
            rename("$userprobdir/$filename", "$backupdir/$backupname");
        }
    }

    function run($source_dir, $program_name, $program_lang = NULL, 
                 $results = NULL, $usepipevalidator = false)
    {
        global $GP, $problem, $systemprobdir, $active_user, $isowner,
               $scoretype, $INST;
        global $_SESSION, $RUN_ID;
        global $TIMETEST_REPEAT, $TIMETEST_MINTIME;
        global $RUN_TIMESTAMP, $TOTAL_RUN_TIMEOUT, $LANG_EXT;
        
        if ($isowner) {
            $debug = true;
        }
        else {
            $debug = false;
        }

        if ($results !== NULL) {
            foreach (array_keys($results) as $ds) {
                if ($results[$ds]['error'] != '') {
                    return $results;
                }
            }
        }

        dolog("{$_SESSION["$INST.user_id"]} runs for problem {$GP['prob_id']} 
               $program_name");

        if ($program_lang == NULL) {
            $program_lang = getlang($program_name);
            if ($program_lang === FALSE) {
                return "Extension for program <code>$program_name</code> was not
                        known.";
            }
        }
        if ($program_lang == 'txt') {
            return "Source language cannot be <code>txt</code>.";
        }
        $program_contents = file_get_contents("$source_dir/$program_name");
        $program_enc = array('language' => base64_encode($program_lang),
                             'filename' => base64_encode($program_name),
                             'content' =>  base64_encode($program_contents));
        
        
        if ($results === NULL) {
            echo "
                <h2>Running</h2>
                <p>
                    Please hold while your program is being run...
                </p>";
            flush();
        }

        global $SERVER_LANG;
        if (!array_key_exists($program_lang, $SERVER_LANG)) {
            return "Unknown language '$program_lang'";
        }

        $t = $problem['inputfile'];
        $tpath = "$systemprobdir/$t";
        if ($t == '' || !file_exists($tpath)) {
            return "Problem input '$t' could not be found.";
        }

        $testsets = array($t);
        
        if ($results === NULL) {
            $checks = array();
            $reverse = array();
            foreach ($testsets as $ds) {
                $i = "{$GP['prob_id']}#$ds#".filemtime($tpath);
                $checks[$ds] = base64_encode($i);
                $reverse[$i] = $ds;
            }
            
            # check timestamps on testsets
            $resp_input_missing = xmlrpc('input_missing',
                                         array((array_values($checks)),
                                               base64_encode('workout')));
            $uploads = array();
            foreach ($resp_input_missing as $bid) {
                $id = base64_decode($bid);
                $ds = $reverse[$id];
                $data = file_get_contents("$systemprobdir/$ds");
                $uploads[] = array('cacheid' => $bid, 
                                   'content' => base64_encode($data));
            }
            
            if ($debug) {
                echo '<span class="debug">Uploading ' . count($uploads) . 
                     ' test sets<br /></span>';
            }

            if (count($uploads) > 0) {
                $resp_upload_input = xmlrpc('upload_input',
                                            array($uploads, 
                                                  base64_encode('workout')));
            }

            $input = array();
            foreach ($testsets as $ds) {
                $input[] = array('type' => 'cacheid',
                                 'value' => $checks[$t]);
            }
        }
        else {
            $input = array();
            foreach ($testsets as $ds) {
                $data = file_get_contents("$systemprobdir/$ds") . "\n" .
                        $results[$ds]['useroutput'];
                $input[] = array('type' => 'data',
                                 'value' => base64_encode($data));
            }
        }

        if ($usepipevalidator) {
            $validator_name = $problem['validatorfile'];
            $validator_lang = getlang($validator_name);
            if ($validator_lang === FALSE) {
                return "Extension for validator <code>$validator_name</code> was not
                        known.";
            }
            $validator_contents = file_get_contents("$systemprobdir/$validator_name");
            $validator_enc = array('language' => base64_encode($validator_lang),
                                   'filename' => base64_encode($validator_name),
                                   'content' =>  base64_encode($validator_contents));
        } else {
            $validator_enc = array();
        }

        $r = get_runlock();
        if ($r !== true) {
            return $r;
        }
        
        //echo "
        //    <span>Running test: $t<br /></span>\n\n";
        //flush();
        if ($results === NULL) {
            $timeout = $problem['timeout'];
            if ($scoretype == 'runtime') {
                $repeat = $TIMETEST_REPEAT;
                $mintime = $TIMETEST_MINTIME;
            }
            else {
                $repeat = 1;
                $mintime = 0;
            }
        }
        else {
            $timeout = 60;
            $repeat = 1;
            $mintime = 0;
        }

        $resp_run = xmlrpc('run',
                           array($validator_enc,
                                 $program_enc,
                                 $input,
                                 $timeout + 1.0,
                                 $repeat,
                                 $mintime,
                                 base64_encode('workout')
                           ));
        
        //_p($resp_run);

        release_runlock($_SESSION["$INST.user_id"], $problem['id'], $RUN_ID); 

        if ($results === NULL) {
            $results = array();
        }
        for ($test_num = 0; $test_num < count($testsets); $test_num++) {
            $tmp = $resp_run[$test_num];
            $ds = $testsets[$test_num];
            if (!isset($results[$ds])) {
	        $results[$ds] = array();
            }
            $results[$ds]['output'] = base64_decode($tmp['output']);
            if (!isset($results[$ds]['usertime'])) {
                $results[$ds]['usertime'] = $tmp['usertime'];
            }
            $results[$ds]['error'] = base64_decode($tmp['error']);
            $results[$ds]['errormessage'] = base64_decode($tmp['errormessage']);
            if ($results[$ds]['error'] == '' && 
                    $results[$ds]['usertime'] > $timeout) {
                $results[$ds]['error'] = 'usertimeout';
            }
        }
        return $results;
    }

    function diff($results) {
        global $isowner, $problem, $systemprobdir;

        if ($isowner) {
            $debug = true;
        }
        else {
            $debug = false;
        }

        foreach (array_keys($results) as $ds) {
            if (!isset($results[$ds]['error']) || $results[$ds]['error'] == '') {
                $program_out = $results[$ds]['output'];
                $fasitfile = "$systemprobdir/{$problem['outputfile']}";
                if ($problem['outputfile'] == '' || !file_exists($fasitfile)) {
                    return "Could not find judges answer file '$fasitfile'";
                }
                $fasit_out = file_get_contents($fasitfile);
                $equal = true;

                $full_program_lines = explode("\n", $program_out);
                $full_fasit_lines = explode("\n", $fasit_out);
                $program_lines = array();
                $fasit_lines = array();
                foreach ($full_program_lines as $line) {
                    $program_lines[] = trim($line);
                }
                foreach ($full_fasit_lines as $line) {
                    $fasit_lines[] = trim($line);
                }
                while (count($program_lines) > 0 &&
                        $program_lines[count($program_lines) - 1] == '') {
                    array_pop($program_lines);
                }
                while (count($fasit_lines) > 0 &&
                        $fasit_lines[count($fasit_lines) - 1] == '') {
                    array_pop($fasit_lines);
                }

                if (count($fasit_lines) != count($program_lines)) {
                    $equal = false;
                    if ($debug) {
                        echo "<span class=\"debug\">
                               <p>Wrong number of lines in output while 
                                 testing $ds<br />
                                 Was " . (count($program_lines)) . "<br />
                                 Should be " . (count($fasit_lines)) . "
                              </p>
                              <p>Fasit:</p>
                              <pre>$fasit_out</pre>
                              <p>Output:</p>
                              <pre>$program_out</pre>
                              </span>";
                    }
                }
                else {
                    for ($i = 0; $i < count($fasit_lines); $i++) {
                        if ($fasit_lines[$i] !== $program_lines[$i]) {
                            $equal = false;
                            if ($debug) {
                                echo "<span class=\"debug\">
                                      <p>Differing output in line $i testing
                                         $ds<br />
                                         Program: <code>" . $program_lines[$i] . 
                                         "</code><br />
                                         Fasit: <code>" . $fasit_lines[$i] . "</code>
                                      </p>
                                      </span>";
                            }
                            else {
                                break;
                            }
                        }
                    }
                }
                $results[$ds]['correct'] = $equal;
                if (!$equal) {
                    $results[$ds]['score'] = NULL;
                }
            }
            else {
                $results[$ds]['score'] = NULL;
                $results[$ds]['correct'] = NULL;
            }
        }
        return $results;
    }

    function evaluate($results) {
        global $isowner, $problem, $systemprobdir, $scoretype, $event;

        if ($isowner) {
            $debug = true;
        }
        else {
            $debug = false;
        }
        
        foreach (array_keys($results) as $ds) {
            if ($results[$ds]['error'] != '') {
                $results[$ds]['correct'] = false;
                $results[$ds]['score'] = NULL;
            }
            else {
                if (in_array($problem['checktype'], array('validator', 'pipevalidator'))) {
                    $results[$ds]['correct'] = (floatval($results[$ds]['output']) >= 0);
                }
                if (!$results[$ds]['correct']) {
                    $results[$ds]['score'] = NULL;
                    continue;
                }
                if ($scoretype == 'runtime') {
                    $results[$ds]['score'] = floatval($results[$ds]['usertime']);
                }
                else if (in_array($scoretype, array('validator', 'pipevalidator'))) {
                    $results[$ds]['score'] = floatval($results[$ds]['output']);
                }
                else if ($scoretype == 'solvetime') {
                    global $event;
                    $results[$ds]['score'] = 
                            floatval(time() - $problem['publicdate']);
                }
                else if ($scoretype == 'eventtime') {
                    global $event;
                    $results[$ds]['score'] = floatval(time() - $event['start']);
                }
                else {
                    _die("Unknown scoretype $scoretype.");
                }
            }
        }
        return $results;
    }

    function errorreport($results) {
        global $isowner;

        if ($isowner) {
            $debug = true;
        }
        else {
            $debug = false;
        }
        
        foreach (array_keys($results) as $ds) {
            if ($results[$ds]['error'] != '') {
                if ($results[$ds]['error'] == 'usertimeout' || 
                        $results[$ds]['error'] == 'usercrash') {
                    // just keep, this is not unexpected
                }
                elseif ($debug) {
                    return '
                        <span class="debug">
                        <p>Running remote program gave the following error:</p>
                        <p><b>Error type</b>: <code>' . $results[$ds]['error'] . '</code></p>
                        <p><b>Program output</b></p>
                        <pre>' . $results[$ds]['output'] . '</pre>
                        <p><b>System output</b></p>
                        <pre>' . $results[$ds]['errormessage'] . '</pre>
                        </span>';
                }
                elseif ($results[$ds]['error'] == 'compile') {
                    return '
                            Your program did not compile. If it compiles 
                            locally, check out the compiler switches shown at
                            the bottom of the page. If you think there is an 
                            error in the system, please let us know.';
                }
                elseif ($results[$ds]['error'] == 'internal') {
                    return '
                            An internal error occurred while running your 
                            program. Please send an explanation and a copy of
                            the below error message to 
                            <a href="mailto:nils.grimsmo@idi.ntnu.no"
                            >nils.grimsmo@idi.ntnu.no</a>.
                            <pre>' . $results[$ds]['errormessage'] . '</pre>';
                }
                elseif ($results[$ds]['error'] == 'unknown') {
                    return '
                            An errorvalue (!=0) was returned from your program
                            when it exited. This means it crashed. If it was
                            written in C, it might mean that you forgot
                            <code>return 0;</code> at the end of your 
                            <code>main</code> function.';
                }
                else {
                    return '
                           Something strange happened. Please contact someone
                            with glasses and a beard.';
                }
            }
        }
        return $results;
    }

    function get_runlock()
    {
        global $RUN_ID, $LOCK_FILE, $LOCK_LOG_FILE, $TOTAL_RUN_TIMEOUT, 
               $SIMULTANEOUS_RUNS, $INQUEUE_TIMEOUT, $RUN_TIMESTAMP, $INST;
        global $problem;
        
        # check if we are already in queue
        if (!file_exists($LOCK_FILE)) {
            touch($LOCK_FILE);
        }
        global $lockRequested;
        $lockRequested = true;
        $fp = fopen($LOCK_FILE, 'r+');
        $in = false;
        $queue = array();
        while (!feof($fp)) {
            $line = trim(fgets($fp, 1024));
            if ($line != '') {
                $queue[] = explode(' ', trim($line));
            }
        }
        foreach ($queue as $q) {
            list($rt, $u, $o, $t, $i) = $q;
            # add '&& trim($o) == $ovId' if you want to allow same user
            # to queue up for different exercises
            # the rest of this function has been readied for that
            if (trim($u) == $_SESSION["$INST.user_id"]) {
                $in = true;
                $old_timeout = $TOTAL_RUN_TIMEOUT - (time() - $t);
            }
        }
        # log queue length
        $log_fp = fopen($LOCK_LOG_FILE, 'a');
        fwrite($log_fp, time() . " " . count($queue) . "\n");
        fclose($log_fp);
        # add user to queue if not in
        if (!$in || $old_timeout < 0) {
            $time = time();
            fwrite($fp, "$RUN_TIMESTAMP {$_SESSION["$INST.user_id"]} " 
                    . "{$problem['id']} $time $RUN_ID\n");
        }
        else {
            fclose($fp);
            return "You already have a request in the queue. It will time out
                    in $old_timeout seconds (if it does not renew itself).";
        }
        fclose($fp);
        if (count($queue) < $SIMULTANEOUS_RUNS) {
            return true;
        }
        else {
            echo "
                <p>
                    At most $SIMULTANEOUS_RUNS may run at once. Your position
                    in the queue will be printed (if your browser updates when
                    the text stream is flushed). Do not close this page! You
                    will not be allowed to start new runs before this one is 
                    finished.
                </p>\n\n";
                flush();
        }
        # wait in line
        $prin = 0;
        while (true) {
            # read queue
            $fp = fopen($LOCK_FILE, 'r+'); 
            $queue = array();
            while (!feof($fp)) {
                $line = trim(fgets($fp, 1024));
                if ($line != '') {
                    $queue[] = explode(' ', $line);
                }
            }
            rewind($fp);
            # update own timestamp
            foreach ($queue as $q) {
                list($ot, $u, $o, $t, $i) = $q;
                if ($u == $_SESSION["$INST.user_id"] && $o == $problem['id'] 
                        && $i == $RUN_ID) {
                    $time = time();
                    fwrite($fp, "$ot $u $o $time $i\n");
                }
                else {
                    fwrite($fp, "$ot $u $o $t $i\n");
                }
            }
            fclose($fp);
            $nr = 1;
            foreach ($queue as $q) {
                list($ot, $u, $o, $t, $i) = $q;
                if ($u == $_SESSION["$INST.user_id"] && $o == $problem['id'] 
                        && $i == $RUN_ID) {
                    break;
                }
                if ($nr <= $SIMULTANEOUS_RUNS) {
                    $tim = $TOTAL_RUN_TIMEOUT;
                }
                else {
                    $tim = $INQUEUE_TIMEOUT;
                }
                # remove others if timestamps are too old
                if (time() - $t > $tim) {
                    release_runlock($u, $o, $i);
                }
                else {
                    $nr += 1;
                }
            }
            if ($nr <= $SIMULTANEOUS_RUNS) {
                break;
            }
            if ($prin % 5 == 0) {
                echo "
                    <span>You are number " .  ($nr - $SIMULTANEOUS_RUNS) . 
                    " in the queue.</span><br />\n";
                flush();
                $prin = 0;
            }
            $prin += 1;
            sleep(1);
        }
        echo "
            <span>Your program is now being run!</span><br /><br />\n\n";
        return true;
    }

    function release_runlock($user, $prob, $run_id = false)
    {
        global $RUN_ID, $LOCK_FILE, $lockRequested;

        if ($run_id === false) {
            $run_id = $RUN_ID;
        }
        if (!$lockRequested && $run_id == $RUN_ID) {
            # don't release a lock, unless we've actually
            # requested a lock
            # (that would be a bug - the lock file may not exist)
            return;
        }

        $fp = fopen($LOCK_FILE, 'r+');
        $queue = array();
        while (!feof($fp)) {
            $line = trim(fgets($fp, 1024));
            if ($line != '') {
                $queue[] = $line;
            }
        }
        rewind($fp);
        for ($j = 0; $j < count($queue); $j++) {
            list($ot, $u, $o, $t, $i) = explode(' ', $queue[$j]);
            # run_id is pseudo unique, so testing $user and $prob is
            # almost never necessary, but better be safe than sorry
            if ($u == $user && $o == $prob && $i == $run_id) {
                continue;
            }
            fwrite($fp, $queue[$j] . "\n");
        }
        ftruncate($fp, ftell($fp));
        fclose($fp);
    }

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
