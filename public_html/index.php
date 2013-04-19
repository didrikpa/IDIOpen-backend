<?
    include_once("../include_html/defs.php");

    $possible_actions = array("editnews"     => true, 
                              "modnews"      => true,
                              "shownews"     => false);

    if (!isset($GP['action'])) {
        $GP['action'] = "shownews";
    }
    if (!in_array($GP['action'], array_keys($possible_actions))) {
        _die("Invalid action : {$GP['action']}");
    }
    if ($possible_actions[$GP['action']] == true) {
        if (!isset($_SESSION) || !isset($_SESSION["$INST.user_id"])) {
            _die("You must be logged in to use this functionality");
        }
    }

    $title = "News";
    include($header);

    function readlastnews() {
        $r = dbquery1("SELECT max(date)
                       FROM news");
        $maxdate = $r['max(date)'];
        if ($maxdate != NULL) {
            $r = dbquery1("SELECT content 
                           FROM news 
                           WHERE date = $maxdate");
            $news = $r['content'];
        } else {
            $news = "";
        }
        return $news;
    }

    $action = $GP['action'];
    if ($action == 'modnews') {
        $GP['newnews'] = cleanformstring($GP['newnews']);
        dbquery("INSERT INTO news (date, content)
                 VALUES (".time().", '{$GP['newnews']}')");
        $action = 'shownews';
    } else if ($action == 'editnews') {
        $news = readlastnews();
        echo '
            <h2>Edit news</h2>
            <form method="post" action=".">
                <p>
                    <input type="hidden" name="action" value="modnews" />
                </p>
                <p>
                    <textarea cols="80" rows="40" name="newnews">'.
                    $news
                    .'</textarea>
                </p>
                <p>
                    <input type="submit" value="Submit" />
                </p>
            </form>';
    }
    if ($action == 'shownews') {
        echo '
            <table class="pcont"><tr><td>
            '.readlastnews().'
            </td></tr></table>';
    }

    include($footer);
?>
