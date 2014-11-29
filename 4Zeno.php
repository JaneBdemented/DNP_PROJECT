<link rel="stylesheet" type="text/css" href="style.css">
	<script src="jsFunctions2.js"></script>

	
	<?php
	ini_set('max_execution_time',300);	
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
	$compArray = Null;
	for($k=0;$k<count($array);$k++){
		if($array[$k][0]==$value && $array[$k][2]!="G5"&& $array[$k][3]!="PAS"){
			$compArray[] = $array[$k];
		}
	}			
	return($compArray);
}
/*****************************************************************
clearTime() makes the time the new class takes in the 
time table no longer avalable.
*****************************************************************/

function cleartime($start,$stop,&$timeE,&$timeO,$day,$week){
	//echo $offset1."--1>2---".$offset2."--3->4--".$offset4;
	if($day = NULL || $start=NULL || $stop=NULL || $start = '00:00:00'|| $stop = '00:00:00'){
		return(1);
	}else{
		$offset1 = array_search($start, $timeE[$day]);
		$offset2 = array_search($stop, $timeE[$day]);
		$offset3 = array_search($start, $timeO[$day]);
		$offset4 = array_search($stop, $timeO[$day]);
	//	echo $offset1."--1>2---".$offset2."    ".$offset3."--3->4--".$offset4;
	}
	switch($week){
		case('E'):
					do{	
						$timeE[$day][$offset1]='0';
						$offset1++;
					}while ($offset1 <= $offset2);
					break; 

		case('O'):
					do{
					
						$timeO[$day][$offset3]='0';
						$offset3++;
					}while ($offset3 <= $offset4);
					break; 

		case('B'):
					do{
					
						$timeO[$day][$offset1]='0';
						$timeE[$day][$offset1]='0';
						$offset1;
					}while ($offset1 <= $offset2);
					break;
				
	}
	return(1);
} 
	
function getClasses($like,$classList){
	for($k=0;$k<Count($classList);$k++){
		if ($like == $classList[$k][1]){
			$classesTmp[] =  $classList[$k];
		}
	}
	return($classesTmp);
}	
function isTimeLec($thisClass,$timeE,$timeO){
	$check=0;
	$day = str_split($thisClass[5]);

	if(count($day)>1){
		if((in_array($thisClass[6],$timeE[$day[0]])!=NULL)&&(in_array($thisClass[7],$timeE[$day[0]])!=NULL)
			&&(in_array($thisClass[6],$timeO[$day[0]])!=NULL)&&(in_array($thisClass[7],$timeO[$day[0]])!=NULL)
			&&(in_array($thisClass[6],$timeE[$day[1]])!=NULL)&&(in_array($thisClass[7],$timeE[$day[1]])!=NULL)
			&&(in_array($thisClass[6],$timeO[$day[1]])!=NULL)&&(in_array($thisClass[7],$timeO[$day[1]])!=NULL)&&($thisClass[8]>=1)){
			$check=array(1,$day[0],$day[1],$thisClass[6],$thisClass[7]);
		}else{		
			$check=NULL;
			}				
	}else{
			if((in_array($thisClass[6],$timeE[$day[0]])!=NULL&&in_array($thisClass[7],$timeE[$day[0]]))!=NULL
				&&(in_array($thisClass[6],$timeO[$day[0]])!=NULL&&in_array($thisClass[7],$timeO[$day[0]])!=NULL&&($thisClass[8]>=1))){
				cleartime($thisClass[6],$thisClass[7],$timeE,$timeO,$day[0],'B');
				$check=array(1,$day[0],$thisClass[6],$thisClass[7]);
			}else{
				$check=NULL;
			}
		}
		return($check);
}	

