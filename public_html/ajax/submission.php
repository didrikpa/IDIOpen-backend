<?

    include_once("../include_html/defs.php");	
    include_once("../include_html/runfunc.php");
	
    if (!isset($_SESSION["$INST.user_id"])) {
        _die("You must be logged in to use this functionality");
    }

    if (!isset($GP['prob_id'])) {
        _die("Problem ID not set.");
    }
	
    if (isset($GP['active_user'])) {
        $active_user = $GP['active_user'];
        if (!isset($_SESSION["$INST.user_id"]) ||
                ($active_user != $_SESSION["$INST.user_id"] &&
                    !$_SESSION["$INST.user_isadmin"])) {
            $active_user = FALSE;
        }
    }
    else if (isset($_SESSION["$INST.user_id"])) {
        $active_user = $_SESSION["$INST.user_id"];
    } else {
        $active_user = FALSE;
    }
	
    $GP['prob_id'] = intval($GP['prob_id']);
	
    if ($active_user) {
        $userprobdir = userdir($active_user)."/".$GP['prob_id'];
        ifmkdir($userprobdir);
    }

    $problem = dbquery1("SELECT * FROM problems WHERE id={$GP['prob_id']}");
    $scoretype = $problem['scoretype'];

    selectproblem($GP['prob_id'], $problem['name']);

    $systemprobdir = "$DATAPATH/problems/{$GP['prob_id']}";
	
    $allevents = dbquery("SELECT events.start, events.end, 
                                 events.id, events.name, events.scoretype
                          FROM events 
                          INNER JOIN eventproblems
                          ON eventproblems.probid = {$problem['id']}
                          AND eventproblems.eventid = " . $eventid);
    $found = FALSE;
	
    foreach ($allevents as $e) {
        if ($e['events.start'] <= time() && time() <= $e['events.end']) {
            if ($found) {
                _die("Cannot determine score from time, as multiple events
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
    
    if($event['id']!=$eventid || $event['start']>time() || $event['end']<time()) {
        _die("This problem is currently not open for submissions.");
    } else {

		$isowner = false;

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
			$RUN_SERVER = 'alan02.idi.ntnu.no';
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
			if (isset($GP['programlang']) && $GP['programlang'] != 'txt') {
				_die("Input must be text, not {$GP['programlang']}");
			}
			$UPLOAD_LANG = array('txt');
		} else {
			if (isset($GP['programlang']) && !array_key_exists($GP['programlang'], $SERVER_LANG)) {
				_die("Language {$GP['programlang']} is not supported");
			}
			$UPLOAD_LANG = array_keys($LANG_EXT);
		}
	
		sort($UPLOAD_LANG);
		
		$output = "";
		if ($GP['action'] == "upload" && $_SESSION["$INST.user_id"] == $active_user) {
			$output = save("uploadfile");
			
		} else if ($GP['action'] == "run") {
			
			$validatorrun = FALSE;
			if (!isset($GP['programname']) || $GP['programname'] == "") {
				_die('<p>You must specify the program to run.</p>');
			}
			if ($problem['inputtype'] == 'program') {
				$output = run($userprobdir, $GP['programname'], $GP['programlang'], 
							  NULL, $problem['checktype'] == 'pipevalidator');
				$validatorrun = $problem['checktype'] == 'pipevalidator';
			} else if ($problem['inputtype'] == 'text') {
				$output = array($problem['inputfile'] => array(
						'output' =>
							file_get_contents("$userprobdir/{$GP['programname']}"),
						'error' => '',
				));
			}
			
			if (is_array($output)) {
				$output = errorreport($output);
			}
			
			if (is_array($output)) {
				
				if ($problem['checktype'] == 'diff') {
					$output = diff($output);
				} else if ($problem['checktype'] == 'validator') {
					foreach (array_keys($output) as $ds) {
						$output[$ds]['useroutput'] = $output[$ds]['output'];
					}
					$output = run($systemprobdir, $problem['validatorfile'], 
								  NULL, $output);
					$validatorrun = TRUE;
				} else if ($problem['checktype'] == 'pipevalidator') {
					// do nothing
				} else {
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
		} else if ($GP['action'] !== "") {
			$output = "Unknown action '{$GP['action']}' or unset '\$programname'";
		}

		if($output != "") {

			$RETVALTAB = array(
				''            => 'Wrong Answer',
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
			
			if (isset($output) && !is_array($output)) {
				echo '<center><table bgcolor="#ccccff" align=center>';
				echo '
				<tr>
					<td>'.$output.'</td>
				</tr>';
			} else if (isset($output['message']) && $output['message'] != '') {
				echo '<center><table bgcolor="#ccccff" align=center>';
				echo '
				<tr>
					<td>'.$output['message'].'</td>
				</tr>';
			} else {
				$tests = array(
					'num_tests' => 0,
					'passed' => 0,
					'crashed' => 0,
					'timedout' => 0,
					'score' => 0.0,
					'error' => 0,
				);
				foreach ($output as $key => $info) {
					if ($info['correct'] === true) {
						echo '<center><table bgcolor="#90EE90" align=center>';
					} else {
						echo '<center><table bgcolor="#FFC0CB" align=center>';
					}
					break;
				}
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
				}

				echo "<tr><td>&nbsp;</td></tr>";
				foreach ($output as $key => $info) {
					echo "<tr><td>";
					if (array_key_exists($info['error'], $RETVALTAB)) {
						if ($info['correct'] === true) {
							echo "Accepted";
						} else if ($info['error'] == '') {
							echo "Wrong Answer";
						} else {
							echo $RETVALTAB[$info['error']];
						}
					} else {
						echo "Unknown return value '{$info['error']}'";
					}
					echo "</td></tr>";
				}
				
				// Updating the score of the run (if applicable):
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
				} else {
					$feedback = "Not updating score (owner/admin/other user).";
				}
				if ($feedback != '') {
					echo '
					<tr><td>&nbsp;</td></tr>
					<tr>
						<td>
							'.$feedback.' 
						</td>
					</tr>';
				}
			}
			echo '</table></center><br>';
		}

	}
?>
