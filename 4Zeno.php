<link rel="stylesheet" type="text/css" href="style.css">
	<script src="jsFunctions.js"></script>
	
	
	<?php
/********************************************************************************************************
	Retriving information passed to Iframe
********************************************************************************************************/	
		$Name = $_GET["user_ID"];
		$Std_num = intval($_GET["STD_NUM"]);
		$Stream = $_GET["Stream"];
		$year = intval($_GET["year"]);

/********************************************************************************************************
	Connecting to Database 
********************************************************************************************************/				
		$servername = "localhost";
		$username = "root";
		$password = ""; //<-your password
		$DNPDB = "DNP_PROJECT";
		// Create connection
		$conn = new mysqli($servername, $username, $password, $DNPDB);
		// Check connection
		if ($conn->connect_error) {
		echo("Connection failed: " . $conn->connect_error);	
		}
/********************************************************************************************************
	Converting user entered information to known database variabls
********************************************************************************************************/			
		
		if ($stmt = $conn->prepare("SELECT stream_id FROM Stream WHERE stream=?")){
			$stmt->bind_param("s",$Stream);
			$stmt->execute();
			$stmt->bind_result($Stream_id);
			$stmt->fetch();
			$stmt->close();
		}
/********************************************************************************************************
	Inshalizing user specific database tables
********************************************************************************************************/			
		if($stmt = $conn->prepare("DELETE FROM `TempUser_Courses` WHERE 1")){
			$stmt->execute();
			$stmt->close();
		}
		//if ($stmt = $conn->prepare("INSERT INTO User (Stu_Num, Stream_id, Year) VALUES (?,?,?)")){
		//	$ret = $stmt->bind_param("iii",$Std_num, $stream_id, $year);
		//	$stmt->execute();
		//}
		//else 
		//{
		//		    printf('errno: %d, error: %s', $conn->errno, $conn->error);
		//}		
	
/********************************************************************************************************
	SQL statments 
********************************************************************************************************/		

  		$sqlAll = "INSERT INTO TempUser_Courses (course_id) SELECT course_id FROM Stream_Courses WHERE (YEAR = ? AND stream_id = ?)";		
  		$sql_read = "SELECT SUBJ, CRSE_NUM, NAME, SMSTR FROM Courses WHERE course_id = ?";
  		$sqlLab = "SELECT Lec_Complement.Lab_id, Lec_Complement.course_id FROM Lec_Complement JOIN TempUser_Courses ON Lec_Complement.course_id=TempUser_Courses.course_id";
  		$sqlCombo = "DELETE FROM TempUser_Courses WHERE course_id = ?";
  		$sqlLec = "SELECT course_id FROM TempUser_Courses WHERE 1";
  		$sqlTiming = "SELECT SUB_VAL, TYPE, DAYS, time_s, time_e,ROOM_CAP FROM Course_Timing Where course_id = ?";
 		$sqlTree = "SELECT classes, semester, Type From Stream_Tree WHERE stream_id = ? AND year = ?";
 		$sqlElec = "SELECT course_id FROM Stream_Courses WHERE type_id = ?";

/********************************************************************************************************
	Retriving from database all revelent classes for stream year combo and storing into 
	database table TempUser_Courses
********************************************************************************************************/	  			
  			
  			$stmt = $conn->prepare($sqlAll);
  			$stmt->bind_param("ii",$year,$Stream_id);
			$stmt->execute();
			$stmt->close();

/********************************************************************************************************
	Pulling short-list of fall and winter courses for stream year combo from database.
	Note '0' indicates 'core' corses.
********************************************************************************************************/
			$TreeListFall=array();
			$TreeListWinter=array();
			$EleListFall=array();
			$EleListWinter=array();
			$stmt = $conn->prepare($sqlTree);
			$stmt->bind_param("ii",$Stream_id, $year);
			$stmt->execute();
			$stmt->bind_result($cla,$sem,$kind);
			while($stmt->fetch()){

				if($sem == 0){
					if($kind == 0){
						$TreeListFall[]=$cla;
					}else{
						$EleListFall[]=$kind;
					}
				}else{
					if($kind==0){
						$TreeListWinter[]=$cla;
					}else{
						$EleListWinter[]=$kind;
					}	
				}

			}
			$tmp2 = $EleListFall;
			$tmp3 = $EleListWinter;
			$tmp = array_merge($tmp2,$tmp3);
			$stmt->close();

