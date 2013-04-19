<?
    $title = "Run";

    include_once("../include_html/defs.php");
    include($header);

    if (!isset($_SESSION["$INST.user_id"])) {
        _die("You must be logged in to use this functionality");
    }

    if (!isset($GP['prob_id'])) {
        _die("Problem ID not set.");
    }
    $GP['prob_id'] = intval($GP['prob_id']);

    if (!isset($GP['active_user'])) {
        _die("User ID not set.");
    }
    $GP['active_user'] = intval($GP['active_user']);

    if ($GP['active_user'] != $_SESSION["$INST.user_id"] &&
            !$_SESSION["$INST.user_isadmin"]) {
        _die("You may not view other people's code, you sopp."); 
    }
    
    $fil = userdir($GP['active_user']).'/'.$GP['prob_id'].'/'.$GP['source'];
    if (!file_exists($fil)) {
        _die("Cannot find file {$GP['source']}");
    }

    $problem = dbquery1("SELECT name 
                         FROM problems
                         WHERE id={$GP['prob_id']}");
    
    $user = dbquery1("SELECT name
                      FROM users
                      WHERE id={$GP['active_user']}");
    
    echo '
        <h2>Showing code</h2>
        <table>
            <tr>
                <th>Problem:</th>
                <td><a href="run.php?prob_id='.$GP['prob_id']
                        .'&amp;active_user='.$GP['active_user'].'">'
                        .$problem['name'].'</a></td>
            </tr>
            <tr>
                <th>User:</th>
                <td>'.$user['name'].'</td>
            </tr>
        </table>
        <p>
            <br />
        </p>
        <table><tr><td style="color: inherit; 
                              background-color: #eeeeee;
                              padding: 20px;">
            <pre>';
    echo htmlspecialchars(file_get_contents($fil));
    echo '</pre>
        </td></tr></table>';

    include($footer);
?>
