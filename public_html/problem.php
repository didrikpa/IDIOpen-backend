<?
error_reporting(E_ALL);
ini_set("display_errors", 1); 

    include_once("../include_html/defs.php");

echo "Data path=$DATAPATH<br>";
    $possible_actions = array("newproblem"   => true, // get form for adding problem
                              "editproblem"  => true, // get form for editing problem
                              "addproblem"   => true, // save new problem
                              "modproblem"   => true, // save updated problem
                              "delproblem"   => true, // delete problem
                              "listproblems" => false);

    if (!isset($GP['action'])) {
        $GP['action'] = "listproblems";
    }
    if (!in_array($GP['action'], array_keys($possible_actions))) {
        _die("Invalid action : {$GP['action']}");
    }

    if (isset($GP['prob_id'])) {
        selectproblem($GP['prob_id']);
    }
    $title = "Problems";
    include($header);

    if ($possible_actions[$GP['action']] == true) {
        if (!isset($_SESSION) || !isset($_SESSION["$INST.user_id"])) {
            _die("You must be logged in to use this functionality");
        }
    }

    if (in_array($GP['action'], array('addproblem','modproblem',
                                      'newproblem','editproblem', 
                                      'delproblem')) &&
            (!$_SESSION["$INST.user_isadmin"])) {
        _die('You must be an admin to edit problems in single-event mode');
    }
    $commit_error = '';
    if ($commit_error == '' && 
            ($GP['action'] == "addproblem" || $GP['action'] == "modproblem")) {
        foreach (array("name", "desc", "inputtype", "checktype", "scoretype") as $w) {
            $GP["prob_$w"] = trim($GP["prob_$w"]);
        }
        if ($GP['prob_name'] == '') {
            $commit_error .= 'Cannot have empty name.<br />';
        }
        $GP['prob_name'] = cleanformstringnohtml($GP['prob_name']);
        $GP['prob_desc'] = cleanformstring($GP['prob_desc']);
        $GP['prob_timeout'] = floatval($GP['prob_timeout']);
        if ($GP['prob_timeout'] > $MAX_TIMEOUT) {
            $commit_error .= "Timeout of {$GP['prob_timeout']} is too high.
                              Max is $MAX_TIMEOUT. <br />";
        }
        if (!in_array($GP['prob_inputtype'], array('program', 'text')) ||
            !in_array($GP['prob_checktype'], array('diff', 'validator', 'pipevalidator')) ||
            !in_array($GP['prob_scoretype'], array('runtime', 'validator','solvetime'))) {
            $commit_error .= 'Invalid selected problem options</br >';
        }
        if ($GP['prob_inputtype'] == 'text' && 
                $GP['prob_scoretype'] == 'runtime') {
            $commit_error .= "Impossible combination: 
                              inputtype == program && scoretype == runtime.<br />";
        }
        $GP['prob_publicdate_epoc'] = strtotime($GP['prob_publicdate']);
        if ($GP['prob_publicdate_epoc'] === FALSE) {
            $commit_error .= "Could not parse start time {$GP['prob_publicdate']}<br />";
        }
        foreach (array("inputfile", "outputfile", "validatorfile") as $w) {
            if (!preg_match("/^$FILE_LEGALCHARS*$/",
                            $_FILES["prob_$w"]["name"])) {
                $commit_error .= "Illegal file name 
                                  {$_FILES["prob_$w"]["name"]}<br />";
            }
        }
    }
    if ($commit_error == '' && $GP['action'] == "addproblem") {
        $r = dbquery("SELECT * FROM problems WHERE name='{$GP['prob_name']}'");
        if (count($r) > 0) {
            $commit_error .= "Problem with name '{$GP['prob_name']}' already 
                              exists.<br />";
        }
    }
    if ($commit_error == '' && $GP['action'] == "modproblem") {
        $GP['prob_id'] = intval($GP['prob_id']);
        $r = dbquery("SELECT * FROM problems WHERE id={$GP['prob_id']}");
        if (count($r) == 0) {
            $commit_error .= "No such problem ID={$GP['prob_id']}.<br />";
        }
        else if ($r[0]['addedby'] != $_SESSION["$INST.user_id"] && 
                !$_SESSION["$INST.user_isadmin"]) {
            $commit_error .= "You are not the owner of this problem.<br />";
        }
    }
    if ($commit_error != '') {
        echo "Error: $commit_error";
        /*
        $problem = array(
            "name" => $GP['prob_name'],
            "desc" => $GP['prob_desc'],
            "inputtype" => $GP['prob_inputtype'],
            "checktype" => $GP['prob_checktype'],
            "scoretype" => $GP['prob_scoretype'],
        );
        if (isset($GP['prob_id'])) {
            $problem['id'] = $GP['prob_id'];
        }
        */
    }
    else if ($GP['action'] == "addproblem" || $GP['action'] == "modproblem") {
        if ($GP['action'] == "addproblem") {
            $query = "INSERT INTO problems (name, desc, 
                                            inputtype, checktype, scoretype,
                                            inputfile, outputfile,
                                            validatorfile, 
                                            timeout,
                                            dateadded, datemodified, 
                                            addedby, modifiedby,
                                            publicdate)
                      VALUES ('{$GP['prob_name']}', '{$GP['prob_desc']}', 
                              '{$GP['prob_inputtype']}', 
                              '{$GP['prob_checktype']}', 
                              '{$GP['prob_scoretype']}', 
                              '{$_FILES['prob_inputfile']['name']}', 
                              '{$_FILES['prob_outputfile']['name']}', 
                              '{$_FILES['prob_validatorfile']['name']}',
                              '{$GP['prob_timeout']}',
                              ".time().", ".time().", 
                              {$_SESSION["$INST.user_id"]}, 
                              {$_SESSION["$INST.user_id"]},
                              {$GP['prob_publicdate_epoc']})";
            dbquery($query);
            $query = "SELECT id FROM problems WHERE name='{$GP['prob_name']}'";
            $r = dbquery1($query);
            $GP['prob_id'] = $r['id'];
        }
        if ($GP['action'] == "modproblem") {
            if (!isset($GP['prob_id'])) {
                _die("Problem ID not set");
            }       
            $query = "UPDATE problems SET name='{$GP['prob_name']}',
                                          desc='{$GP['prob_desc']}',
                                          inputtype='{$GP['prob_inputtype']}',
                                          checktype='{$GP['prob_checktype']}',
                                          scoretype='{$GP['prob_scoretype']}',
                                          timeout={$GP['prob_timeout']},
                                          publicdate={$GP['prob_publicdate_epoc']},
                                          datemodified=".time().",
                                          modifiedby={$_SESSION["$INST.user_id"]}";
            if ($_FILES['prob_inputfile']['name'] != '') {
                $query .= ", inputfile='{$_FILES['prob_inputfile']['name']}'";
            }
            if ($_FILES['prob_outputfile']['name'] != '') {
                $query .= ", outputfile='{$_FILES['prob_outputfile']['name']}'";
            }
            if ($_FILES['prob_validatorfile']['name'] != '') {
                $query .= ", validatorfile='{$_FILES['prob_validatorfile']['name']}'";
            }
            $query .= " WHERE id={$GP['prob_id']}";
            dbquery($query);
        }
        $pdir = "$DATAPATH/problems/{$GP['prob_id']}";
        if (!file_exists($pdir)) {
            mkdir($pdir, 0770);
        }
        else if (!is_dir($pdir)) {
            die("$pdir is not a directory.");
        }
        foreach (array('prob_inputfile', 
                       'prob_outputfile', 
                       'prob_validatorfile') 
                as $file) {
            if ($_FILES[$file]['tmp_name'] != '') {
                move_uploaded_file($_FILES[$file]['tmp_name'],
                                   "$pdir/" . $_FILES[$file]['name']);
            }
        }
        if ($GP['action'] == "addproblem") {
            echo "
            <h2>Problem '{$GP['prob_name']}' added</h2>";
        }
        else {
            echo "
            <h2>Problem '{$GP['prob_name']}' modified</h2>";
        }
        $GP['action'] = 'listproblems';
    }
    if ($GP['action'] == 'delproblem') {
        if (!isset($GP['prob_id'])) {
            _die("Problem ID was not set");
        }
        $GP['prob_id'] = intval($GP['prob_id']);
        $problem = dbquery1("SELECT *
                             FROM problems 
                             WHERE id={$GP['prob_id']}");
        if ($problem['addedby'] != $_SESSION["$INST.user_id"]
                && !$_SESSION["$INST.user_isadmin"]) {
            _die("You are not the owner of this problem.");
        }
        $eps = dbquery("SELECT events.name 
                        FROM eventproblems
                        INNER JOIN events
                        ON eventproblems.probid = {$GP['prob_id']}
                        AND events.id = eventproblems.eventid");
        if (count($eps) > 0) {
            echo "
                Error: Problem '{$problem['name']}' is used in the event
                '{$eps[0]['events.name']}', and hence cannot be deleted.<br />";
        }
        else {
            dbquery("DELETE FROM problems
                     WHERE id={$GP['prob_id']}");
            echo "
                <h2>Problem '{$problem['name']}' deleted</h2>";
        }
        $GP['action'] = 'listproblems';
    }
    if ($GP['action'] == 'listproblems') {
        echo "
        <h2>Problem list</h2>";
        if (isset($_SESSION["$INST.user_id"]) &&
                ($_SESSION["$INST.user_isadmin"])) {
            echo '	
        <p>
            Add <a href="?action=newproblem">new problem</a>.
        </p>';
        }
        echo '
        <table>
            <tr>
                <th>Problem name</th>
                <th></th>
                <th></th>
                <th>Start</th>';
        if ($_SESSION["$INST.user_isadmin"]) {
            echo '
                <th>Added by</th>
                <th>Date added</th>
                <th>Date updated</th>';
        } 
        echo '
                <th></th>
            </tr>';
        $problems = dbquery("SELECT problems.*, users.name 
                             FROM problems INNER JOIN users 
                             ON problems.addedby=users.id
                             ORDER BY problems.name");
        foreach ($problems as $p) {
            if ($p['problems.publicdate'] <= time() || 
                    (isset($_SESSION["$INST.user_id"]) &&
                         $p['problems.addedby'] == $_SESSION["$INST.user_id"]) ||
                    $_SESSION["$INST.user_isadmin"]) {
                $p['problems.publicdate'] = mydate($p['problems.publicdate']);
                $mayalter = (isset($_SESSION["$INST.user_id"])
                        && $p['problems.addedby'] == $_SESSION["$INST.user_id"])
                    || $_SESSION["$INST.user_isadmin"];
                echo '
                <tr>
                    <td>';
                //if (isset($_SESSION["$INST.user_id"])) {
                echo '
                        <a href="run.php?prob_id='.$p['problems.id'].'">'
                        .$p['problems.name'].'</a>';
                //}
                //else {
                //    echo $p['problems.name'];
                //}
                echo '
                    </td>';
                if ($mayalter) {
                    echo '
                    <td><a href="?action=editproblem&amp;prob_id='.$p['problems.id']
                        .'">Edit</a></td>';
                }
                else {
                    echo '
                    <td></td>';
                }
                echo '
                    <td><a href="highscore.php?prob_id='.$p['problems.id'].'">Highscore</a></td>
                    <td>'.$p['problems.publicdate'].'</td>';
                if ($_SESSION["$INST.user_isadmin"]) {
                    echo '
                    <td>'.$p['users.name'].'</td>
                    <td>'.mydate($p['problems.dateadded']).'</td>
                    <td>'.mydate($p['problems.datemodified']).'</td>';
                }
                if ($mayalter) {
                    echo '
                    <td>
                        <a href="problem.php?action=delproblem&amp;prob_id='
                                .$p['problems.id'].'">Delete</a>
                    </td>';
                }
                echo '
                </tr>';
            }
        }
        echo '
        </table>';
    }
    if ($GP['action'] == "editproblem") {
        if (!isset($problem)) {
            assert(isset($GP['prob_id']));
            $problem = dbquery1("SELECT * FROM problems WHERE id={$GP['prob_id']}");
            $problem['desc'] = htmlspecialchars($problem['desc']);
        }
    }       
    else if ($GP['action'] == "newproblem") {
        if (!isset($problem)) {
            $problem = array(
                "name"          => "Enter name of problem. (Will be sorted alphabetically by this)",
                "desc"          => "Any HTML description. Could contain a link to another description.",
                "inputtype"     => "program",
                "checktype"     => "diff",
                "scoretype"     => "time", 
                "inputfile"     => "", 
                "outputfile"    => "", 
                "validatorfile" => "", 
                "timeout"       => "10.0", 
                "publicdate"    => time(), 
            );
        }
    }
    else if ($GP['action'] == "addproblem" || $GP['action'] == "modproblem") {
        $problem = dbquery1("SELECT * FROM problems WHERE id={$GP['prob_id']}");
        foreach ($GP as $k => $v) { 
            if (preg_match("/prob_(.*)/", $k, $m)) {
                $problem[$m[1]] = $v;
            }
        }
        if ($GP['action'] == "addproblem") {
            $GP['action'] = "newproblem";
        }
        else if ($GP['action'] == "modproblem") {
            $GP['action'] = "editproblem";
        }
    }
    if ($GP['action'] == "editproblem" || $GP['action'] == "newproblem") {
        if ($GP['action'] == "editproblem") {
            echo '
        <h2>Modify problem</h2>';
        }
        else if ($GP['action'] == "newproblem") {
            echo '
        <h2>Add new problem</h2>
        <p style="color: red">
            If the problem is part of an event (such as "AKVK 2006"), 
            remember to at it to the event at once. If you don\'t, some
            contestants runs might not be counted in the event.
        </p>';
        }
        echo '
        <form action="problem.php" method="post" enctype="multipart/form-data">
            <p>';
        if ($GP['action'] == "editproblem") {
            echo '
                <input type="hidden" name="action" value="modproblem" />
                <input type="hidden" name="prob_id" value="'.$problem['id'].'" />';
        }
        else if ($GP['action'] == "newproblem") {
            echo '
                <input type="hidden" name="action" value="addproblem" />';
        }
        echo '
            </p>
            <table>
                <tr>
                    <td>Problem name:</td>
                    <td><input type="text" name="prob_name" size="40"
                            value="' .$problem['name'] .'" />
                    </td>
                </tr>
                <tr>
                    <td>Description:</td>
                    <td>
                        <textarea cols="80" rows="10" name="prob_desc">'
                                .$problem['desc']
                        .'</textarea>
                    </td>
                </tr>
                <tr>
                    <td>Input type:</td>
                    <td>'.HTMLselect('prob_inputtype', 
                                     array('program' => 'User program', 
                                           'text' => 'Flat text'),
                                     $problem['inputtype']).'
                    </td>
                </tr>
                <tr>
                    <td>Check type:</td>
                    <td>'.HTMLselect('prob_checktype', 
                                     array('diff' => 'System diff', 
                                           'validator' => 'Validator',
                                           'pipevalidator' => 'Validator (pipe)'),
                                     $problem['checktype']).'
                    </td>
                </tr>
                <tr>
                    <td>Score type:</td>
                    <td>'.HTMLselect('prob_scoretype', 
                                     array('runtime' => $SCORE_EXP['runtime'],
                                           'validator' => $SCORE_EXP['validator'],
                                           'solvetime' => $SCORE_EXP['solvetime']),
                                     $problem['scoretype']).'
                    </td>
                    <td>
                        <i>If "Input type" = "Flat text", 
                        "Score type" cannot be "Run time".</i><br />
                    </td>
                </tr>
                <tr>
                    <td>Test input file:</td>
                    <td>
                        <input type="file" name="prob_inputfile" />
                        Now: <code>'.$problem['inputfile'].' </code>
                    </td>
                    <td>
                    </td>
                </tr>
                <tr>
                    <td>Test output file:</td>
                    <td>
                        <input type="file" name="prob_outputfile" />
                        Now: <code>'.$problem['outputfile'].' </code>
                    </td>
                    <td>
                    </td>
                </tr>
                <tr>
                    <td>Validator program:</td>
                    <td>
                        <input type="file" name="prob_validatorfile" />
                        Now: <code>'.$problem['validatorfile'].' </code>
                    </td>
                    <td>
                        <i>Used for for "Check type" = "From validator".</i>
                    </td>
                </tr>
                <tr>
                    <td>Problem timeout:</td>
                    <td><input type="text" name="prob_timeout" size="20"
                            value="' .$problem['timeout'] .'" />
                        (In seconds. Do not set too high!)
                    </td>
                    <td>
                        <i>Used for "Input type" = "User program"</i>.
                    </td>
                </tr>
                <tr>
                    <td>Make public:</td>
                    <td>
                        <input type="text" name="prob_publicdate" size="20"
                            value="'.mydate($problem['publicdate']).'" />
                    </td>
                </tr>
                <tr>
                    <td>
                    </td>
                    <td>
                        <br />
                        <input type="submit" value="Submit" />
                    </td>
                </tr>
            </table>
        </form>
        <ul>
            <li>The validator must output a single number.
                A negative number indicates failure, while a
                non-negative (real) number gives the score.
            </li>
            <li>Validator: The validator will be input the concatenation of the 
                problem input and the user answer, separated by a newline.
            </li>
            <li>Validator (pipe): The validator using pipes will receive as its first
                argument a command to run the user program, and its input will 
                be given on standard in. 
            </li>
            <li>Score for "Run time" is milli-seconds
            </li> 
        </ul>
        <h3>Piped validator example</h3>
        <pre>
# Notice that the pipes must be flushed aften writes to make sure the data
# is received!

import subprocess
import sys

try:
    # The first argument to this script is a command to run the user program,
    # which must be split into separate arguments to be run as a subprocess.
    # Example: "java Foo.java" --> "java" "Foo.java"
    program = subprocess.Popen(sys.argv[1].split(), stdin=subprocess.PIPE,
                               stdout=subprocess.PIPE, stderr=subprocess.PIPE)

    a, b = [int(x) for x in sys.stdin.readline().split()]
    program.stdin.write(\'%d %d\n\' % (a, b))
    program.stdin.flush()
    c  = int(program.stdout.readline().strip())
    if c == a + b:
        print 1
    else:
        print -1
except:
    print -1
</pre>
    ';
    }

    include($footer);

?>