/********************************************************************************************************
	Retriving list of Required electives distinguished by type (complementry studys, Basic science,
	note A,b,c,...ect.) 
********************************************************************************************************/			
			
			$Tmp1 = array_unique($tmp);
			$Tmp2 = array_keys($Tmp1);
			$compStudies= array();
			$basicScience= array();
			$comms_elec= array();
			$sysc_elec= array();
			$CSE_spec= array();
			$BOIM_gp1= array();
			$BIOM_gp2= array();
			$SE_spce1= array();
			$SE_spce2 = array();
			$Hold=array();
			for($i=0;$i<count($Tmp1);$i++){
				switch($Tmp1[$Tmp2[$i]]){
					case 1:
						$stmt = $conn->prepare($sqlElec);
						$stmt->bind_param("i",$Tmp1[$Tmp2[$i]]);
						$stmt->bind_result($elec_id);
						$stmt->execute();
						while ( $stmt->fetch()) {
							$compStudies[] = $elec_id;
						}
						$stmt->close();
						break;

					case 2:
						$stmt = $conn->prepare($sqlElec);
						$stmt->bind_param("i",$Tmp1[$Tmp2[$i]]);
						$stmt->bind_result($elec_id);
						$stmt->execute();
						while($stmt->fetch()){
							$basicScience[]=$elec_id;
						}
						$stmt->close();
						break;
					case 3:	
						$stmt = $conn->prepare($sqlElec);
						$stmt->bind_param("i",$Tmp1[$Tmp2[$i]]);
						$stmt->bind_result($elec_id);
						$stmt->execute();
						while($stmt->fetch()){
							$comms_elec[]=$elec_id;
						}
						$stmt->close();
						break;
					case 4;
						$stmt = $conn->prepare($sqlElec);
						$stmt->bind_param("i",$Tmp1[$Tmp2[$i]]);
						$stmt->bind_result($elec_id);
						$stmt->execute();
						while($stmt->fetch()){
							$sysc_elec[]=$elec_id;
						}
						$stmt->close();
						break;
					case 5;
						$stmt = $conn->prepare($sqlElec);
						$stmt->bind_param("i",$Tmp1[$Tmp2[$i]]);
						$stmt->bind_result($elec_id);
						$stmt->execute();
						while($stmt->fetch()){
							$CSE_spec[]=$elec_id;
						}
						$stmt->close();
						break;	
					case 6;
						$stmt = $conn->prepare($sqlElec);
						$stmt->bind_param("i",$Tmp1[$Tmp2[$i]]);
						$stmt->bind_result($elec_id);
						$stmt->execute();
						while($stmt->fetch()){
							$BOIM_gp1[]=$elec_id;
						}
						$stmt->close();
						break;
					case 7;
						$stmt = $conn->prepare($sqlElec);
						$stmt->bind_param("i",$Tmp1[$Tmp2[$i]]);
						$stmt->bind_result($elec_id);
						$stmt->execute();
						while($stmt->fetch()){
							$BIOM_gp2[]=$elec_id;
						}
						$stmt->close();
						break;
					case 8;
						$stmt = $conn->prepare($sqlElec);
						$stmt->bind_param("i",$Tmp1[$Tmp2[$i]]);
						$stmt->bind_result($elec_id);
						$stmt->execute();
						while($stmt->fetch()){
							$SE_spce1[]=$elec_id;
						}
						$stmt->close();
						break;
					case 9;
						$stmt = $conn->prepare($sqlElec);
						$stmt->bind_param("i",$Tmp1[$Tmp2[$i]]);
						$stmt->bind_result($elec_id);
						$stmt->execute();
						while($stmt->fetch()){
							$SE_spce2[]=$elec_id;
						}
						$stmt->close();
						break;				
				}
			}
				if (!is_null($compStudies)){shuffle($compStudies);}
				if(!is_null($basicScience)){shuffle($basicScience);}
				if(!is_null($comms_elec)){shuffle($comms_elec);}
				if(!is_null($sysc_elec)){shuffle($sysc_elec);}
				if(!is_null($CSE_spec)){shuffle($CSE_spec);}
				if(!is_null($BOIM_gp1)){shuffle($BOIM_gp1);}
				if(!is_null($BIOM_gp2)){shuffle($BIOM_gp2);}
				if(!is_null($SE_spce1)){shuffle($SE_spce1);}
				if(!is_null($SE_spce2)){shuffle($SE_spce2);}

