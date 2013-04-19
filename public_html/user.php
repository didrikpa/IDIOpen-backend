<?
    include_once("../include_html/defs.php");
    
    $MAX_MEMBERS = 3;
    $possible_actions = array('newuser'     => false, // get form for adding user
                              'edituser'    => true, // get form for editing user
                              'adduser'     => false, // save new user
                              'moduser'     => true, // save updated user
                              'makeadmin'   => true, // save updated user
                              'revokeadmin' => true, // save updated user
                              'listusers'   => false);

    if (!isset($GP['action'])) {
        $GP['action'] = "listusers";
    }
    if (!in_array($GP['action'], array_keys($possible_actions))) {
        _die("Invalid action : {$GP['action']}");
    }

    $title = "Users";
    include($header);

    if ($possible_actions[$GP['action']] === true) {
        if (!isset($_SESSION) || !isset($_SESSION["$INST.user_id"])) {
            _die("You must be logged in to use this functionality");
        }
    }

//    if($GP['action']!="listusers" && !$_SESSION["$INST.user_isadmin"]) {
//        _die("You must be an admin to perform this functionality. Please use http://feiten.idi.ntnu.no/open10/");
//    }

    $commit_error = '';
    if ($commit_error == '' && 
            ($GP['action'] == "adduser" || $GP['action'] == "moduser")) {
        $what = array('login', 'name', 'loctype', 'teamtype');
        for ($i = 1; $i <= $MAX_MEMBERS; $i++) {
            $what[] = "realname$i";
            $what[] = "email$i";
            $what[] = "belong$i";
        }
        foreach ($what as $w) {
            $GP["user_$w"] = trim($GP["user_$w"]);
        }
        for ($i = 1; $i <= $MAX_MEMBERS; $i++) {
            $GP["user_grade$i"] = intval($GP["user_grade$i"]);
        }
        if ($GP['action'] == "moduser") {
            foreach (array("password", "cpassword") as $w) {
                $GP["user_$w"] = trim($GP["user_$w"]);
            }
            $GP["user_id"] = intval($GP["user_id"]);
            /*
            if ($GP['user_password'] == '') {
                $commit_error .= 'Cannot have empty password.<br />';
            }
            */
            if ($GP['user_password'] != $GP['user_cpassword']) {
                $commit_error .= 'Passwords did not match.<br />';
            }
        }
        if (!preg_match('/^(\\S)+$/', $GP['user_login'])) {
            $commit_error .= 'Login cannot contain whitespace.<br />';
        }
        if ($GP['user_name'] == '') {
            $commit_error .= 'Cannot have empty team name.<br />';
        }
        /*if ($GP['user_email1'] == '') {
            $commit_error .= 'First member cannot have empty email.<br />';
        }*/
        $GP["user_login"] = cleanformstringnohtml($GP["user_login"]);
        $GP["user_name"] = cleanformstringnohtml($GP["user_name"]);
        foreach (array('loctype', 'teamtype') as $w) {
            if (!isset($GP["user_$w"])) {
                $GP["user_$w"] = '';
            } else {
                if (!in_array($GP["user_$w"], 
                        array_keys($FIELD_TEXT[$w]))) {
                    $commit_error .= "Illegal $w {$GP["user_$w"]}";
                }
            }
        }
        for ($i = 1; $i <= $MAX_MEMBERS; $i++) {
            $GP["user_realname$i"] = cleanformstringnohtml($GP["user_realname$i"]);
            $GP["user_email$i"] = cleanformstringnohtml($GP["user_email$i"]);
            $GP["user_belong$i"] = cleanformstringnohtml($GP["user_belong$i"]);
            foreach (array('grade') as $w) {
                if (!in_array($GP["user_$w$i"], 
                        array_keys($FIELD_TEXT[$w]))) {
                    $commit_error .= "Illegal $w {$GP["user_$w$i"]} (for $i)";
                }
            }
        }
    }
    if ($commit_error == '' && $GP['action'] == "adduser") {
        $r = dbquery("SELECT * FROM users WHERE login='{$GP['user_login']}'");
        if (count($r) > 0) {
            $commit_error .= "User with login '{$GP['user_login']}' already 
                              exists.<br />";
        }
    }
    else if ($commit_error == '' && $GP['action'] == "moduser") {
        
        if ($GP['user_id'] != $_SESSION["$INST.user_id"] && !$_SESSION["$INST.user_isadmin"]) {
            $commit_error .= "You may only modify yourself.
                    {$GP['user_id']} != {$_SESSION["$INST.user_id"]}";
        }
        $r = dbquery("SELECT * FROM users 
                      WHERE login='{$GP['user_login']}'
                      AND id != {$_SESSION["$INST.user_id"]}");
        /*if (count($r) > 0) {
            $commit_error .= "User with login '{$GP['user_login']}' already 
                              exists.<br />";
        }*/
        
    }
    if ($commit_error != '') {
        echo "Error: $commit_error";
        /*
        $user = array(
            "realname" => $GP['user_realname'],
            "login" => $GP['user_login'],
            "email" => $GP['user_email'],
            "password" => "",
        );
        if (isset($GP['user_id'])) {
            $user['id'] = $GP['user_id'];
        }
        */
    }
    else if ($GP['action'] == "adduser" || $GP['action'] == "moduser") {
        $email_sent_to = array();
        if ($GP['action'] == "adduser") {

            $GP['user_password'] = substr(md5(rand()), 0, 6);
            /*$success = false;
            for ($i = 1; $i <= $MAX_MEMBERS; $i++) {
                if ($GP["user_email$i"] != '') {
                    $success |= mail($GP["user_email$i"],
                                     "Registration for $INST_NAME\n",

                                     "Registration for $INST_NAME\n" .
                                     "\n" .
                                     "Username: {$GP['user_login']}\n" .
                                     "Password: {$GP['user_password']}\n",
                                      
                                     "From: $INST_NAME <www-data@feiten.idi.ntnu.no>\r\n" .
                                     "Reply-to: Nils Grimsmo <nils.grimsmo@idi.ntnu.no>"
                                     );
                    if ($success) {
                        $email_sent_to[] = $GP["user_email$i"];
                    }
                }
            }
            if (!$success) {
                _die("Could not send e-mails");
            }*/
            $r = dbquery1("SELECT count(*) FROM users");
            $isadmin = ($r['count(*)'] == 0) ? 1 : 0;
            dbquery("BEGIN TRANSACTION");
            $query = "INSERT INTO users (login,password,name,isadmin,
                                         loctype,teamtype)
                      VALUES ('{$GP['user_login']}', 
                              '".md5($GP['user_password'])."',
                              '{$GP['user_name']}', 
                              $isadmin,
                              '{$GP['user_loctype']}',
                              '{$GP['user_teamtype']}')";
            dbquery($query);
            $query = "SELECT id FROM users WHERE login='{$GP['user_login']}'";
            $r = dbquery1($query);
            $GP['user_id'] = $r['id'];
            for ($i = 1; $i <= $MAX_MEMBERS; $i++) {
                $any = false;
                foreach (array('realname','email','belong') as $w) {
                    if ($GP["user_$w$i"] != '') {
                        $any = true;
                    }
                }
                if ($any) {
                    dbquery("INSERT INTO members (teamid,memnum,
                                                  name,email,grade,belong)
                             VALUES ('{$GP['user_id']}', 
                                     '$i',
                                     '{$GP["user_realname$i"]}', 
                                     '{$GP["user_email$i"]}',
                                     {$GP["user_grade$i"]},
                                     '{$GP["user_belong$i"]}');");
                }
            }
            dbquery("COMMIT TRANSACTION");
        }
        if ($GP['action'] == "moduser") {
            if (!isset($GP['user_id'])) {
                _die("User ID not set");
            }       
            $GP['user_id'] = intval($GP['user_id']);
            //$u = dbquery1("SELECT * FROM users WHERE id={$GP['user_id']}");
            dbquery("BEGIN TRANSACTION");
            $query = "UPDATE users 
                      SET login='{$GP['user_login']}',
                          name='{$GP['user_name']}',
                          loctype='{$GP['user_loctype']}',
                          teamtype='{$GP['user_teamtype']}'
                      WHERE id={$GP['user_id']};";
                          //isadmin={$u['isadmin']}
            dbquery($query);
            if ($GP['user_password'] != '') {
                $query = "UPDATE users 
                          SET password='".md5($GP['user_password'])."'
                          WHERE id={$GP['user_id']};";
                dbquery($query);
            }
            for ($i = 1; $i <= $MAX_MEMBERS; $i++) {
                $any = false;
                foreach (array('realname','email','belong') as $w) {
                    if ($GP["user_$w$i"] != '') {
                        $any = true;
                    }
                }
                $query = "SELECT * FROM members 
                          WHERE teamid={$GP['user_id']}
                            AND memnum=$i";
                $r = dbquery($query);
                if (count($r) > 0) {
                    $any = true;
                }
                if ($any) {
                    if (count($r)) {
                        dbquery("UPDATE members 
                                 SET name='{$GP["user_realname$i"]}',
                                     email='{$GP["user_email$i"]}',
                                     grade='{$GP["user_grade$i"]}',
                                     belong='{$GP["user_belong$i"]}'
                                 WHERE teamid={$GP['user_id']}
                                   AND memnum=$i;");
                    } else {
                        dbquery("INSERT INTO members (teamid,memnum,
                                                      name,email,grade,belong)
                                 VALUES ('{$GP['user_id']}', 
                                         '$i',
                                         '{$GP["user_realname$i"]}', 
                                         '{$GP["user_email$i"]}',
                                         '{$GP["user_grade$i"]}',
                                         '{$GP["user_belong$i"]}');");
                     }
                }
            }
            dbquery("COMMIT TRANSACTION");
        }
        $udir = "$DATAPATH/users";
        ifmkdir($udir);
        $udir = "$udir/{$GP['user_id']}";
        ifmkdir($udir);
        if ($GP['action'] == "adduser") {
            echo "
            <h2>User '{$GP['user_login']}' added</h2>
            <p>
                Password: <span style=\"font-color: red;\"
                >{$GP['user_password']}</span>
            </p>
            <p>
                E-mail with password sent to " .
                    implode(", ", $email_sent_to) . ".<br/>
                Login and edit your user to change your password.
            </p>
            ";
        }
        else {
            echo "
            <h2>User '{$GP['user_login']}' modified</h2>";
        }
        $GP['action'] = 'listusers';
    }
    if ($GP['action'] == 'makeadmin' || $GP['action'] == 'revokeadmin') {
        if (!$_SESSION["$INST.user_isadmin"]) {
            _die("You must be an administrator to do this.");
        }
        $GP['user_id'] = intval($GP['user_id']);
        if ($GP['user_id'] == $_SESSION["$INST.user_id"]) {
            _die("You cannot give/take admin rights for yourself.");
        }
        if ($GP['action'] == 'makeadmin') {
            $isadmin = 1;
        }
        else {
            $isadmin = 0;
        }
        dbquery("UPDATE users
                 SET isadmin=$isadmin
                 WHERE id={$GP['user_id']}");
        $GP['action'] = 'listusers';
    }
    if ($GP['action'] == 'listusers') {
        echo '
        <h2>List of teams</h2>
        <table>
            <tr>
                <th></th>';
        if ($_SESSION["$INST.user_isadmin"]) {
            echo '
                <th>Login</th>';
        }
        echo '
                <th>Team</th>';
        if ($SINGLE_EVENT) {// && $_SESSION["$INST.user_isadmin"]) {
            echo '
                <th>Team type</th>
                <th>Location</th>';
        }
        for ($i = 1; $i <= $MAX_MEMBERS; $i++) {
            echo "
                <th>Member $i</th>";
        }
        if ($_SESSION["$INST.user_isadmin"] || !$SINGLE_EVENT) {
            echo "
                <th>Solved</th>
                <th>Submitted</th>";
        }
        if ($_SESSION["$INST.user_isadmin"]) {
            echo "
                <th>Is admin</th>";
        } 
        echo "
            </tr>";
        $users = dbquery("SELECT *
                          FROM users
                          ORDER BY name");
        $num = 0;
        foreach ($users as $u) {
            if ($_SESSION["$INST.user_isadmin"] || !$u['isadmin']) {
                $num++;
                $mem = dbquery("SELECT * 
                                FROM members
                                WHERE teamID={$u['id']}
                                ORDER BY memnum");
                $query = "SELECT count(*)
                          FROM runs
                          WHERE userid={$u['id']}
                            AND score NOTNULL";
                if ($SINGLE_EVENT) {
                    $query .= "
                            AND eventid=1";
                } 
                $query .= "
                          GROUP BY probid";
                $r = dbquery($query);
                if (count($r) > 0) {
                    $solved = count($r);
                } else {
                    $solved = 0;
                }
                $query = "SELECT count(*)
                          FROM runs
                          WHERE userid={$u['id']}";
                if ($SINGLE_EVENT) {
                    $query .= "
                            AND eventid=1";
                } 
                $query .= "
                          GROUP BY probid";
                $r = dbquery($query);
                if (count($r) > 0) {
                    $tried = count($r);
                } else {
                    $tried = 0;
                }
                echo '
                <tr>
                    <td style="text-align: right">'.$num.'</td>';
                if ($_SESSION["$INST.user_isadmin"]) {
                    echo '
                    <td>
                        <a href="?action=edituser&amp;user_id='.$u['id'].'">'.
                        $u['login'].'</a>
                    </td>';
                }
                echo '
                    <td>'.$u['name'].'</td>';
                if ($SINGLE_EVENT) {// && $_SESSION["$INST.user_isadmin"]) {
                    echo '
                        <td>'.$FIELD_TEXT['teamtype'][$u['teamtype']].' (';
                    foreach ($mem as $m) {
                        if ($m['grade']) {
                            echo $m['grade'];
                        } else {
                            echo 'x';
                        }
                    }
                    echo ')</td>
                    <td>'.$FIELD_TEXT['loctype'][$u['loctype']].'</td>';
                }
                $i = 1;
                foreach ($mem as $m) {
                    echo '
                        <td>'.$m['name'].'</td>';
                    $i++;
                }
                for ( ; $i <= $MAX_MEMBERS; $i++) {
                    echo '
                        <td></td>';
                }
                if ($_SESSION["$INST.user_isadmin"] || !$SINGLE_EVENT) {
                    echo '
                    <td style="text-align: center;">'.$solved.'</td>
                    <td style="text-align: center;">'.$tried.'</td>';
                }
                if ($_SESSION["$INST.user_isadmin"]
                        && $_SESSION["$INST.user_id"] != $u['id']) {
                    if ($u['isadmin']) {
                        echo '
                        <td>
                            <a href="user.php?action=revokeadmin&amp;user_id='
                                    .$u['id'].'">Revoke admin</a>
                        </td>';
                    }
                    else {
                        echo '
                        <td>
                            <a href="user.php?action=makeadmin&amp;user_id='
                                    .$u['id'].'">Make admin</a>
                        </td>';
                    }
                }
                else {
                    echo '
                        <td>' . ( $u['isadmin'] ? "Admin" : "") . '</td>';
                    
                }
                echo '
                </tr>';
            }
        }
        echo '
        </table>';
    }
    if ($GP['action'] == "edituser") {
        if (!isset($user)) {
            if (!isset($GP['user_id'])) {
                _die('user_id was not set.');
            }
            $user = dbquery1("SELECT * FROM users WHERE id={$GP['user_id']}");
            $members = dbquery("SELECT * FROM members 
                                WHERE teamid={$GP['user_id']}");
            foreach ($members as $m) {
                $i = $m['memnum'];
                $user["realname$i"] = $m['name'];
                $user["email$i"] = $m['email'];
                $user["grade$i"] = $m['grade'];
                $user["belong$i"] = $m['belong'];
            }
            for ($i = 1; $i <= $MAX_MEMBERS; $i++) {
                if (!isset($user["realname$i"])) $user["realname$i"] = '';
                if (!isset($user["email$i"]))    $user["email$i"] = '';
                if (!isset($user["grade$i"]))    $user["grade$i"] = '';
                if (!isset($user["belong$i"]))   $user["belong$i"] = '';
            }
        }
    }       
    else if ($GP['action'] == "newuser") {
        if (!isset($user)) {
            $user = array(
                "login"     => "",
                "name"      => "",
                "loctype"   => "onsite",
                "teamtype"  => "student",
            );
            for ($i = 1; $i <= $MAX_MEMBERS; $i++) {
                $user["realname$i"] = "";
                $user["email$i"] = "";
                $user["grade$i"] = 1;
                $user["belong$i"] = "";
            }
        }
    }
    else if ($GP['action'] == "adduser" || $GP['action'] == "moduser") {
        $user = array();
        foreach ($GP as $k => $v) { 
            if (preg_match("/user_(.*)/", $k, $m)) {
                $user[$m[1]] = $v;
            }
        }
        if ($GP['action'] == "adduser") {
            $GP['action'] = "newuser";
        }
        else if ($GP['action'] == "moduser") {
            $GP['action'] = "edituser";
        }
    }
    if ($GP['action'] == "edituser" || $GP['action'] == "newuser") {
        if ($GP['action'] == "edituser") {
            echo '
        <h2>Modify user</h2>';
        }
        else if ($GP['action'] == "newuser") {
            echo '
        <h2>Add new user</h2>';
        }
        /*echo '
        <p>
            Registration is closed
        </p>';*/
        
        echo '
        <form action="user.php" method="post">
            <p>';
        if ($GP['action'] == "edituser") {
            echo '
                <input type="hidden" name="action" value="moduser" />
                <input type="hidden" name="user_id" value="'.$user['id'].'" />';
        }
        else if ($GP['action'] == "newuser") {
            echo '
                <input type="hidden" name="action" value="adduser" />';
        }
        echo '
            </p>
            <table>
                <tr>
                    <td>Login:</td>
                    <td><input type="text" name="user_login" size="40"
                            maxlength="255" value="' .$user['login'] .'" />
                    </td>
                </tr>
                <tr>
                    <td>Team name:</td>
                    <td><input type="text" name="user_name" size="40"
                            maxlength="255" value="' .$user['name'] .'" />
                    </td>
                    </tr>';
        
        /*if($SINGLE_EVENT) {*/
            echo '
                <tr>
                    <td>Competes:</td>
                    <td>'.HTMLselect('user_loctype',
                                     $FIELD_TEXT['loctype'],
                                     $user['loctype']).'
                    </td>
                </tr>
                <tr>
                    <td>Team type</td>
                    <td>'.HTMLselect('user_teamtype',
                                     $FIELD_TEXT['teamtype'],
                                     $user['teamtype']).'
                    </td>
                </tr>';
        /*} else {
            echo '
                <tr>
                    <td>
                        <input type="hidden" name="user_loctype" value="" />
                        <input type="hidden" name="user_teamtype" value="" />
                    </td>
                </tr>';
        }*/
        for ($i = 1; $i <= 3; $i++) {
            echo '
                <tr>
                    <td></td>
                    <td>Member '.$i.'
                <tr>
                    <td>Real name:</td>
                    <td><input type="text" name="user_realname'.$i.'" size="40"
                            maxlength="255" value="' .$user['realname'.$i] .'" />
                    </td>
                </tr>
                <tr>
                    <td>E-mail:</td>
                    <td><input type="text" name="user_email'.$i.'" size="40"
                            maxlength="255" value="' .$user['email'.$i] .'" />
                    </td>
                </tr>
                <tr>
                    <td>Grade:</td>
                    <td>'.HTMLselect("user_grade$i",
                                     $FIELD_TEXT['grade'],
                                     $user["grade$i"]).'
                    </td>
                </tr>
                <tr>
                    <td>Affiliation:</td>
                    <td><input type="text" name="user_belong'.$i.'" size="40"
                            maxlength="255" value="'.$user["belong$i"].'" />
                    </td>';
            if ($i == 1) {
                echo '
                    <td><span style="font-style: italic;">
                        For example "NTNU/MTDT" or "McKinsey".</span>
                    </td>';
            }
            echo '
                </tr>';
        }
        if ($GP['action'] == 'edituser') {
            echo '
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>New password:</td>
                    <td><input type="password" name="user_password" size="40"
                            maxlength="255" value="" />
                    </td>
                </tr>
                <tr>
                    <td>Confirm new password:</td>
                    <td><input type="password" name="user_cpassword" size="40"
                            maxlength="255" value="" />
                    </td>
                </tr>';
        }
        echo '
                <tr>
                    <td>
                    </td>
                    <td>
                        <br />
                        <input type="submit" value="Submit" />
                    </td>
                </tr>
            </table>
        </form>';
        
    }

    include($footer);

?>
