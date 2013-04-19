<?
   require_once("db.php");
    function mydate($timestamp) {
        global $DATE_FORMAT;
        return date($DATE_FORMAT, $timestamp);
    }

    function _p($what)
    {
        echo "<pre>" . print_r($what, true) . "</pre>";
    }

    function _die($reason) {
        echo "</p></pre></tr></td></table>";
        die("FATAL ERROR: $reason");
    }

    function HTMLselect($name, $choice, $default) {
        $t = '<select name="'.$name.'">';
        foreach ($choice as $k => $v) {
            $t .= '<option value="'.$k.'" '. 
                        ($default == $k ? 'selected="selected" ' : '') . '>'
                        .$v.'</option>';
        }
        $t .= '</select>';
        return $t;
    }

    function ifmkdir($d) {
        if (!file_exists($d)) {
            if (!mkdir($d, 0770)) {
                _die("Could not create directory $d");
            }
        }
        else if (!is_dir($d)) {
            _die("'$d' is not a directory.");
        }
    }

    function filelist($sourcedir, $extension=false){
        if (! ($h = @opendir($sourcedir))) { 
            return array(); 
        }
        $dir = array();
        $file_desc = array();
        while (false !== ($file = readdir($h))) {
            if ($extension !== false) {
                $dotIndex = strrpos($file, '.');
                $lastname = ($dotIndex === false ? '' : substr($file, $dotIndex+1));
                if ($lastname != $extension) {
                    continue;
                }
            }
            $file_desc[0] = $file;
            $date = filemtime("$sourcedir/$file");
            if ($date === false) {
                $file_desc[1] = "Unknown date";
            }
            else {
                $file_desc[1] = date("d.m.Y H:i", $date);
            }
            $file_desc[2] = filesize("$sourcedir/$file");
            if ($file != "." && $file != ".." && is_file("$sourcedir/$file")) {
                array_push($dir, $file_desc);
            }
        }
        return $dir;
    }

    function dolog($text) {
        global $LOGFILE;
        $fp = fopen($LOGFILE, 'a');
        fwrite($fp, "$text\n");
        fclose($fp);
    }

    function cleanformstring($str) {
        //_p($str);
        $str = preg_replace('/\\\"/', '"', $str);
        //_p($str);
        $str = preg_replace("/\\\'/", "&#039;", $str);
        //_p($str);
        return $str;
    }

    function cleanformstringnohtml($str) {
        //_p($str);
        $str = cleanformstring($str);
        //_p($str);
        $str = htmlspecialchars($str, ENT_QUOTES);
        //_p($str);
        $str = preg_replace("/&amp;#039;/", "&#039;", $str);
        //_p($str);
        return $str;
    }

    function getbackupname($backupdir, $filename)
    {
        # find taken numbers
        $takennumbers = array();
        if (file_exists($backupdir)) {
            if ($dh = opendir($backupdir)) {
                while (($_file = readdir($dh)) !== false) {
                    if (strlen($_file) - 4 == strlen($filename)) {
                        if (substr($_file, 0, strlen($filename)) == $filename) {
                            $takennumbers[] = substr($_file, -3);
                        }
                    }
                }
                closedir($dh);
            }
        }
        rsort($takennumbers);
        reset($takennumbers);

        # create filename with non-taken number
        $num = intval(current($takennumbers)) + 1;
        return "$filename." . str_pad($num, 3, STR_PAD_LEFT, '0');
    }

    function eventinfo($event_id)
    {
        return dbquery1("SELECT *
                         FROM events
                         WHERE id=$event_id");
    }

    function selectevent($event_id, $event_name = false)
    {
        global $INST;
        $event = eventinfo($event_id);
        $_SESSION["$INST.event_id"] = $event_id;
        $_SESSION["$INST.event_name"] = $event['name'];
        $_SESSION["$INST.event_start"] = $event['start'];
        return $event;
    }

    function probleminfo($prob_id)
    {
        return dbquery1("SELECT *
                         FROM problems
                         WHERE id=$prob_id");
    }

    function selectproblem($prob_id, $prob_name = false)
    {
        global $INST;
        $problem = probleminfo($prob_id);
        $_SESSION["$INST.prob_id"] = $prob_id;
        $_SESSION["$INST.prob_name"] = $problem['name'];
        return $problem;
    }
?>