$electives = array(1 => $compStudies,2 => $basicScience,3 => $comms_elec,4=>$sysc_elec,5=>$CSE_spec,6 =>$BOIM_gp1, 7=>$BIOM_gp2, 8=>$SE_spce1,9=>$SE_spce2);



			

/********************************************************************************************************
	Creating an array of associated complementery classes (lab,tut,PAS,...ect) based on lectures 
	stored in database table TempUser_courses
********************************************************************************************************/
			
			$stmt = $conn->prepare($sqlLab);
			$stmt->execute();
			$stmt->bind_result($lab, $c);
			while( $stmt->fetch())
			{
				$complementery[]= array($c, $lab);
			}
			$stmt->close();

/********************************************************************************************************
	Eliminating all complementry classes from TempUsers_courses Table in database
********************************************************************************************************/			
			
			$stmt = $conn->prepare($sqlCombo);
			for($i=0;$i<count($complementery);$i++){
				$stmt->bind_param("i",$complementery[$i][1]);
				$stmt->execute();
			}

/********************************************************************************************************
	Creating an array consisting of Lectures only
********************************************************************************************************/
			
			$stmt = $conn->prepare($sqlLec);
			$stmt->execute();
			$stmt->bind_result($Lecture);
			while( $stmt->fetch())
			{
				$Lectures[]=$Lecture;	
			}
			$stmt->close();

/********************************************************************************************************
	Linking complementry courses time and day information into complementry classes array.
********************************************************************************************************/			
			
			for($row = 0; $row<count($complementery);$row++)
					{
						$stmt = $conn->prepare($sqlTiming);
						$stmt->bind_param("i",$complementery[$row][1]);
						$stmt->execute();
						$stmt->bind_result($sec,$type,$days,$s_time,$e_time,$space);
						$stmt->fetch();
						$stmt->close();	
						$EO = str_split($sec);
						if(in_array('O',$EO)){
							array_push($complementery[$row], $sec, $type, $days, $s_time, $e_time,$space,'O');
						}elseif(in_array('E', $EO)){
							array_push($complementery[$row], $sec, $type, $days, $s_time, $e_time,$space,'E');
						}else{
							array_push($complementery[$row], $sec, $type, $days, $s_time, $e_time,$space,'B');
						}
						
					}
/********************************************************************************************************
	Linking course code, name, day and timing information into two arrays specifyed by semester of 
	occurance. 
********************************************************************************************************/					
			
			$classeslist = array();
			$CourseListFall = array();
			$CourseListWinter = array();
			for($x=0;$x<count($Lectures); $x++)
			{
				$stmt = $conn->prepare($sql_read);
  				$stmt->bind_param("i", $Lectures[$x]);
				$stmt->execute();
				$stmt->bind_result($subj,$courseNum,$courseName,$semester);
				$stmt->fetch();
				$stmt->close();
				if($semester == 0){
					
					$classesFall[]= array($Lectures[$x],''.$subj." ".$courseNum.'', $courseName);

				}else{
					$classesWinter[]= array($Lectures[$x],''.$subj." ".$courseNum.'', $courseName);
					
				}
				
			}	

					
			$Lab_Time = array();
			$Lecture_Time = array();
			for($row = 0; $row<count($classesFall);$row++)
			{
				$stmt = $conn->prepare($sqlTiming);
				$stmt->bind_param("i",$classesFall[$row][0]);
				$stmt->execute();
				$stmt->bind_result($sec,$type,$days,$s_time,$e_time,$space);
				$stmt->fetch();
				$stmt->close();	
			
				/*Concatinating the Lecture scheduial information to Lecture information*/
				array_push($classesFall[$row], $sec, $type, $days, $s_time, $e_time,$space);
							
			}

			for($row = 0; $row<count($classesWinter);$row++)
			{
				$stmt = $conn->prepare($sqlTiming);
				$stmt->bind_param("i",$classesWinter[$row][0]);
				$stmt->execute();
				$stmt->bind_result($sec,$type,$days,$s_time,$e_time,$space);
				$stmt->fetch();
				$stmt->close();	
			
				/*Concatinating the Lecture scheduial information to Lecture information*/
				array_push($classesWinter[$row], $sec, $type, $days, $s_time, $e_time,$space);
							
			}

