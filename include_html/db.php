<?
    ifmkdir("$DATAPATH/db");
    $DB_FILE = "$DATAPATH/db/db.sqlite";
    $db = sqlite_open($DB_FILE);

    function dbquery($query) {
        global $db;
        $res = sqlite_array_query($db, $query, SQLITE_ASSOC);
        if ($res === FALSE) {
            _p("Database query failed:");
            _p(preg_replace("/\n\\s+/", "\n", $query));
            _die("Dying.");
        }
        return $res;
    }

    function dbquery1($query) {
        $r = dbquery($query);
        if (count($r) == 0) {
            _p("No hit on this database query:");
            _p(preg_replace("/\n\\s+/", "\n", $query));
            _die("Dying.");
        }
        return $r[0];
    }

    function _dbcreate($name, $def) {
        return "create table $name ($def)";
    }

    $DB_CHECK = TRUE;
    $DB_FIX = FALSE;
    
    $DB_TABLES = array(
        // would use AUTOINCREMENT on keys if we had sqlite3
        // FIXME add pipevalidator to checktype comment
        "events" => "
            id            INTEGER PRIMARY KEY,
            name          VARCHAR(255) UNIQUE NOT NULL,
            desc          TEXT,
            scoretype     VARCHAR(20) NOT NULL, /* {fromproblem, eventtime} */
            penalty       REAL NOT NULL,
            dateadded     INTEGER(8) NOT NULL,
            datemodified  INTEGER(8) NOT NULL,
            addedby       INTEGER NOT NULL,
            modifiedby    INTEGER NOT NULL,
            start         INTEGER(8) NOT NULL,
            end           INTEGER(8) NOT NULL
        ",
        "eventproblems" => "
            eventid       INTEGER NOT NULL,
            probid        INTEGER NOT NULL,
            number        INTEGER NOT NULL,
            PRIMARY KEY   (eventid, probid)
        ",
        "problems" => "
            id            INTEGER PRIMARY KEY,
            name          VARCHAR(255) UNIQUE NOT NULL,
            desc          TEXT,
            inputtype     VARCHAR(20) NOT NULL, /* {program, text} */
            checktype     VARCHAR(20) NOT NULL, /* {diff, validator} */
            scoretype     VARCHAR(20) NOT NULL, /* {runtime, validator, solvetime} */
            inputfile     VARCHAR(255),
            outputfile    VARCHAR(255),
            validatorfile VARCHAR(255),
            timeout       REAL NOT NULL,
            dateadded     INTEGER(8) NOT NULL,
            datemodified  INTEGER(8) NOT NULL,
            addedby       INTEGER NOT NULL,
            modifiedby    INTEGER NOT NULL,
            publicdate    INTEGER(8) DEFAULT 0 NOT NULL
            ",
        /*
        "users" => "
            id            INTEGER PRIMARY KEY,
            nick          VARCHAR(255) UNIQUE NOT NULL,
            realname      VARCHAR(255) NOT NULL,
            email         VARCHAR(255) NOT NULL,
            password      VARCHAR(255) NOT NULL,
            isteam        INTEGER DEFAULT 0 NOT NULL,
            isadmin       INTEGER DEFAULT 0 NOT NULL
            ",
        */
        "users" => "
            id            INTEGER PRIMARY KEY,
            login         VARCHAR(255) UNIQUE NOT NULL,
            password      VARCHAR(255) NOT NULL,
            name          VARCHAR(255) NOT NULL,
            isadmin       INTEGER DEFAULT 0 NOT NULL,
            loctype       VARCHAR(255) NOT NULL,
            teamtype      VARCHAR(255) NOT NULL
            ",
        "members" => "
            teamid        INTEGER NOT NULL,
            memnum        INTEGER NOT NULL,
            name          VARCHAR(255) NOT NULL,
            email         VARCHAR(255) NOT NULL,
            grade         INTEGER NOT NULL,
            belong        VARCHAR(255) NOT NULL
            ",
        "runs" => "
            eventid       INTEGER NOT NULL,
            probid        INTEGER NOT NULL,
            userid        INTEGER NOT NULL,
            lang          VARCHAR(20) NOT NULL,
            score         REAL,
            tries         INTEGER NOT NULL,
            bestprogram   VARCHAR(255) NOT NULL, 
            PRIMARY KEY (eventid, probid, userid, lang)
        ",
        "news" => "
            date          INTEGER(8) PRIMARY KEY,
            content       TEXT NOT NULL
        ",
        "clarifications" => "
            id            INTEGER PRIMARY KEY,
            event_id      INTEGER NOT NULL,
            request       TEXT NOT NULL,
            requestdate   INTEGER(8) NOT NULL,
            requestby     INTEGER NOT NULL,
            answer        TEXT DEFAULT NULL,
            answerdate    INTEGER(8) DEFAULT NULL,
            answerby      INTEGER DEFAULT NULL
        ",
    );
                
    foreach ($DB_TABLES as $name => $def) {
        $q = "SELECT * FROM sqlite_master WHERE name='$name'";
        $r = dbquery($q);
        if (count($r) == 0) {
            dbquery(_dbcreate($name, $def));
        }
        else if ($DB_CHECK && $r[0]['sql'] != _dbcreate($name, $def)) {
            echo "
                <p>
                    Wrong definition of table $name:
                </p>
                <pre>'".$r[0]['sql']."' != '\n"._dbcreate($name, $def)."'</pre>";
            if ($DB_FIX) {
                dbquery("DROP table $name");
                dbquery(_dbcreate($name, $def));
            }
            else {
                die("Giving up...");
            }
        }
    }
?>