function isTimeComp($thisClass,$timeE,$timeO){
	$check=0;
	$day = str_split($thisClass[4]);
	$week = $thisClass[8];
	
	switch($week){
		case 'O':
					if(in_array($thisClass[5],$timeO[$$day[0]])!=NULL&&in_array($thisClass[6],$timeO[$day[0]])!=NULL&&($thisClass[7]>=1)){
						$check=array(1,$day[0],$thisClass[5],$thisClass[6]);
							break;
						}else{
							$check=NULL;
							break;
						}	
					
		case 'E':
					if((in_array($thisClass[5],$timeE[$day[0]])!=NULL&&in_array($thisClass[6],$timeE[$day[0]]))!=NULL&&($thisClass[7]>=1)){
						$check=array(1,$day[0],$thisClass[5],$thisClass[6]);
						break;
					}else{
						$check=NULL;
						break;
					}			
		case 'B':
					if(count($day)>1){
						if((in_array($thisClass[5],$timeE[$day[0]])!=NULL)&&(in_array($thisClass[6],$timeE[$day[0]])!=NULL)
							&&(in_array($thisClass[5],$timeO[$day[0]])!=NULL)&&(in_array($thisClass[6],$timeO[$day[0]])!=NULL)
							&&(in_array($thisClass[5],$timeE[$day[1]])!=NULL)&&(in_array($thisClass[6],$timeE[$day[1]])!=NULL)
							&&(in_array($thisClass[5],$timeO[$day[1]])!=NULL)&&(in_array($thisClass[6],$timeO[$day[1]])!=NULL)&&($thisClass[7]>=1)){
							$check=array(1,$day[0],$day[1],$thisClass[5],$thisClass[6]);
							break;
						}else{		
							$check=Null;
							break;
						}
					}else{
						if((in_array($thisClass[5],$timeE[$day[0]])!=NULL&&in_array($thisClass[6],$timeE[$day[0]]))!=NULL
							&&(in_array($thisClass[5],$timeO[$day[0]])!=NULL&&in_array($thisClass[6],$timeO[$day[0]])!=NULL&&($thisClass[7]>=1))){
							$check=array(1,$day[0],$thisClass[5],$thisClass[6]);
							break;
						}else{
							$check=NULL;
							break;
						}			
					}
	}
	return($check);
}
	