$conn->close();	
/********************************************************************************************************
	functions for the creation of conflect free time table.
********************************************************************************************************/	
function newTimeTable(){
			$Days = array('M','T','W','R','F');
			$Time = array('00:08:35','00:09:05','00:09:25','00:09:35','00:09:55','00:10:05','00:10:25','00:10:35','00:10:55',
						  '00:11:05','00:11:25','00:11:35','00:11:55','00:12:05','00:12:25','00:12:35','00:12:55','00:13:05',
						  '00:13:25','00:13:35','00:13:55','00:14:05','00:14:25','00:14:35','00:14:55','00:15:05','00:15:25',
						  '00:15:35','00:15:55','00:16:05','00:16:25','00:16:35','00:16:55','00:17:05','00:17:25','00:17:35',
						  '00:17:55','00:18:05','00:18:25','00:18:35','00:18:55','00:19:05','00:19:25','00:19:35','00:19:55',
						  '00:20:05','00:20:25','00:20:35','00:20:55'); 
			$time = array_fill_keys($Days, $Time);
			return($time);	 
	}
/*****************************************************************
subArray() finds complementery courses assocaited with 
selected lectures, based on the course_id Primary key index
of the lecture and accociated course_id of complementry class
*****************************************************************/
function subArray($array, $value){
	for($k=0;$k<count($array);$k++){
		if($array[$k][0]==$value && $array[$k][2]!="G5"){
			return($array[$k]);
		}
	}			
	return(NULL);
}
/*****************************************************************
clearTime() makes the time the new class takes in the 
time table no longer avalable.
*****************************************************************/

function cleartime($start,$stop,&$timeE,&$timeO,$day,$week){
	echo '<br>**********in**********<br>';
	if($day != NULL&&$start != NULL&&$stop!=NULL){
		$offset1 = array_search($start, $timeE[$day]);
		$offset2 = array_search($stop, $timeE[$day]);
		$offset3 = array_search($start, $timeO[$day]);
		$offset4 = array_search($stop, $timeO[$day]);
	}else{
		return(1);
	}
	switch($week){
		case('E'):
					do{
						$timeE[$day][$offset1]=0;
						$offset1++;
					}while ($offset1 <= $offset2);
					break; 

		case('O'):
					do{
						$timeO[$day][$offset3]=0;
						$offset3++;
					}while ($offset3 <= $offset4);
					break; 

		case('B'):
					while ($offset1 <= $offset2){
						$timeO[$day][$offset1]=0;
						$timeE[$day][$offset1]=0;
						$offset1++;
					};
					break;
				
	}
} 
	

