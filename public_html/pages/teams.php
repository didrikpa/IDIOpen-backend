<h1>Registered teams</h1>
<!--<p>None at this time</p>-->
<?php
	
	$before_first_grade="<1";

	//perform query
	$query = "SELECT team, fullname, studyyear FROM User ORDER BY team";
	$result = mysql_query($query,$conn);
	
	$teams = array();
	
	while($row = mysql_fetch_array($result)){
		$team = $row['team'];
		if(!array_key_exists($team, $teams)){
			$teams[$team] = array();
		}
		array_push($teams[$team], $row['fullname'], $row['studyyear']);
	}
	
        
        echo '<p>There are currently '.(count($teams)-1).' teams registered for IDI Open.</p><br>';

	if(count($teams)>0){
	
		foreach($teams as $team_name=>$info){
			//read teamname and persons;
                        if($team_name=="Judges"||$team_name=="Site_Admin")continue;
			$person1 = null;
			$person1_grade;
			if(count($info)>=2){
				$person1 = $info[0];
				$person1_grade = $info[1];
			}

			$person2 = null;
			$person2_grade;
			if(count($info)>=4){
				$person2 = $info[2];
				$person2_grade = $info[3];
			}
			$person3 = null;
			$person3_grade;
			if(count($info)>=6){
				$person3 = $info[4];
				$person3_grade = $info[5];
			}
			
			$team_grade = ")";
			if($person3!=null){
				if($person3_grade==0){
					$person3_grade = "Pro";
				}
				else if ($person3_grade==-1)	
					$person3_grade=$before_first_grade;

				$team_grade = ", " . $person3_grade . $team_grade;
			}
			if($person2!=null){
				if($person2_grade==0){
					$person2_grade = "Pro";
				}
				else if ($person2_grade==-1)
                                	$person2_grade=$before_first_grade;

				$team_grade = ", " . $person2_grade . $team_grade;
			}
			if($person1_grade==0){
				$person1_grade = "Pro";
			}
			else if ($person1_grade==-1)
				$person1_grade=$before_first_grade;
			$team_grade = "(" . $person1_grade . $team_grade;
		       	 
			echo "<p>";
			echo "<span class=\"teams_team\">" . htmlentities($team_name) . "</span> <span class=\"teams_team_grade\">" . $team_grade . "</span><br/>";
			$persons = "";
			if($person3!=null){
				$persons = ", " . $person3 . $persons;
			}
			if($person2!=null){
				$persons = ", " . $person2 . $persons;
			}
			$persons = $person1 . $persons;
			echo "<span class=\"teams_person\">" . $persons . "</span>";
			echo "</p>";
		}
	} else {
		echo "<p>No teams are registered yet. Go <a href=\"theindex.php?page=register\">here</a> to register.</p>";
	}
?>
