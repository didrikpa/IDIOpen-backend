<?
////////////////////////////////////////////////////////////////////////////////

	$eventid = 1;
	$INCPATH = "../include_html";
	$PUBPATH = "../public_html";
	$DATAPATH = "../data";
	$LOGFILE = "$DATAPATH/log";

	$GP = array();
	foreach ($_GET as $k => $v) {
		$GP[$k] = $v;
	}
	foreach ($_POST as $k => $v) {
		$GP[$k] = $v;
	}

	# ADDED BY BJARNE
	foreach ($_REQUEST as $k => $v) {
		$GP[$k] = $v;
	}

	if(array_key_exists('sidebarlogin', $GP) && $GP['sidebarlogin'] == 'yes'){
	 $GP['action'] = 'login';
	 $GP['login_user'] = $playGroundLogOn;
	$GP['login_pass'] = $playGroundPass;
	}


	# ADDED BY BJARNE


	$INSTANCE_NAMES = array(
		'playground' => "Programming Playground",
		'test_playground' => "Programming Playground (test inst)",
		'open07' => "IDI Open 2007",
		'open08' => "IDI Open 2008",
		'open09' => "IDI Open 2009",
		'open10' => "IDI Open 2010",
		'open11' => "IDI Open 2011",
		'open12' => "IDI Open 2012",
		'open13' => "IDI Open 2013",
		'test_open07' => "IDI Open 2007 (test inst)",
	);
	$uri = $_SERVER['REQUEST_URI'];
	if (!isset($INST)) $INST = '';
	foreach (array_keys($INSTANCE_NAMES) as $t) {
		if (preg_match('/\/'.$t.'\//', $uri)) {
			$INST = $t;
			break;
		}
	}
	if ($INST === '') {
		die('Could not decide instance');
	}
	$INST_NAME = $INSTANCE_NAMES[$INST];
	$SINGLE_EVENT = in_array($INST, array('open07', 'test_open07'));

	include_once("$INCPATH/functions.php");
	ifmkdir("$DATAPATH/problems");
	ifmkdir("$DATAPATH/users");

	include_once("$INCPATH/db.php");
	include_once("$INCPATH/user.php");

	if ($SINGLE_EVENT && !isset($_SESSION["$INST.event_id"])) {
		selectevent(1);
	}

	$header = "$INCPATH/header.php";
	$footer = "$INCPATH/footer.php";

	$MAX_TIMEOUT = 60;
	$FILE_LEGALCHARS = "[A-Za-z0-9_\\\\.-]";
	$DATE_FORMAT = 'Y-m-d H:i:s';

	$SCORE_EXP = array(
		'solvetime' => 'Time in seconds from problem was made public',
		'runtime' => 'Execution time of program',
		'validator' => 'Given from a validator',
		'eventtime' => 'Time in seconds from start of event',
		'fromproblem' => 'From problem definition',
	);

	$FIELD_TEXT = array(
		'scoretype' => array(
			'solvetime' => 'Time in seconds from problem was made public',
			'runtime' => 'Execution time of program',
			'validator' => 'Given from a validator',
			'eventtime' => 'Time in seconds from start of event',
			'fromproblem' => 'From problem definition',
		),
		'grade' => array(
			1 => '1st',
			2 => '2nd',
			3 => '3rd',
			4 => '4th',
			5 => '5th',
			0 => 'none',
		),
		'loctype' => array(
			'onsite' => 'Onsite',
			'online' => 'Online',
			'' => '',
		),
		'teamtype' => array(
			'student' => 'Student',
			'pro' => 'Pro',
			'' => '',
		),
	);
?>