function shedClasses($TreeListSpec,$classesTotake,$complementery,$electives,$EleListSeason,$S){			
		$shedW = array();
		shuffle($TreeListSpec);
		shuffle($classesTotake);
		$timeE=newTimeTable();
		$timeO=newTimeTable();
		for($i=0;$i<count($TreeListSpec);$i++){
			$NoMatch = 0;	
				$check=0;
				$select = $TreeListSpec[$i];
				for($k=0;$k<Count($classesTotake);$k++){
					$day = 0;
					if ($select == $classesTotake[$k][1]){
						$day = str_split($classesTotake[$k][5]);
						$j=0;
						if(count($day)>1){
							if((in_array($classesTotake[$k][6],$timeE[$day[$j]])&&in_array($classesTotake[$k][7],$timeE[$day[$j]]))
								&&(in_array($classesTotake[$k][6],$timeO[$day[$j]])&&in_array($classesTotake[$k][7],$timeO[$day[$j]]))){
								if((in_array($classesTotake[$k][6],$timeE[$day[$j+1]])&&in_array($classesTotake[$k][7],$timeE[$day[$j+1]]))
									&&(in_array($classesTotake[$k][6],$timeO[$day[$j+1]])&&in_array($classesTotake[$k][7],$timeO[$day[$j+1]]))&&($classesTotake[$k][8]>=1)){
									$check=1;
								}
							}else{
									$check=0;
							}
						}elseif($classesTotake[$k][5] == NULL){
							$check=1;				
						}else{
							if( (in_array($classesTotake[$k][6],$timeE[$classesTotake[$k][5]])&&in_array($classesTotake[$k][7],$timeE[$classesTotake[$k][5]]))
								&&(in_array($classesTotake[$k][6],$timeO[$classesTotake[$k][5]])&&in_array($classesTotake[$k][7],$timeO[$classesTotake[$k][5]])&&($classesTotake[$k][8]>=1))){
								$check=1;
							}
						}	

						if($check==1){
							do{
								Shuffle($complementery);
								$compTemp = subArray($complementery, $classesTotake[$k][0]);

							}while($compTemp[3]=='PAS');

							if (is_null($compTemp)){
								$shedW[$i]=$classesTotake[$k];
								if(count($day)>1){
									cleartime($classesTotake[$k][6],$classesTotake[$k][7],$timeE,$timeO,$day[$j],'B');
									cleartime($classesTotake[$k][6],$classesTotake[$k][7],$timeE,$timeO,$day[$j+1],'B');
								}elseif(count($day)==1){
									cleartime($classesTotake[$k][6],$classesTotake[$k][7],$timeE,$timeO,$day[0],'B');
								}
								
								if(count($day)>1){
									cleartime($classesTotake[$k][6],$classesTotake[$k][7],$timeE,$timeO,$day[$j+1],'B');
								}
								$NoMatch=1;
								break;

							}elseif(is_array($compTemp)){
								$days = str_split($compTemp[4]);
								echo"days = ".print_r($days)."count = ".count($days);
									if(count($days)==1){	
										if($compTemp[8]=='O'){
											if((in_array($compTemp[5],$timeO[$compTemp[4]])&&in_array($compTemp[6],$timeO[$compTemp[4]]))&&($compTemp[7]>=1)){
											
												$shedW[$i]=$classesTotake[$k];
												array_push($shedW[$i], $compTemp);									
												cleartime($classesTotake[$k][6],$classesTotake[$k][7],$timeE,$timeO,$day[$j],'B');
												cleartime($compTemp[5],$compTemp[6],$timeE,$timeO,$compTemp[4],'O');
												if(count($day)>1){											
													cleartime($classesTotake[$k][6],$classesTotake[$k][7],$timeE,$timeO,$day[$j+1],'B');					
												}
										
											$NoMatch=1;
											break;
											}
										}elseif($compTemp[8]=='E'){
											if((in_array($compTemp[5],$timeE[$compTemp[4]])&&in_array($compTemp[6],$timeE[$compTemp[4]]))&&($compTemp[7]>=1)){
										
												$shedW[$i]=$classesTotake[$k];
												array_push($shedW[$i], $compTemp);
												cleartime($classesTotake[$k][6],$classesTotake[$k][7],$timeE,$timeO,$day[$j],'B');						
												cleartime($compTemp[5],$compTemp[6],$timeE,$timeO,$compTemp[4],'E');
												if(count($day)>1){											
													cleartime($classesTotake[$k][6],$classesTotake[$k][7],$timeE,$timeO,$day[$j+1],'B');
												}
											
												$NoMatch=1;
												break;
										}
									}elseif(count($days>1)&&(in_array($compTemp[5],$timeO[$compTemp[4]])&&in_array($compTemp[6],$timeO[$compTemp[4]]))&&
										(in_array($compTemp[5],$timeE[$compTemp[4]])&&in_array($compTemp[6],$timeE[$compTemp[4]]))&&($compTemp[7]>=1)){

										$shedW[$i]=$classesTotake[$k];
										array_push($shedW[$i], $compTemp);										
										cleartime($classesTotake[$k][6],$classesTotake[$k][7],$timeE,$timeO,$day[$j],'B');						
										cleartime($compTemp[5],$compTemp[6],$timeE,$timeO,$compTemp[4],'B');
										if(count($day)>1){										
											cleartime($classesTotake[$k][6],$classesTotake[$k][7],$timeE,$timeO,$day[$j+1],'E');
										}
										
										$NoMatch=1;
										break;
									}else{
										$NoMatch=0;
									}
								}else{
									echo"*****days not equal to one*******";
									if((in_array($compTemp[5],$timeO[$compTemp[4]])&&in_array($compTemp[6],$timeO[$days[0]]))&&
									   (in_array($compTemp[5],$timeE[$compTemp[4]])&&in_array($compTemp[6],$timeE[$days[0]]))&&
									   (in_array($compTemp[5],$timeO[$compTemp[4]])&&in_array($compTemp[6],$timeO[$days[1]]))&&
									   (in_array($compTemp[5],$timeE[$compTemp[4]])&&in_array($compTemp[6],$timeE[$days[1]]))&&($compTemp[7]>=1)){
											$shedW[$i]=$classesTotake[$k];
											array_push($shedW[$i], $compTemp);										
											cleartime($classesTotake[$k][6],$classesTotake[$k][7],$timeE,$timeO,$day[$j],'B');						
											cleartime($compTemp[5],$compTemp[6],$timeE,$timeO,$days[0],'B');
											cleartime($compTemp[5],$compTemp[6],$timeE,$timeO,$days[1],'B');
											if(count($day)>1){										
											cleartime($classesTotake[$k][6],$classesTotake[$k][7],$timeE,$timeO,$day[$j+1],'E');
											}

									   }
								}
							}		
						}
					}
				}
			}	
		if($NoMatch==0){
			$shedW = winterClasses($TreeListWinter,$classesTotake,$complementery);
		}
		if(count($EleListSeason)>0){
			$elec = selectElectives($timeE,$timeO,$electives, $EleListSeason,$S,$complementery);
			$shed = array_merge($shedW,$elec);
		}	
	
	return($shed);	
}										
				
		

