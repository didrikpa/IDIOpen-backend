<?
    include_once("../include_html/defs.php");
    $MAX_MEMBERS = 3;
    $possible_actions = array('adduser'     => false, // save new user
                              'moduser'     => true, // save updated user
                              'makeadmin'   => true, // save updated user
                              'revokeadmin' => true, // save updated user
                                    );

    if (!isset($GP2['action'])) {
        _die("Invalid action!");
    }
    if (!in_array($GP2['action'], array_keys($possible_actions))) {
        _die("Invalid action : {$GP['action']}");
    }

    // Checks if the current action is legal
    if ($possible_actions[$GP2['action']] === true) {
        if (!isset($_SESSION) || !isset($_SESSION["$INST.user_id"])) {
            _die("You must be logged in to use this functionality");
        }
    }

    $commit_error = '';
	
    if ($commit_error == '' && $GP2['action'] == "adduser") {
			
        $what = array('login', 'name', 'loctype', 'teamtype','password');
        foreach ($what as $w) {
            $GP2["user_$w"] = trim($GP2["user_$w"]);
        }
		
        if (!preg_match('/^(\\S)+$/', $GP2['user_login'])) {
            $commit_error .= 'Login cannot contain whitespace.<br />';
        }
		
        if ($GP2['user_name'] == '') {
            $commit_error .= 'Cannot have empty team name.<br />';
        }
		
        $GP2["user_login"] = cleanformstringnohtml($GP2["user_login"]);
        $GP2["user_name"] = cleanformstringnohtml($GP2["user_name"]);
 
    }
	
	if ($commit_error == '' && $GP2['action'] == "moduser") {
			
        $what = array('login', 'name', 'loctype', 'teamtype');
        foreach ($what as $w) {
            $GP2["user_$w"] = trim($GP2["user_$w"]);
        }
		
        if (!preg_match('/^(\\S)+$/', $GP2['user_login'])) {
            $commit_error .= 'Login cannot contain whitespace.<br />';
        }
		
        if ($GP2['user_name'] == '') {
            $commit_error .= 'Cannot have empty team name.<br />';
        }
		
        $GP2["user_login"] = cleanformstringnohtml($GP2["user_login"]);
        $GP2["user_name"] = cleanformstringnohtml($GP2["user_name"]);
		
        foreach (array('loctype', 'teamtype') as $w) {
            if (!isset($GP2["user_$w"])) {
                $GP2["user_$w"] = '';
            } else {
                if (!in_array($GP2["user_$w"], 
                        array_keys($FIELD_TEXT[$w]))) {
                    $commit_error .= "Illegal $w {$GP2["user_$w"]}";
                }
            }
        }
 
    }
	
    if ($commit_error == '' && $GP2['action'] == "adduser") {
        $r = dbquery("SELECT * FROM users WHERE login='{$GP2['user_login']}'");
        if (count($r) > 0) {
            $commit_error .= "User with login '{$GP2['user_login']}' already 
                              exists.<br />";
        }
    }
	
    if ($commit_error != '') {
        echo "Error: $commit_error";
    }
	
    else if ($GP2['action'] == "adduser" || $GP2['action'] == "moduser") {
		
        if ($GP2['action'] == "adduser") {

            $r = dbquery1("SELECT count(*) FROM users");
            $isadmin = ($r['count(*)'] == 0) ? 1 : 0;
            dbquery("BEGIN TRANSACTION");
            $query = "INSERT INTO users (login,password,name,isadmin,
                                         loctype,teamtype)
                      VALUES ('{$GP2['user_login']}', 
                              '".md5($GP2['user_password'])."',
                              '{$GP2['user_name']}', 
                              $isadmin,
                              '{$GP2['user_loctype']}',
                              '{$GP2['user_teamtype']}')";
            dbquery($query);
			
            dbquery("COMMIT TRANSACTION");

            $queryGetId = "SELECT id FROM users WHERE login ='" .  $GP2['user_login'] . "'";
            $GP['user_id'] = dbquery($queryGetId);



        }
		
        if ($GP2['action'] == "moduser") {
            if (!isset($GP['user_id'])) {
                _die("User ID not set");
            }       
            $GP['user_id'] = intval($GP2['user_id']);
            dbquery("BEGIN TRANSACTION");
            $query = "UPDATE users 
                      SET login='{$GP['user_login']}',
                          name='{$GP['user_name']}',
                          loctype='{$GP['user_loctype']}',
                          teamtype='{$GP['user_teamtype']}'
                      WHERE id={$GP['user_id']};";
            dbquery($query);
            dbquery("COMMIT TRANSACTION");
        }
	
        $udir = "$DATAPATH/users";
        ifmkdir($udir);
        $udir = "$udir/{$GP['user_id']}";
        ifmkdir($udir);

        $GP2['action'] = 'listusers';
    }
	
	// Making a user admin
	
    if ($GP2['action'] == 'makeadmin' || $GP2['action'] == 'revokeadmin') {
        if (!$_SESSION["$INST.user_isadmin"]) {
            _die("You must be an administrator to do this.");
        }
        $GP2['user_id'] = intval($GP2['user_id']);
        if ($GP2['user_id'] == $_SESSION["$INST.user_id"]) {
            _die("You cannot give/take admin rights for yourself.");
        }
        if ($GP2['action'] == 'makeadmin') {
            $isadmin = 1;
        }
        else {
            $isadmin = 0;
        }
        dbquery("UPDATE users
                 SET isadmin=$isadmin
                 WHERE id={$GP2['user_id']}");
        $GP2['action'] = 'listusers';
    }

?>
