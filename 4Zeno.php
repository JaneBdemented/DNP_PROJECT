/*****************************************************************
	offTrackShed() creats a conflict free time table from
	a list of courses that the user has the required pre-req's for. 
*****************************************************************/
function offTrackShed($Classes,$sem,$complementery){
	$timeE = newTimeTable();
	$timeO = newTimeTable();
	$shed_elec = array();
	$servername = "localhost";
	$username = "root";
	$password = ""; //<-your password
	$DNPDB = "DNP_PROJECT";
	$conn = new mysqli($servername, $username, $password, $DNPDB);
		// Check connection
	if ($conn->connect_error) {
		echo("Connection failed: " . $conn->connect_error);	
	}	
	shuffle($Classes);
	for($i=0;$i<6;$i++){
			$index = 0;
			if($which == 0){
			$list = $Classes[$index];
			shuffle($list);
			}
			$compTemp=NULL;
			$match = 0;
		while($match == 0){
				$stmt = $conn->prepare("SELECT SUB_VAL, TYPE, DAYS, time_s, time_e,ROOM_CAP FROM Course_Timing Where course_id = ?");
				$stmt->bind_param("i",$list);
				$stmt->execute();
				$stmt->bind_result($sec,$type,$days,$s_time,$e_time,$space);
				$stmt->fetch();
				$stmt->close();
				$stmt = $conn->prepare("SELECT SUBJ, CRSE_NUM, NAME, SMSTR, HAS_LAB ,HAS_TUT FROM Courses WHERE course_id = ?");
  				$stmt->bind_param("i", $list);
				$stmt->execute();
				$stmt->bind_result($subj,$courseNum,$courseName,$semester,$lab,$tut);
				$stmt->fetch();
				$stmt->close();			
				$day = str_split($days);
				for($l=0;$l<count($complementery);$l++){
					if($list==$complementery[$l]){
						$compTemp = $complementery[$l]
					}
				}
				if(is_null($compTemp)){
					if(is_null($day)||is_null($days)||($type!='LEC')){
					}elseif(count($day)>1 && $semester == $sem){
				 		if(in_array($s_time,$timeE[$day[0]])&&in_array($e_time,$timeE[$day[0]])&&in_array($s_time,$timeE[$day[1]])&&in_array($s_time,$timeE[$day[1]])
				 			&&in_array($s_time,$timeO[$day[0]])&&in_array($e_time,$timeO[$day[0]])&&in_array($s_time,$timeO[$day[1]])&&in_array($s_time,$timeO[$day[1]])&&$space>0){
				 			cleartime($s_time,$e_time,$timeE,$timeO,$day[0],'B');
				 			cleartime($s_time,$e_time,$timeE,$timeO,$day[1],'B');
				 			$match=1;
				 			$shed_elec[] = array($list[$j],$subj." ".$courseNum, $courseName,$sec,$type,$days,$s_time,$e_time,$space,'B');
				 			break;
				 		} 	
					}elseif(count($day)==1&&$days!=NULL&&in_array($s_time,$timeE[$day[0]])&&in_array($e_time,$timeE[$day[0]])&&in_array($s_time,$timeO[$day[0]])&&in_array($s_time,$timeO[$day[0]])
							&&$semester==$sem&&$space>0){
						cleartime($s_time,$e_time,$timeE,$timeO,$day[0],'B');
						$shed_elec[] = array($list[$j],$subj." ".$courseNum, $courseName, $sec,$type,$days,$s_time,$e_time,$space,'B');
				 		$match = 1;
				 		break;

					}
				}else{
					if(is_null($day)||is_null($days)||($type!='LEC')){
					}elseif(count($days)==1){	
							if($compTemp[8]=='O')){
								if((in_array($compTemp[5],$timeO[$compTemp[4]])&&in_array($compTemp[6],$timeO[$compTemp[4]]))&&($compTemp[7]>=1)){
											
									$shed_elec[] = array($list,$subj." ".$courseNum, $courseName, $sec,$type,$days,$s_time,$e_time,$space,'B');
									array_push($shed_elec, $compTemp);									
									cleartime($s_time,$e_time,$timeE,$timeO,$day[0],'B');
									cleartime($compTemp[5],$compTemp[6],$timeE,$timeO,$comp[4],'O');
									if(count($day)>1){											
										cleartime($s_time,$e_time,$timeE,$timeO,$day[1],'B');					
									}
										
									$NoMatch=1;
									break;
										
								}elseif($compTemp[8]=='E'){
										if((in_array($compTemp[5],$timeE[$compTemp[4]])&&in_array($compTemp[6],$timeE[$compTemp[4]]))&&($compTemp[7]>=1)){
										
											$shed_elec[] = array($list,$subj." ".$courseNum, $courseName, $sec,$type,$days,$s_time,$e_time,$space,'B');
											array_push($shed_elec, $compTemp);
											cleartime($s_time,$e_time,$timeE,$timeO,$day[0],'B');
											cleartime($compTemp[5],$compTemp[6],$timeE,$timeO,$comp[4],'E');
											if(count($day)>1){											
												cleartime($s_time,$e_time,$timeE,$timeO,$day[1],'B');					
											}
										
											$NoMatch=1;
											break;
										}
								}elseif((in_array($compTemp[5],$timeO[$compTemp[4]])&&in_array($compTemp[6],$timeO[$compTemp[4]]))&&
										(in_array($compTemp[5],$timeE[$compTemp[4]])&&in_array($compTemp[6],$timeE[$compTemp[4]]))&&($compTemp[7]>=1)){

									$shed_elec[] = array($list,$subj." ".$courseNum, $courseName, $sec,$type,$days,$s_time,$e_time,$space,'B');
									array_push($shed_elec, $compTemp);
									cleartime($s_time,$e_time,$timeE,$timeO,$day[0],'B');
									cleartime($compTemp[5],$compTemp[6],$timeE,$timeO,$comp[4],'B');
									if(count($day)>1){											
										cleartime($s_time,$e_time,$timeE,$timeO,$day[1],'B');					
									}
									
									$NoMatch=1;
									break;
								}else{
									$NoMatch=0;
								}
						}else{
							if((in_array($compTemp[5],$timeO[$compTemp[4]])&&in_array($compTemp[6],$timeO[$days[0]]))&&
							   (in_array($compTemp[5],$timeE[$compTemp[4]])&&in_array($compTemp[6],$timeE[$days[0]]))&&
							   (in_array($compTemp[5],$timeO[$compTemp[4]])&&in_array($compTemp[6],$timeO[$days[1]]))&&
							   (in_array($compTemp[5],$timeE[$compTemp[4]])&&in_array($compTemp[6],$timeE[$days[1]]))&&($compTemp[7]>=1)){
									$shed_elec[] = array($list[$j],$subj." ".$courseNum, $courseName,$sec,$type,$days,$s_time,$e_time,$space,'B');
									array_push($shed_elec, $compTemp);
									cleartime($s_time,$e_time,$timeE,$timeO,$day[0],'B');
									cleartime($compTemp[5],$compTemp[6],$timeE,$timeO,$day[0],'B');
									cleartime($compTemp[5],$compTemp[6],$timeE,$timeO,$day[1],'B');
									if(count($day)>1){											
										cleartime($s_time,$e_time,$timeE,$timeO,$day[1],'B');					
									}
					   		}
						}
					}
				}
			}	
		}	
	}			
	$conn->close();
	if(count($shed_elec) != 6){
		$shed_elec = offTrackShed($Classes,$sem,$complementery);
	}
	return($shed_elec);
}