/*****************************************************************
	selectElectives() Selecting required electives, second to 
	core courses. selection based solely on first fit. Note 
	shuffling of array allows for random behaviours in selecting. 
*****************************************************************/
function selectElectives(&$timeE,&$timeO,$electives,$EleListTemp,$sem,$comp,$which){
		
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
		if($which == 1){
			shuffle($electives);
			$list = $electives;
		}
		//shuffle($electives);
		for($i=0;$i<count($EleListTemp);$i++){
			if($which == 0){
			$list = $electives[$EleListTemp[$i]];
			shuffle($list);
			}
			$match = 0;
			$compTemp = NULL;
			for($j=0;$j<count($list);$j++){
				$stmt= $conn->prepare("SELECT SUB_VAL, TYPE, DAYS, time_s, time_e,ROOM_CAP FROM Course_Timing Where course_id = ?");
				$stmt->bind_param("i",$list[$j]);
				$stmt->execute();
				$stmt->bind_result($sec,$type,$days,$s_time,$e_time,$space);
				$stmt->fetch();
				$stmt->close();
				$stmt = $conn->prepare("SELECT SUBJ, CRSE_NUM, NAME, SMSTR FROM Courses WHERE course_id = ?");
  				$stmt->bind_param("i", $list[$j]);
				$stmt->execute();
				$stmt->bind_result($subj,$courseNum,$courseName,$semester);
				$stmt->fetch();
				$stmt->close();
				$day = str_split($days);
				for($i=0; $i<count($comp);$i++){
					if($list[$j]==$comp[$i][0]){
						$index = $i;
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
					}elseif(count($day)==1&&in_array($s_time,$timeE[$day[0]])&&in_array($e_time,$timeE[$day[0]])&&in_array($s_time,$timeO[$day[0]])&&in_array($s_time,$timeO[$day[0]])
							&&$semester==$sem&&$space>0){
						cleartime($s_time,$e_time,$timeE,$timeO,$day,'B');
						$shed_elec[] = array($list[$j],$subj." ".$courseNum, $courseName, $sec,$type,$days,$s_time,$e_time,$space,'B');
				 		$match = 1;
				 		break;
					}
				}else{
					//$day = str_split($days);
					$EvenOdd = str_split($comp[$index][8]);
					if(is_null($day)||is_null($days)||($type!='LEC')){
					}elseif(count($days)==1){	
							if(in_array('O', $EvenOdd)){
								if((in_array($comp[$index][5],$timeO[$comp[$index][4]])&&in_array($comp[$index][6],$timeO[$comp[$index][4]]))&&($comp[$index][7]>=1)){
											
									$shed_elec[] = array($list[$j],$subj." ".$courseNum, $courseName, $sec,$type,$days,$s_time,$e_time,$space,'B');
									array_push($shed_elec, $comp[$index]);									
									cleartime($s_time,$e_time,$timeE,$timeO,$day[0],'B');
									cleartime($comp[$index][5],$comp[$index][6],$timeE,$timeO,$comp[4],'O');
									if(count($day)>1){											
										cleartime($s_time,$e_time,$timeE,$timeO,$day[1],'B');					
									}
										
									$NoMatch=1;
									break;
										}
								}elseif($compTemp[8]=='E'){
										if((in_array($comp[$index][5],$timeE[$comp[$index][4]])&&in_array($comp[$index][6],$timeE[$comp[$index][4]]))&&($comp[$index][7]>=1)){
										
											$shed_elec[] = array($list[$j],$subj." ".$courseNum, $courseName, $sec,$type,$days,$s_time,$e_time,$space,'B');
											array_push($shed_elec, $comp[$index]);
											cleartime($s_time,$e_time,$timeE,$timeO,$day[0],'B');
											cleartime($comp[$index][5],$comp[$index][6],$timeE,$timeO,$comp[4],'E');
											if(count($day)>1){											
												cleartime($s_time,$e_time,$timeE,$timeO,$day[1],'B');					
											}
										
											$NoMatch=1;
											break;
										}
								}elseif((in_array($comp[$index][5],$timeO[$comp[$index][4]])&&in_array($comp[$index][6],$timeO[$comp[$index][4]]))&&
										(in_array($comp[$index][5],$timeE[$comp[$index][4]])&&in_array($comp[$index][6],$timeE[$comp[$index][4]]))&&($comp[$index][7]>=1)){

									$shed_elec[] = array($list[$j],$subj." ".$courseNum, $courseName, $sec,$type,$days,$s_time,$e_time,$space,'B');
									array_push($shed_elec, $comp[$comp[$index]]);
									cleartime($s_time,$e_time,$timeE,$timeO,$day[0],'B');
									cleartime($comp[$index][5],$comp[$index][6],$timeE,$timeO,$comp[4],'B');
									if(count($day)>1){											
										cleartime($s_time,$e_time,$timeE,$timeO,$day[1],'B');					
									}
									
									$NoMatch=1;
									break;
								}else{
									$NoMatch=0;
								}
						}else{
							if((in_array($comp[$index][5],$timeO[$comp[$index][4]])&&in_array($comp[$index][6],$timeO[$days[0]]))&&
							   (in_array($comp[$index][5],$timeE[$comp[$index][4]])&&in_array($comp[$index][6],$timeE[$days[0]]))&&
							   (in_array($comp[$index][5],$timeO[$comp[$index][4]])&&in_array($comp[$index][6],$timeO[$days[1]]))&&
							   (in_array($comp[$index][5],$timeE[$comp[$index][4]])&&in_array($comp[$index][6],$timeE[$days[1]]))&&($comp[$index][7]>=1)){
									$shed_elec[] = array($list[$j],$subj." ".$courseNum, $courseName,$sec,$type,$days,$s_time,$e_time,$space,'B');
									array_push($shed_elec, $comp[$comp[$index]]);
									cleartime($s_time,$e_time,$timeE,$timeO,$day[0],'B');
									cleartime($comp[$index][5],$comp[$index][6],$timeE,$timeO,$day[0],'B');
									cleartime($comp[$index][5],$comp[$index][6],$timeE,$timeO,$day[1],'B');
									if(count($day)>1){											
										cleartime($s_time,$e_time,$timeE,$timeO,$day[1],'B');					
									}
					   		}
						}
					}
				
			}
		}
		$conn->close();
		return($shed_elec);
}
	


				$shed1F = shedClasses($TreeListFall,$classesFall,$complementery,$electives,$EleListFall,0);
				$shed2F = shedClasses($TreeListFall,$classesFall,$complementery,$electives,$EleListFall,0);
				$shed1W = shedClasses($TreeListWinter,$classesWinter,$complementery,$electives,$EleListWinter,1);
				$shed2W = shedClasses($TreeListWinter,$classesWinter,$complementery,$electives,$EleListWinter,1);
				



					//shed[0][x]= [0]->course_id [1]->SUBJ Number [2]->course name [3]->sectio [4]-> type [5]->days [6]->s_time, [7]->e_time 
					//[8]->ROOM_CAP [9]->ARRAY([9][0]->Lec course_id 
					//[9][1]->lab course_id [9][2]->lab section [9][3]->type [9][4]->days [9][5]->lab s_time [9][6]->lab e_time [9][7]->lab ROOM_CAP [9][8]even/odd)


				/*for($i=0;$i<count($Shed1F);$i++){
					functioncall($shed1F[$i][2],$shed1F[$i][4],$shed1F[$i][6],$shed1F[$i][7],$shed1F[$i][5],$shed1F[$i][3],'B');
					if(count($shed1F[$i])>9){	
						functionCall($shed1F[$i][2],$shed1F[$i][9][3],$shed1F[$i][9][5],$shed1F[$i][9][6],$shed1F[$i][9][4],$shed1F[$i][9][2],$shed1F[$i][9][8]);
					}	
				}*/	
				
				
				?>	