function shedClasses($TreeListSpec,$classesTotake,$complementery,$S,$attempts){			
		$shedW = array();
		shuffle($TreeListSpec);
		shuffle($classesTotake);
		$timeE=newTimeTable();
		$timeO=newTimeTable();
		$upComp=0;
		$index =0;
		for($i=0;$i<count($TreeListSpec);$i++){
			$NoMatch = 0;	
				$thisClass = getclasses($TreeListSpec[$i],$classesTotake);
				for($k=0;$k<Count($thisClass);$k++){
						if($thisClass[$k][5] == NULL){
							$shedW[$i]=$thisClass[$k];
							$NoMatch=1;
							break;				
						}
						$upClass = isTimeLec($thisClass[$k],$timeE,$timeO);
						if($upClass!=NULL){
							$compTemp = subArray($complementery, $thisClass[$k][0]);
							if($compTemp!=NULL){
								shuffle($compTemp);
								for($e=0;$e<count($compTemp);$e++){
									$upComp =isTimeComp($compTemp[$e],$timeE,$timeO);
									if($upComp!=NULL){
										$index = $e;
										break;
									}
								}
							}	
						}

					if($upClass[0]==1&&$upComp[0]==1){
						if(count($upClass)==5){
							cleartime($upClass[3],$upClass[4],$timeE,$timeO,$upClass[1],'B');
							cleartime($upClass[3],$upClass[4],$timeE,$timeO,$upClass[2],'B');
							$shedW[$i]=$thisClass[$k];
												
						}else{
							cleartime($upClass[2],$upClass[3],$timeE,$timeO,$upClass[1],'B');
							$shedW[$i]=$thisClass[$k];
						}
						if(count($upComp)==5){
							cleartime($upComp[3],$upComp[4],$timeE,$timeO,$upComp[1],'B');
							cleartime($upComp[3],$upClass[4],$timeE,$timeO,$upComp[2],'B');
							array_push($shedW[$i], $compTemp[$index]);
						}else{
							cleartime($upClass[2],$upClass[3],$timeE,$timeO,$upClass[1],$compTemp[$index][8]);
							array_push($shedW[$i], $compTemp[$index]);
						}
						$NoMatch=1;
						break;
					}elseif($upClass[0]==1&&$compTemp==NULL){
						if(count($upClass)==5){
							cleartime($upClass[3],$upClass[4],$timeE,$timeO,$upClass[1],'B');
							cleartime($upClass[3],$upClass[4],$timeE,$timeO,$upClass[2],'B');
							$shedW[$i]=$thisClass[$k];
												
						}else{
							cleartime($upClass[2],$upClass[3],$timeE,$timeO,$upClass[1],'B');
							$shedW[$i]=$thisClass[$k];
						}
						$NoMatch=1;
						break;
					}
				}
			if($NoMatch == 0){
				break;
			}		
		}
		if($NoMatch==0){
			$attempts++;
			if($attempt==10){
				echo "<script>alert('The based on course avalibality and timeing we were unable to create a comflect free Table with in 10 itterations. Sorry!')</script>";
				break;
			}
			$shedW = shedClasses($TreeListSpec,$classesTotake,$complementery,$S,$attempts);
		}					
	return($shedW);	
}						
							
						/*	if (is_null($compTemp)){
								$shedW[$i]=$thisClass[$k];
								if(count($day)>1){
									cleartime($thisClass[$k][6],$thisClass[$k][7],$timeE,$timeO,$day[0],'B');
									cleartime($thisClass[$k][6],$thisClass[$k][7],$timeE,$timeO,$day[1],'B');
								}elseif(count($day)==1){
									cleartime($thisClass[$k][6],$thisClass[$k][7],$timeE,$timeO,$day[0],'B');
								}
								
								if(count($day)>1){
									cleartime($thisClass[$k][6],$thisClass[$k][7],$timeE,$timeO,$day[1],'B');
								}
								$NoMatch=1;
								break;

							}elseif(is_array($compTemp)){
								for($q=0;$q<count($compTemp);$q++){
									$days = str_split($compTemp[$q][4]);
									if(count($days)>1){

									}else{
										if((in_array($compTemp[$q][5],$timeO[$days[0]]])!=NULL)&&(in_array($compTemp[$q][6],$timeO[$days[0]])!=NULL)&&
											   (in_array($compTemp[$q][5],$timeE[$days[0]]])!=NULL)&&(in_array($compTemp[$q][6],$timeE[$days[0]])!=NULL)&&
											   (in_array($compTemp[$q][5],$timeO[$days[1]]])!=NULL)&&(in_array($compTemp[$q][6],$timeO[$days[1]])!=NULL)&&
											   (in_array($compTemp[$q][5],$timeE[$days[1]])!=NULL)&&(in_array($compTemp[$q][6],$timeE[$days[1]])!=NULL)&&($compTemp[7]>=1)){
											$pass = true;
											$index = $q;
											break;
										}

									}
								}
							}*/
									/*if(count($days)==1){	
										if($compTemp[8]=='O'){
											if((in_array($compTemp[5],$timeO[$compTemp[4]])!=NULL)&&(in_array($compTemp[6],$timeO[$compTemp[4]]))!=NULL&&($compTemp[7]>=1)){
											
												$shedW[$i]=$thisClass[$k];
												array_push($shedW[$i], $compTemp);									
												cleartime($thisClass[$k][6],$thisClass[$k][7],$timeE,$timeO,$day[0],'B');
												cleartime($compTemp[5],$compTemp[6],$timeE,$timeO,$compTemp[4],'O');
												if(count($day)>1){											
													cleartime($thisClass[$k][6],$thisClass[$k][7],$timeE,$timeO,$day[1],'B');					
												}
											
											$NoMatch=1;
											break;
											}
										}elseif($compTemp[8]=='E'){
											if((in_array($compTemp[5],$timeE[$compTemp[4]])!=NULL)&&(in_array($compTemp[6],$timeE[$compTemp[4]])!=NULL)&&($compTemp[7]>=1)){
										
												$shedW[$i]=$thisClass[$k];
												array_push($shedW[$i], $compTemp);
												cleartime($thisClass[$k][6],$thisClass[$k][7],$timeE,$timeO,$day[0],'B');						
												cleartime($compTemp[5],$compTemp[6],$timeE,$timeO,$compTemp[4],'E');
												if(count($day)>1){											
													cleartime($thisClass[$k][6],$thisClass[$k][7],$timeE,$timeO,$day[1],'B');
												}
											
												$NoMatch=1;
												break;
										}
									}elseif(count($days>1)&&(in_array($compTemp[5],$timeO[$compTemp[4]])!=NULL)&&(in_array($compTemp[6],$timeO[$compTemp[4]])!=NULL)&&
										(in_array($compTemp[5],$timeE[$compTemp[4]])!=NULL)&&(in_array($compTemp[6],$timeE[$compTemp[4]])!=NULL)&&($compTemp[7]>=1)){

										$shedW[$i]=$thisClass[$k];
										array_push($shedW[$i], $compTemp);										
										cleartime($thisClass[$k][6],$thisClass[$k][7],$timeE,$timeO,$day[$j],'B');						
										cleartime($compTemp[5],$compTemp[6],$timeE,$timeO,$compTemp[4],'B');
										if(count($day)>1){										
											cleartime($thisClass[$k][6],$thisClass[$k][7],$timeE,$timeO,$day[1],'E');
										}
										
										$NoMatch=1;
										break;
									}else{
										$NoMatch=0;
									}
								}else{
									if((in_array($compTemp[5],$timeO[$compTemp[4]])!=NULL)&&(in_array($compTemp[6],$timeO[$days[0]])!=NULL)&&
									   (in_array($compTemp[5],$timeE[$compTemp[4]])!=NULL)&&(in_array($compTemp[6],$timeE[$days[0]])!=NULL)&&
									   (in_array($compTemp[5],$timeO[$compTemp[4]])!=NULL)&&(in_array($compTemp[6],$timeO[$days[1]])!=NULL)&&
									   (in_array($compTemp[5],$timeE[$compTemp[4]])!=NULL)&&(in_array($compTemp[6],$timeE[$days[1]])!=NULL)&&($compTemp[7]>=1)){
											$shedW[$i]=$thisClass[$k];
											array_push($shedW[$i], $compTemp);										
											cleartime($thisClass[$k][6],$thisClass[$k][7],$timeE,$timeO,$day[0],'B');						
											cleartime($compTemp[5],$compTemp[6],$timeE,$timeO,$days[0],'B');
											cleartime($compTemp[5],$compTemp[6],$timeE,$timeO,$days[1],'B');
											if(count($day)>1){										
											cleartime($thisClass[$k][6],$thisClass[$k][7],$timeE,$timeO,$day[1],'E');
											}

									}
								}
							}
						}
					}
				}*/
				
		//if($NoMatch==0){
		//	$shedW = shedClasses($TreeListSpec,$classesTotake,$complementery,$S);
		//}
		//if(count($EleListSeason)>0){
		//	$elec = selectElectives($timeE,$timeO,$electives, $EleListSeason,$S,$complementery,0);
		//	$shed = array_merge($shedW,$elec);
		//}	
	
	//return($shedW);	
