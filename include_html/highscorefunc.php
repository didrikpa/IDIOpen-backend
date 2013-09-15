<?

	function cmpuserscore($us1, $us2) {
		if ($us1['solved'] > $us2['solved']) {
			return -1;
		}
		else if ($us1['solved'] < $us2['solved']) {
			return 1;
		}
		else if ($us1['total'] < $us2['total']) {
			return -1;
		}
		else if ($us1['total'] > $us2['total']) {
			return 1;
		}
		return 0;
	}

	function problemhigh($prob_id) {
		$prob_id = intval($prob_id);
		$problem = dbquery1("SELECT * FROM problems WHERE id=$prob_id");
		$high = array('probname'  => $problem['name'],
					  'dosplit'   => ($problem['scoretype'] == 'runtime'),
					  'scorepatt' => ($problem['scoretype'] == 'solvetime'
									  ? "%.0lf" : "%.3lf"),
					  'inevents'  => array(),
					  'results'   => array(),
				);
		$inevents = dbquery("SELECT events.id, events.name
							 FROM events
							 INNER JOIN eventproblems
							 ON eventproblems.probid = $prob_id
							   AND events.id = eventproblems.eventid
							 GROUP BY events.id");

		foreach ($inevents as $ev) {
			$high['inevents'][$ev['events.id']] = $ev['events.name'];
		}

		if ($high['dosplit']) {
			$res = dbquery("SELECT distinct lang
							FROM runs
							WHERE probid=$prob_id
							ORDER BY lang");
			$langs = array();
			foreach ($res as $r) {
				$langs[] = $r['lang'];
			}
		}
		else {
			$langs = array("*");
		}
		foreach ($langs as $lang) {
			$query = "SELECT users.name,
							 min(runs.score),
							 runs.bestprogram
					  FROM runs
					  INNER JOIN users
					  ON runs.probid = $prob_id";
			if ($high['dosplit']) {
				$query .= "
						  AND runs.lang = '$lang'";
			}
			$query .= "
						  AND runs.userid = users.id
						  AND runs.score NOTNULL
						  AND users.isadmin = 0
						  AND eventid == 0
					  GROUP BY users.id
					  ORDER BY min(runs.score)";
			$runs = dbquery($query);
			$ns = array();
			foreach ($runs as $run) {
				$ns[] = array(
						'place'	   => -1,
						'username'	=> $run['users.name'],
						'score'	   => $run['min(runs.score)'],
						'bestprogram' => $run['runs.bestprogram'],
				);
			}
			if (count($ns) > 0) {
				$ns[count($ns) - 1]['place'] = count($ns);
				for ($i = count($ns) - 2; $i >= 0; $i--) {
					if ($ns[$i]['score'] < $ns[$i+1]['score']) {
						$ns[$i]['place'] = $i+1;
					} else {
						$ns[$i]['place'] = $ns[$i+1]['place'];
					}
				}
				$high['results'][$lang] = $ns;
			}

		}
		return $high;
	}

	function eventhigh($event_id, $loctype, $teamtype, $showall = false) {
		$event_id = intval($event_id);
		$high = array();
		$event = dbquery1("SELECT * FROM events WHERE id=$event_id");
		$high['event'] = $event;
		$eventprobs = dbquery("SELECT problems.id,
									  eventproblems.number,
									  problems.scoretype
							   FROM eventproblems
							   INNER JOIN problems
							   ON eventproblems.eventid=$event_id
							   AND eventproblems.probid = problems.id
							   ORDER BY eventproblems.number");
		$high['eventprobs'] = $eventprobs;
		$userscore = array();
		$bestscore = array();
		foreach ($eventprobs as $e) {
			$pid = $e['problems.id'];
			if ($event['scoretype'] != 'eventtime') {
				$best = dbquery1("SELECT min(runs.score)
								  FROM runs
								  WHERE runs.probid = $pid
								  AND runs.eventid = $event_id");
				if ($best['min(runs.score)'] != 0.0) {
					$bestscore[$pid] = $best['min(runs.score)'];
				}
			}
			$query = "SELECT users.*,
							 min(runs.score), sum(runs.tries)
					  FROM runs
					  INNER JOIN users
					  ON runs.userid = users.id
					  AND runs.probid = $pid
					  AND runs.eventid = $event_id
					  AND users.isadmin = 0";
			if ($loctype != 'all') {
				$query .= "
					  AND users.loctype='{$loctype}'";
			}
			if ($teamtype != 'all') {
				$query .= "
					  AND users.teamtype='{$teamtype}'";
			}
			$query .= "
					  GROUP BY users.id
					  ORDER BY users.name";
			$results = dbquery($query);
			foreach ($results as $res) {
				$uid = $res['users.id'];
				if (!isset($userscore[$uid])) {
					$userscore[$uid] = array('sum' => 0,
											 'solved' => 0,
											 'penal' => 0,
											 'failed' => 0,
											 'id' => $res['users.id'],
											 'name' => $res['users.name'],
											 'teamtype' => $res['users.teamtype'],
											 'loctype' => $res['users.loctype'],
											 'score' => array(),
											 'score' => array(),
											 'score' => array(),
											 'tries' => array());
				}
				$us = $res['min(runs.score)'];
				if ($us !== NULL && isset($bestscore[$pid])) {
					$us /= $bestscore[$pid];
				}
				$userscore[$uid]['score'][$pid] = $us;
				$userscore[$uid]['tries'][$pid] = $res['sum(runs.tries)'];
			}
		}
		if ($showall) {
			$query = "SELECT users.id, users.name
					  FROM users
					  WHERE users.isadmin = 0";
			if ($loctype != 'all') {
				$query .= "
					  AND users.loctype='{$loctype}'";
			}
			if ($teamtype != 'all') {
				$query .= "
					  AND users.teamtype='{$teamtype}'";
			}
			$results = dbquery($query);
			foreach ($results as $res) {
				$uid = $res['users.id'];
				if (!isset($userscore[$uid])) {
					$userscore[$uid] = array('sum' => 0,
											 'solved' => 0,
											 'penal' => 0,
											 'failed' => 0,
											 'id' => $res['users.id'],
											 'name' => $res['users.name'],
											 'score' => array(),
											 'tries' => array());
				}
			}
		}
		foreach ($userscore as $k => $u) {
			foreach ($eventprobs as $e) {
				$pid = $e['problems.id'];
				if (isset($u['score'][$pid]) && $u['score'][$pid] !== NULL) {
					$userscore[$k]['sum'] += $u['score'][$pid];
					$userscore[$k]['solved'] += 1;
					$userscore[$k]['failed'] += $u['tries'][$pid] - 1;
					$userscore[$k]['penal'] += ($u['tries'][$pid] - 1)
											   * $event['penalty'];
				}
			}
			$userscore[$k]['total'] = $userscore[$k]['sum']
									  + $userscore[$k]['penal'];
		}
		uasort($userscore, 'cmpuserscore');
		$ns = array();
		foreach ($userscore as $s) {
			$ns[] = array(-1, $s);
		}
		if (count($ns) > 0) {
			$ns[count($ns) - 1][0] = count($ns);
		}
		for ($i = count($ns) - 2; $i >= 0; $i--) {
			if (cmpuserscore($ns[$i][1], $ns[$i+1][1]) < 0) {
				$ns[$i][0] = $i+1;
			} else {
				$ns[$i][0] = $ns[$i+1][0];
			}
		}
		$high['userscore'] = $ns;
		return $high;
	}
?>
