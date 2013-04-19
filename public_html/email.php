<?
    include_once("../include_html/defs.php");

    if (!$_SESSION["$INST.user_isadmin"]) {
        _die("You sneak");
    }

    $title = 'E-mails';
    include($header);

    $members = dbquery("SELECT *
                        FROM members
                        ORDER by name");

    echo '
        <h2>E-mails</h2>
        <p>';
    foreach ($members as $m) {
        if ($m['name'] != '') {
            echo "
            &nbsp;&nbsp;&nbsp;&nbsp;{$m['name']} &lt;{$m['email']}&gt;,<br />";
        }
    }
    echo '
        </p>';

    include($footer);
?>