<?/********************************************************************************************************
	Debug Stuff
********************************************************************************************************/?>
<table id = 'list' name='Fall' align = 'left' padding = '10%'>
				<tr>
					<th>Class Fall</th>
				</tr>
				<?
					for($i = 0;$i<count($TreeListFall);$i++){
						echo "<tr>
									<td>".$TreeListFall[$i]."</td>";
								
									echo "</tr>";
					}	
				?>	
			</table>
			<table id = 'list' name='Fall' align = 'left' padding = '10%'>
				<tr>
					<th>Class Winter</th>
				</tr>
				<?
					for($i = 0;$i<count($TreeListWinter);$i++){
						echo "<tr>
									<td>".$TreeListWinter[$i]."</td>";
								
									echo "</tr>";
								}	
				?>	
			</table>
			<br>
			
			<table id = 'list' name='Fall' align = 'left' padding = '10%'>
				<th>Fall</th>
				<tr>
					<th>Class</th>
					<th>CompClass</th>
				</tr>
				<?
					for($i = 0;$i<count($shed1F);$i++){
						echo "<tr>
									<td>".$shed1F[$i][1]."-".$shed1F[$i][2]."-".$shed1F[$i][4]."-".$shed1F[$i][5]."-".$shed1F[$i][6]."-".$shed1F[$i][7]."</td>";
								if(count($shed1F[$i])>9){	
									echo	"<td>".$shed1F[$i][9][2]."-".$shed1F[$i][9][3]."-".$shed1F[$i][9][4]."-".$shed1F[$i][9][5]."-".$shed1F[$i][9][6]."-".$shed1F[$i][9][8]."</td></tr>";
								}else{
									echo "<td></td></tr>";
								}	
							
							 
					}
				?>	
			</table>
			<table id = 'list' name='Winter' align = 'right' padding = '10%'>
				<th>Winter</th>
				<tr>
					<th>Class</th>
					<th>CompClasses</th>
				</tr>
				<?
					for($i = 0;$i<count($shed1W);$i++){
						echo "<tr>
									<td>".$shed1W[$i][2]."-".$shed1W[$i][4]."-".$shed1W[$i][5]."-".$shed1W[$i][6]."-".$shed1W[$i][7]."</td>";
								if(count($shed1W[$i])>9){	
									echo "<td>".$shed1W[$i][9][2]."-".$shed1W[$i][9][3]."-".$shed1W[$i][9][4]."-".$shed1W[$i][9][5]."-".$shed1W[$i][9][6]."</td></tr>";
								}else{
									echo "<td></td></tr>";
								}	
							 
					}
				?>	
			</table>
			<br><br><br><br>
			<br><br><br><br>
		
			<table id = 'list' name='Fall' align = 'left' padding = '10%'>
				<th>Fall</th>
				<tr>
					<th>Class</th>
					<th>CompClass</th>
				</tr>
				<?
					for($i = 0;$i<count($shed2F);$i++){
						echo "<tr>
									<td>".$shed2F[$i][1]."-".$shed2F[$i][2]."-".$shed2F[$i][4]."-".$shed2F[$i][5]."-".$shed2F[$i][6]."-".$shed2F[$i][7]."</td>";
								if(count($shed2F[$i])>9){	
									echo	"<td>".$shed2F[$i][9][2]."-".$shed2F[$i][9][3]."-".$shed2F[$i][9][4]."-".$shed2F[$i][9][5]."-".$shed2F[$i][9][6]."</td></tr>";
								}else{
									echo "<td></td></tr>";
								}	
							
							 
					}
				?>	
			</table>
			
			<table id = 'list' name='Winter' align = 'left' padding = '10%'>
				<th>Winter</th>
				<tr>
					<th>Class</th>
					<th>CompClasses</th>
				</tr>
				<?
					for($i = 0;$i<count($shed2W);$i++){
						echo "<tr>
									<td>".$shed2W[$i][2]."-".$shed2W[$i][4]."-".$shed2W[$i][5]."-".$shed2W[$i][6]."-".$shed2W[$i][7]."</td>";
								if($shed2W[$i][9]!=0){	
									echo "<td>".$shed2W[$i][9][2]."-".$shed2W[$i][9][3]."-".$shed2W[$i][9][4]."-".$shed2W[$i][9][5]."-".$shed2W[$i][9][6]."</td></tr>";
								}else{
									echo "<td></td></tr>";
								}	
							 
					}
				?>	
			</table>				