//}										
					
		


	


				$shed1F = shedClasses($TreeListFall,$classesFall,$complementery,$electives,$EleListFall,0,0);
				//$shed2F = shedClasses($TreeListFall,$classesFall,$complementery,$electives,$EleListFall,0);
				//$shed1W = shedClasses($TreeListWinter,$classesWinter,$complementery,$electives,$EleListWinter,1);
				//$shed2W = shedClasses($TreeListWinter,$classesWinter,$complementery,$electives,$EleListWinter,1);
				



					//shed[0][x]= [0]->course_id [1]->SUBJ Number [2]->course name [3]->sectio [4]-> type [5]->days [6]->s_time, [7]->e_time 
					//[8]->ROOM_CAP [9]->ARRAY([9][0]->Lec course_id 
					//[9][1]->lab course_id [9][2]->lab section [9][3]->type [9][4]->days [9][5]->lab s_time [9][6]->lab e_time [9][7]->lab ROOM_CAP [9][8]even/odd)


				/*for($i=0;$i<count($shed1F);$i++){
					    testStamp($shed1F[$i][2],$shed1F[$i][4],$shed1F[$i][6],$shed1F[$i][7],$shed1F[$i][5],$shed1F[$i][3],'B');
					if(count($shed1F[$i])>9){	
						testStamp($shed1F[$i][2],$shed1F[$i][9][3],$shed1F[$i][9][5],$shed1F[$i][9][6],$shed1F[$i][9][4],$shed1F[$i][9][2],$shed1F[$i][9][8]);
					}	
				}	
				*/
				
				?>	


