<?
    include_once("../include_html/defs.php");
    
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

    function getlang($pname) {
        global $LANG_EXT;
        preg_match('/\.([^\.]+)$/', $pname, $match);
        $ext = $match[1];
        if ( !array_key_exists($ext, $LANG_EXT) ) {
            return FALSE;
        }
        return $LANG_EXT[$ext];
    }

    function save($file) {
        global $GP, $userprobdir, $INST;
        global $SERVER_LANG, $LANG_EXT, $_FILES,
               $FILE_LEGALCHARS, $UPLOAD_LANG;

        if (!isset($_FILES[$file])) {
            return "No such uploaded file: '$file'";
        }
        $ufile = $_FILES[$file];
         
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
         
        return "";
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
                <center><p>
                    Please wait while your program is being run...
                </p></center>";
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

?>
