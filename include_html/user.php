<?
	function userdir($user_id) {
		global $DATAPATH;
		$d = "$DATAPATH";
		ifmkdir($d);
		$d = "$d/users";
		ifmkdir($d);
		$d = "$d/$user_id";
		ifmkdir($d);
		return $d;
	}
	if(session_id() ==null){
		session_start();
		}
	//ini_alter("session.gc_maxlifetime", "28800");

	if (isset($GP['action']) && $GP['action'] == 'logout') {
		session_destroy();
		unset($_SESSION);
		unset($GP['action']);
	}

	if (isset($GP['action']) && $GP['action'] == 'login') {
		$GP['login_user'] = cleanformstringnohtml($GP['login_user']);
		$_user = dbquery("SELECT *
						FROM users
						INNER JOIN members
						ON login='{$GP['login_user']}'
						AND users.id=members.teamid");
		if (count($_user) == 0) {
			$_user = dbquery("SELECT *
							FROM users
							WHERE login='{$GP['login_user']}'");
			if (count($_user)) {
				foreach ($_user as $k => $v) {
					foreach ($v as $k2 => $v2) {
						$_user[$k]["users.$k2"] = $v2;
					}
				}
			}
		}
	if (count($_user)) {

			if (isset($GP['mailpass'])) {
				$newpass = substr(md5(rand()), 0, 6);
				$success = false;
				$hasmail = false;
				foreach ($_user as $u) {
					if ($u['members.email'] != '') {
						$success |= mail($u['members.email'],
										 "New password for $INST_NAME\n",

										 "New password for $INST_NAME\n" .
										 "\n" .
										 "Login:	{$u['users.login']}\n" .
										 "Password: $newpass\n",

										 "From: $INST_NAME <www-data@feiten.idi.ntnu.no>\r\n" .
										 "Reply-to: Nils Grimsmo <nils.grimsmo@idi.ntnu.no>"
									 );
					}
				}
				if ($success) {
					dbquery("UPDATE users
							 SET password='".md5($newpass)."'
							 WHERE id={$_user[0]['users.id']}");
					$passwordmailjustsent = 1;
				} else {
					_die("No email addresses registered for this user");
				}
				$_SESSION["$INST.user_isadmin"] = false;
			}
			else {
				if (md5($GP['login_pass']) != $_user[0]['users.password']) {
					_die("Password mismatch");
				}
				$_SESSION["$INST.user_id"] = $_user[0]['users.id'];
				$_SESSION["$INST.user_login"] = $_user[0]['users.login'];
				$_SESSION["$INST.user_isadmin"] = $_user[0]['users.isadmin'];
				unset($_user);
			}
		}
		else {
			_die("No such user {$GP['login_user']}");
		}
		unset($GP['action']);
	}
	else if (isset($_SESSION["$INST.user_id"])) {
		$_user = dbquery1("SELECT * FROM users
							WHERE id={$_SESSION["$INST.user_id"]}");
		$_SESSION["$INST.user_isadmin"] = $_user['isadmin'];
		unset($_user);
	}
	else {
		$_SESSION["$INST.user_isadmin"] = false;
	}
?>