<?/********************************************************************************************************
	Debug Stuff
********************************************************************************************************/?>
<script type="text/javascript">

    var fall= <?php echo json_encode($shed1F); ?>;
   

 </script>
  
  </head>

  <body>
    <title> Schedule </title>
    <button onclick="shed(fall)" > Click me </button>
    
    <div class="schedule">
      <div id="evenWeek">
        <table class="tables" cellpadding="0" cellspacing="0">
            <caption> EVEN WEEK </caption>
            <tr>
              <th>Time</th>
              <th>MON</th>
              <th>TUE</th>    
              <th>WED</th>
              <th>THU</th>
              <th>FRI</th>
            </tr>



            <tr><td><p class="emptyTime"></p></td><td></td><td></td><td></td><td></td><td></td></tr>

            <tr><td>8:00</td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td><p class="emptyTime"></p></td><td id="835MONE"></td><td id="835TUEE"></td><td id="835WEDE"></td><td id="835THUE"></td><td id="835FRIE"></td></tr>
            <tr><td>9:00</td><td id="905MONE"></td><td id="905TUEE"></td><td id="905WEDE"></td><td id="905THUE"></td><td id="905FRIE"></td></tr>
            <tr><td ><p class="emptyTime"></p></td><td id="935MONE"></td><td id="935TUEE"></td><td id="935WEDE"></td><td id="935THUE"></td><td id="935FRIE"></td></tr>
            <tr><td>10:00</td><td id="1005MONE"></td><td id="1005TUEE"></td><td id="1005WEDE"></td><td id="1005THUE"></td><td id="1005FRIE"></td></tr>
            <tr><td><p class="emptyTime"></p></td><td id="1035MONE"></td><td id="1035TUEE"></td><td id="1035WEDE"></td><td id="1035THUE"></td><td id="1035FRIE"></td></tr>
            <tr><td>11:00</td><td id="1105MONE"></td><td id="1105TUEE"></td><td id="1105WEDE"></td><td id="1105THUE"></td><td id="1105FRIE"></td></tr>
            <tr><td><p class="emptyTime"></p></td><td id="1135MONE"></td><td id="1135TUEE"></td><td id="1135WEDE"></td><td id="1135THUE"></td><td id="1135FRIE"></td></tr>
            <tr><td>12:00</td><td id="1205MONE"></td><td id="1205TUEE"></td><td id="1205WEDE"></td><td id="1205THUE"></td><td id="1205FRIE"></td></tr>
            <tr><td><p class="emptyTime"></p></td><td id="1235MONE"></td><td id="1235TUEE"></td><td id="1235WEDE"></td><td id="1235THUE"></td><td id="1235FRIE"></td></tr>              
            <tr><td>13:00</td><td id="1305MONE"></td><td id="1305TUEE"></td><td id="1305WEDE"></td><td id="1305THUE"></td><td id="1305FRIE"></td></tr>
            <tr><td><p class="emptyTime"></p></td><td id="1335MONE"></td><td id="1335TUEE"></td><td id="1335WEDE"></td><td id="1335THUE"></td><td id="1335FRIE"></td></tr>  
            <tr><td>14:00</td><td id="1405MONE"></td><td id="1405TUEE"></td><td id="1405WEDE"></td><td id="1405THUE"></td><td id="1405FRIE"></td></tr>
            <tr><td><p class="emptyTime"></p></td><td id="1435MONE"></td><td id="1435TUEE"></td><td id="1435WEDE"></td><td id="1435THUE"></td><td id="1435FRIE"></td></tr>  
            <tr><td>15:00</td><td id="1505MONE"></td><td id="1505TUEE"></td><td id="1505WEDE"></td><td id="1505THUE"></td><td id="1505FRIE"></td></tr>
            <tr><td><p class="emptyTime"></p></td><td id="1535MONE"></td><td id="1535TUEE"></td><td id="1535WEDE"></td><td id="1535THUE"></td><td id="1535FRIE"></td></tr>
            <tr><td>16:00</td><td id="1605MONE"></td><td id="1605TUEE"></td><td id="1605WEDE"></td><td id="1605THUE"></td><td id="1605FRIE"></td></tr>
            <tr><td><p class="emptyTime"></p></td><td id="1635MONE"></td><td id="1635TUEE"></td><td id="1635WEDE"></td><td id="1635THUE"></td><td id="1635FRIE"></td></tr>
            <tr><td>17:00</td><td id="1705MONE"></td><td id="1705TUEE"></td><td id="1705WEDE"></td><td id="1705THUE"></td><td id="1705FRIE"></td></tr>
            <tr><td><p class="emptyTime"></p></td><td id="1735MONE"></td><td id="1735TUEE"></td><td id="1735WEDE"></td><td id="1735THUE"></td><td id="1735FRIE"></td></tr>
            <tr><td>18:00</td><td id="1805MONE"></td><td id="1805TUEE"></td><td id="1805WEDE"></td><td id="1805THUE"></td><td id="1805FRIE"></td></tr>
            <tr><td><p class="emptyTime"></p></td><td id="1835MONE"></td><td id="1835TUEE"></td><td id="1835WEDE"></td><td id="1835THUE"></td><td id="1835FRIE"></td></tr>
            <tr><td>19:00</td><td id="1905MONE"></td><td id="1905TUEE"></td><td id="1905WEDE"></td><td id="1905THUE"></td><td id="1905FRIE"></td></tr>
            <tr><td><p class="emptyTime"></p></td><td id="1935MONE"></td><td id="1935TUEE"></td><td id="1935WEDE"></td><td id="1935THUE"></td><td id="1935FRIE"></td></tr>
            <tr><td>20:00</td><td id="2005MONE"></td><td id="2005TUEE"></td><td id="2005WEDE"></td><td id="2005THUE"></td><td id="2005FRIE"></td></tr>
            <tr><td><p class="emptyTime"></p></td><td id="2035MONE"></td><td id="2035TUEE"></td><td id="2035WEDE"></td><td id="2035THUE"></td><td id="2035FRIE"></td></tr>
            <tr><td>21:00</td><td id="2105MONE"></td><td id="2105TUEE"></td><td id="2105WEDE"></td><td id="2105THUE"></td><td id="2105FRIE"></td></tr>
            <tr><td><p class="emptyTime"></p></td><td id="2135MONE"></td><td id="2135TUEE"></td><td id="2135WEDE"></td><td id="2135THUE"></td><td id="2135FRIE"></td></tr>
            <tr><td>22:00</td><td id="2205MONE"></td><td id="2205TUEE"></td><td id="2205WEDE"></td><td id="2205THUE"></td><td id="2205FRIE"></td></tr>
        </table>

      </div>

      <div id="oddWeek">
        <table class="tables" cellpadding="0" cellspacing="0">

            <caption> ODD WEEK </caption>
            <tr>
              <th>Time</th>
              <th>MON</th>
              <th>TUE</th>    
              <th>WED</th>
              <th>THU</th>
              <th>FRI</th>
            </tr>

            

            <tr><td><p class="emptyTime"></p></td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>8:00</td><td></td><td></td><td></td><td></td><td></td></tr>

            <tr><td><p class="emptyTime"></p></td><td id="835MONO"></td><td id="835TUEO"></td><td id="835WEDO"></td><td id="835THUO"></td><td id="835FRIO"></td></tr>
            <tr><td>9:00</td><td id="905MONO"></td><td id="905TUEO"></td><td id="905WEDO"></td><td id="905THUO"></td><td id="905FRIO"></td></tr>
            <tr><td ><p class="emptyTime"></p></td><td id="935MONO"></td><td id="935TUEO"></td><td id="935WEDO"></td><td id="935THUO"></td><td id="935FRIO"></td></tr>
            <tr><td>10:00</td><td id="1005MONO"></td><td id="1005TUEO"></td><td id="1005WEDO"></td><td id="1005THUO"></td><td id="1005FRIO"></td></tr>
            <tr><td><p class="emptyTime"></p></td><td id="1035MONO"></td><td id="1035TUEO"></td><td id="1035WEDO"></td><td id="1035THUO"></td><td id="1035FRIO"></td></tr>
            <tr><td>11:00</td><td id="1105MONO"></td><td id="1105TUEO"></td><td id="1105WEDO"></td><td id="1105THUO"></td><td id="1105FRIO"></td></tr>
            <tr><td><p class="emptyTime"></p></td><td id="1135MONO"></td><td id="1135TUEO"></td><td id="1135WEDO"></td><td id="1135THUO"></td><td id="1135FRIO"></td></tr>
            <tr><td>12:00</td><td id="1205MONO"></td><td id="1205TUEO"></td><td id="1205WEDO"></td><td id="1205THUO"></td><td id="1205FRIO"></td></tr>
            <tr><td><p class="emptyTime"></p></td><td id="1235MONO"></td><td id="1235TUEO"></td><td id="1235WEDO"></td><td id="1235THUO"></td><td id="1235FRIO"></td></tr>              
            <tr><td>13:00</td><td id="1305MONO"></td><td id="1305TUEO"></td><td id="1305WEDO"></td><td id="1305THUO"></td><td id="1305FRIO"></td></tr>
            <tr><td><p class="emptyTime"></p></td><td id="1335MONO"></td><td id="1335TUEO"></td><td id="1335WEDO"></td><td id="1335THUO"></td><td id="1335FRIO"></td></tr>  
            <tr><td>14:00</td><td id="1405MONO"></td><td id="1405TUEO"></td><td id="1405WEDO"></td><td id="1405THUO"></td><td id="1405FRIO"></td></tr>
            <tr><td><p class="emptyTime"></p></td><td id="1435MONO"></td><td id="1435TUEO"></td><td id="1435WEDO"></td><td id="1435THUO"></td><td id="1435FRIO"></td></tr>  
            <tr><td>15:00</td><td id="1505MONO"></td><td id="1505TUEO"></td><td id="1505WEDO"></td><td id="1505THUO"></td><td id="1505FRIO"></td></tr>
            <tr><td><p class="emptyTime"></p></td><td id="1535MONO"></td><td id="1535TUEO"></td><td id="1535WEDO"></td><td id="1535THUO"></td><td id="1535FRIO"></td></tr>
            <tr><td>16:00</td><td id="1605MONO"></td><td id="1605TUEO"></td><td id="1605WEDO"></td><td id="1605THUO"></td><td id="1605FRIO"></td></tr>
            <tr><td><p class="emptyTime"></p></td><td id="1635MONO"></td><td id="1635TUEO"></td><td id="1635WEDO"></td><td id="1635THUO"></td><td id="1635FRIO"></td></tr>
            <tr><td>17:00</td><td id="1705MONO"></td><td id="1705TUEO"></td><td id="1705WEDO"></td><td id="1705THUO"></td><td id="1705FRIO"></td></tr>
            <tr><td><p class="emptyTime"></p></td><td id="1735MONO"></td><td id="1735TUEO"></td><td id="1735WEDO"></td><td id="1735THUO"></td><td id="1735FRIO"></td></tr>
            <tr><td>18:00</td><td id="1805MONO"></td><td id="1805TUEO"></td><td id="1805WEDO"></td><td id="1805THUO"></td><td id="1805FRIO"></td></tr>
            <tr><td><p class="emptyTime"></p></td><td id="1835MONO"></td><td id="1835TUEO"></td><td id="1835WEDO"></td><td id="1835THUO"></td><td id="1835FRIO"></td></tr>
            <tr><td>19:00</td><td id="1905MONO"></td><td id="1905TUEO"></td><td id="1905WEDO"></td><td id="1905THUO"></td><td id="1905FRIO"></td></tr>
            <tr><td><p class="emptyTime"></p></td><td id="1935MONO"></td><td id="1935TUEO"></td><td id="1935WEDO"></td><td id="1935THUO"></td><td id="1935FRIO"></td></tr>
            <tr><td>20:00</td><td id="2005MONO"></td><td id="2005TUEO"></td><td id="2005WEDO"></td><td id="2005THUO"></td><td id="2005FRIO"></td></tr>
            <tr><td><p class="emptyTime"></p></td><td id="2035MONO"></td><td id="2035TUEO"></td><td id="2035WEDO"></td><td id="2035THUO"></td><td id="2035FRIO"></td></tr>
            <tr><td>21:00</td><td id="2105MONO"></td><td id="2105TUEO"></td><td id="2105WEDO"></td><td id="2105THUO"></td><td id="2105FRIO"></td></tr>
            <tr><td><p class="emptyTime"></p></td><td id="2135MONO"></td><td id="2135TUEO"></td><td id="2135WEDO"></td><td id="2135THUO"></td><td id="2135FRIO"></td></tr>
            <tr><td>22:00</td><td id="2205MONO"></td><td id="2205TUEO"></td><td id="2205WEDO"></td><td id="2205THUO"></td><td id="2205FRIO"></td></tr>
        </table>
      </div>
    </div>
    



  </body>

</html>
