<?
    $INST = 'open09';

    include_once("/home/algdat/open09/include_html/defs.php");
    include_once("/home/algdat/open09/include_html/db.php");
    include_once("/home/algdat/open09/include_html/highscorefunc.php");

    $event_id = 1;

    $LOCTYPES = array('all', 'onsite', 'online');
    $TEAMTYPES = array('all', 'student', 'pro');

    function gentypearray() {
        global $LOCTYPES, $TEAMTYPES;
        $ret = array();
        foreach ($LOCTYPES as $loctype) {
            $ret[$loctype] = array();
            foreach ($TEAMTYPES as $teamtype) {
                $ret[$loctype][$teamtype] = NULL;
            }
        }
        return $ret;
    }
    
    $PLSTRS = gentypearray();
    $PLSTRS['all']['all'] = 'Placement open class';
    $PLSTRS['onsite']['student'] = 'Placement on-site student class';
    $PLSTRS['onsite']['pro'] = 'Placement on-site pro class';

    $placements = array();
    foreach ($LOCTYPES as $loctype) {
        foreach ($TEAMTYPES as $teamtype) {
            $high = eventhigh($event_id, $loctype, $teamtype, true);
            $num = 0;
            foreach ($high['userscore'] as $h) {
                $num = $h[0];
                $res = $h[1];
                if (!isset($placements[$res['id']])) {
                    $placements[$res['id']] = gentypearray();
                }
                $placements[$res['id']][$loctype][$teamtype] = $num;
            }
        }
    }

    $mal = file_get_contents("mal.tex");

    $R = array(
            '&quot;' => '"',
            '&amp;' => '&',
            '&#039;' => "'",
            '{'     => '\\{',
            '}'     => '\\}',
            '_'     => '\\_', 
            '&'     => '\\&',
            '^'     => '\\^{}', 
            '~'     => '\\~{}',
            chr(195).chr(134) => '{\\AE}',
            chr(195).chr(166) => '{\\ae}',
            chr(195).chr(152) => '{\\O}',
            chr(195).chr(184) => '{\\o}',
            chr(195).chr(133) => '{\\AA}',
            chr(195).chr(165) => '{\\aa}',
            chr(195).chr(169) => '{\\\'e}',
            /*
            chr(196).chr(177) => chr(253),
            chr(196).chr(176) => chr(221),
            chr(195).chr(182) => chr(246),
            chr(195).chr(150) => chr(214),
            chr(195).chr(167) => chr(231),
            chr(195).chr(135) => chr(199),
            chr(197).chr(159) => chr(254),
            chr(197).chr(158) => chr(222),
            chr(196).chr(159) => chr(240),
            chr(196).chr(158) => chr(208),
            chr(195).chr(188) => chr(252),
            chr(195).chr(156) => chr(220),
            */
    );

    $tnum = 0;

    foreach (array(array('onsite', 'student'), array('onsite', 'pro')) as $type) {
        $loctype = $type[0];
        $teamtype = $type[1];

        $high = eventhigh($event_id, $loctype, $teamtype, true);

        foreach ($high['userscore'] as $h) {
            $tnum += 1;
            $place = $h[1];
            $members = dbquery("SELECT *
                                FROM members 
                                WHERE teamid={$place['id']}");
            /*
            echo "$tnum " .
                 "{$placements[$place['id']][$loctype][$teamtype]} " .
                 "{$place['name']} " .
                 "{$members[0]['name']} " .
                 "{$members[1]['name']} " .
                 "{$members[2]['name']}\n";
            */
            /*
            if ($tnum == 48) {
                for ($i = 0; $i < strlen($members[0]['name']); $i++) {
                    $c = $members[0]['name'][$i];
                    echo "($c) ". ord($c) . "\n";
                }
            }
            */
            $texdata = $mal;
            $S = array(
                    "LAGNAVN" => $place['name'],
                    "PERSON1" => $members[0]['name'],
                    "PERSON2" => $members[1]['name'],
                    "PERSON3" => $members[2]['name'],
                    "PL1"     => $placements[$place['id']][$loctype][$teamtype],
                    "PL2"     => $placements[$place['id']]['all']['all'],
                    "PLSTR1"  => $PLSTRS[$loctype][$teamtype],
                    "PLSTR2"  => $PLSTRS['all']['all'],
            );
            foreach ($S as $k => $v) {
                foreach ($R as $f => $t) {
                    /*
                    if ('&quot;' == $f) {
                        echo "($f) ($t) ($v) (" .str_replace($f, $t, $v) . ")\n";
                    }
                    */
                    $v = str_replace($f, $t, $v);
                }
                $S[$k] = $v;
            }
            $fp = fopen(sprintf("output/dip%03d.tex", $tnum), "w");
            foreach (explode("\n", $texdata) as $line)  {
                $line = utf8_decode($line);
                foreach ($S as $w => $d) {
                    $d = trim($d);
                    if (strpos($line, $w) !== FALSE) {
                        if ($d != '') {
                            $line = str_replace($w, $d, $line);
                        } else {
                            $line = '';
                        }
                    }
                }
                fwrite($fp, "$line\n");
            }
            fclose($fp);
        }
    }
?>
