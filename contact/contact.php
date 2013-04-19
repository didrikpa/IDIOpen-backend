<?
    $INST = 'open07';

    include_once("/home/algdat/open07/include_html/defs.php");
    include_once("/home/algdat/open07/include_html/db.php");
    include_once("/home/algdat/open07/include_html/highscorefunc.php");

    $event_id = 1;

    $R = array(
            '&quot;' => '"',
            '&amp;' => '&',
            '&#039;' => "'",
            //'{'     => '\\{',
            //'}'     => '\\}',
            //'_'     => '\\_', 
            //'&'     => '\\&',
            //'^'     => '\\^{}', 
            //'~'     => '\\~{}',
            chr(195).chr(134) => 'Æ',
            chr(195).chr(166) => 'æ',
            chr(195).chr(152) => 'Ø',
            chr(195).chr(184) => 'ø',
            chr(195).chr(133) => 'Å',
            chr(195).chr(165) => 'å',
            chr(195).chr(169) => 'é',
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

    $high = eventhigh($event_id, 'all', 'all', false);

    printf("PLACE SOLVED TEAMTYPE LOCATION TEAMNAME                                     MEMBERS\n");
    foreach ($high['userscore'] as $h) {
        $members = dbquery("SELECT *
                            FROM members 
                            WHERE teamid={$h[1]['id']}");
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
                "LAGNAVN" => $h[1]['name'],
                "PERSON0" => $members[0]['name'],
                "PERSON1" => $members[1]['name'],
                "PERSON2" => $members[2]['name'],
        );
        foreach ($S as $k => $v) {
            foreach ($R as $f => $t) {
                $v = str_replace($f, $t, $v);
            }
            $S[$k] = $v;
        }
        printf("%2d    %2d     %-7s  %-7s  %-45s",
            $h[0],
            $h[1]['solved'],
            $h[1]['teamtype'],
            $h[1]['loctype'],
            $S['LAGNAVN']
        );
        for ($i = 0; $i < 3; $i++) {
            if ($members[$i]['email'] != '') {
                printf("%s <%s>, ",
                    $S["PERSON$i"],
                    $members[$i]['email']
                );
            }
        }
        printf("\n");
    }
?>
