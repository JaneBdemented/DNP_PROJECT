<?php

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
//-------------------------------------------------------------------------------------------------------
/*****************************************************************
clearTime() makes the time the new class takes in the 
time table no longer avalable.
*****************************************************************/
function cleartime($start,$stop,&$timeE,&$timeO,$day,$week){
	//echo '<br>**********in**********<br>';
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
	





//-------------------------------------------------------------------------------------------------------











function offTrackShed($Classes,$sem,$complementery){
	$timeE = newTimeTable();
	$timeO = newTimeTable();
	$shed_elec = array();
	$servername = "localhost";
	$username = "root";
	$password = "1234"; //<-your password
	$DNPDB = "DNP_PROJECT";
	$conn = new mysqli($servername, $username, $password, $DNPDB);
		// Check connection
	if ($conn->connect_error) {
		echo("Connection failed: " . $conn->connect_error);	
	}	
	shuffle($Classes);
	for($i=0;$i<6;$i++){
			$index = 0;
			
			$compTemp=NULL;
			$match = 0;
		while($match == 0){
				$list = $Classes[$index];
				$index++ ;
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
						$compTemp = $complementery[$l] ;
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
				 			$shed_elec[] = array($list,$subj." ".$courseNum, $courseName,$sec,$type,$days,$s_time,$e_time,$space,'B');
				 			break;
				 		} 	
					}elseif(count($day)==1&&$days!=NULL&&in_array($s_time,$timeE[$day[0]])&&in_array($e_time,$timeE[$day[0]])&&in_array($s_time,$timeO[$day[0]])&&in_array($s_time,$timeO[$day[0]])
							&&$semester==$sem&&$space>0){
						cleartime($s_time,$e_time,$timeE,$timeO,$day[0],'B');
						$shed_elec[] = array($list,$subj." ".$courseNum, $courseName, $sec,$type,$days,$s_time,$e_time,$space,'B');
				 		$match = 1;
				 		break;

					}
				}else{
					if(is_null($day)||is_null($days)||($type!='LEC')){
					}elseif(count($days)==1){	
							if($compTemp[8]=='O'){
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
				
	$conn->close();
	if(count($shed_elec) != 6){
		$shed_elec = offTrackShed($Classes,$sem,$complementery);
	}
	return($shed_elec);
} // end function











/////////////////////////////////////This is the start of the prereq identification code /////////////////////////////////////


	//$StatusGiven =$_POST['Status'] ; // need the status right here 
	//$StatusGiven =  4 ; 
	//$StreamGiven  = "Comms" ;
	$Semester= $_POST['Semester']; 
	$complementery = $_POST['complementery'];
	//print_r($complementery);
	//echo $Semester ; 
	
	$StreamGiven = $_POST['Stream'];
	//echo $StreamGiven ;
	$courses_taken = $_POST['querycourses']; //courses the person has taken 
    $Elegible_Courses= array();// the list of courses the person is eligible to take based on the prerequisites
	//$Courses_Given= array("ELEC 4505","ELEC 4506","SYSC 4602","PHYS 1004","PHYS 1002","ELEC 2501","ELEC 2507","ELEC 2607","ELEC 3500");// array of the courses in his stream 
	$Courses_Given = $_POST['resultUnique'];
	
	////Determines the year status based on the number of courses taken that is calculates the number of credits 
	
	$length = count($courses_taken) ; 
	if (($length/2) < 4){
		$StatusGiven = 1;
	}elseif(($length/2) >= 4 && ($length/2) <= 8.5 ){
		$StatusGiven = 2;
	}elseif(($length/2) >= 9 && ($length/2) <= 13.5 ){
		$StatusGiven = 3;
	}else{
		$StatusGiven = 4;
	}//end if 
	
	//echo $StatusGiven ;
	
	
	
	
	
	for($i=0; $i < count($Courses_Given); $i++){
    //echo "Selected " . $Courses_Given[$i] ."<br/>";	

	
	
	$conn = mysqli_connect("localhost","root","1234","DNP_PROJECT");

	// Check connection
	if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
	}//end if  
	
	
	$sqlIfPrereq = "SELECT * from prereq2 where Courses = ?";
	
	$stmt = $conn->prepare($sqlIfPrereq);
  	$stmt->bind_param("s",$Courses_Given[$i]);
	//if ($stmt->execute() == TRUE ){
	$check = ($stmt->execute() ) ; 
	$stmt->close();
	if($check == 1){
	
	$sql2 = "SELECT prereq_Type,Status_Stream FROM prereq2 WHERE Courses = ? ";
	$stmt = $conn->prepare($sql2);
  	$stmt->bind_param("s",$Courses_Given[$i]);
	$stmt->execute();
	$stmt->bind_result($prereq_Type,$Status_Stream);
	 /* fetch value */
    $stmt->fetch();
	$stmt->close();
	if($Status_Stream !=0){
	$Status_Stream_Array = explode("_",$Status_Stream);
	$Status = $Status_Stream_Array[0]; // represents the status 
	$Stream = $Status_Stream_Array[1]; // represents the stream 
	}//end if 
	
	if($prereq_Type !=0 ){
	//echo "is of prerequisite type of ". $prereq_Type."<br>";
	
	
	//Writing switch cases here 
	switch ($prereq_Type) {
	
    case 1: //Prereqs with just AND terms 
		$sqlAND = "SELECT prereq_ANDS FROM prereq2 WHERE Courses = ? ";
		$stmt = $conn->prepare($sqlAND);
		$stmt->bind_param("s",$Courses_Given[$i]);
		$stmt->execute();
		$stmt->bind_result($AND_prereq); //gets the whole string under the column prereq AND
		/* fetch value */
		$stmt->fetch();
		$stmt->close();
		$prereq_AND =  explode(",",$AND_prereq) ; //splits the string 
		
		
		$contains_all_needed = 0 == count(array_diff($prereq_AND,$courses_taken)) ;
		if ($contains_all_needed){
		
		array_push($Elegible_Courses,$Courses_Given[$i]); //pushes this given course in the existing list of elegible courses 		
		echo "You are eligible to take " .$Courses_Given[$i]."as you meet the prerequisites <br>" ;
		} //end if 
		
        break;
		
    case 2: //Prereqs with just OR terms 
			
		$sqlOR = "SELECT prereq_ORS FROM prereq2 WHERE Courses = ? ";
		$stmt = $conn->prepare($sqlOR);
		$stmt->bind_param("s",$Courses_Given[$i]);
		$stmt->execute();
		$stmt->bind_result($OR_prereq); //gets the whole string under the column prereq OR 
			/* fetch value */
		$stmt->fetch();
		$stmt->close();
		$prereq_OR =  explode(",",$OR_prereq) ; //splits the string 
		$countofsame = count(array_intersect($prereq_OR,$courses_taken)) > 0;
			if($countofsame){
			array_push($Elegible_Courses,$Courses_Given[$i]); //pushes this given course in the existing list of elegible courses 		
			//echo "You are eligible to take " .$Courses_Given[$i]."as you meet the prerequisites <br>" ;
		} //end if 
        break;
		
    case 3: // Prereqs with ANDs and ORS 
		$sqlANDOR = "SELECT prereq_ANDS,prereq_ORS FROM prereq2 WHERE Courses = ? ";
		$stmt = $conn->prepare($sqlANDOR);
		$stmt->bind_param("s",$Courses_Given[$i]);
		$stmt->execute();
		$stmt->bind_result($AND_prereq,$OR_prereq); //gets the whole string under the column prereq AND
		$stmt->fetch();
		$stmt->close();
		$prereq_OR =  explode(",",$OR_prereq) ; //splits the string 
		$prereq_AND =  explode(",",$AND_prereq) ; //splits the string 
		//print_r($prereq_AND);
		//print_r($prereq_OR);
		
		$contains_all_needed = 0 == count(array_diff($prereq_AND,$courses_taken)) ;
		if ($contains_all_needed){
			$countofsame = count(array_intersect($prereq_OR,$courses_taken)) > 0;
			if($countofsame){
			echo "You can take this course";
			array_push($Elegible_Courses,$Courses_Given[$i]); 
			}//end if 
		} //end if 
		
        break;
		
		case 4: //single course
		$sqlSingle = "SELECT prereq_ANDS FROM prereq2 WHERE Courses = ? ";
		$stmt = $conn->prepare($sqlSingle);
		$stmt->bind_param("s",$Courses_Given[$i]);
		$stmt->execute();
		$stmt->bind_result($SingleCourse); //gets the whole string under the column prereq
		$stmt->fetch();
		$stmt->close();
	
		//echo $SingleCourse ;
		if (in_array($SingleCourse,$courses_taken)){
		
		array_push($Elegible_Courses,$Courses_Given[$i]); 
		}//end if 
		
		break ;
		
		
	case 5: // ANDs and check status and stream 
		$sqlAND = "SELECT prereq_ANDS FROM prereq2 WHERE Courses = ? ";
		$stmt = $conn->prepare($sqlAND);
		$stmt->bind_param("s",$Courses_Given[$i]);
		$stmt->execute();
		$stmt->bind_result($AND_prereq); //gets the whole string under the column prereq AND
		/* fetch value */
		$stmt->fetch();
		$stmt->close();
		$prereq_AND =  explode(",",$AND_prereq) ; //splits the string 
		
		$contains_all_needed = 0 == count(array_diff($prereq_AND,$courses_taken)) ;
		if ($contains_all_needed){
			if ($StatusGiven == $Status & $StreamGiven == $Stream ){
			array_push($Elegible_Courses,$Courses_Given[$i]); 		
			//echo "You are eligible to take " .$Courses_Given."as you meet the prerequisites <br>" ;
			} //end if 
		}//end if
        break;
		
		
	case 6: // Unique case
		$sqlBoth = "SELECT prereq_ANDS,prereq_ORS FROM prereq2 WHERE Courses = ? ";
		$stmt = $conn->prepare($sqlBoth);
		$stmt->bind_param("s",$Courses_Given[$i]);
		$stmt->execute();
		$stmt->bind_result($ORS1,$ORS2); //gets the whole string under the column prereq AND
		$stmt->fetch();
		$stmt->close();
		$prereq_ORS1 =  explode(",",$ORS1) ; //splits the string 
		$prereq_ORS2 =  explode(",",$ORS2) ; //splits the string 
		
		$countofsame1 = count(array_intersect($prereq_ORS1,$courses_taken)) > 0;
		$countofsame2 = count(array_intersect($prereq_ORS2,$courses_taken)) > 0;
		if($countofsame1  & $countofsame2 ){
			echo "You can take this course";
			array_push($Elegible_Courses,$Courses_Given[$i]); 
		}//end if 
		break ;
	
	
	
	case 7: //Outliers 
	
	if ($Courses_Given[$i] == "ECOR 2606"){
	
		if (in_array("MATH 1005",$course_taken)){
		
			$sqlBoth = "SELECT prereq_ANDS,prereq_ORS FROM prereq2 WHERE Courses = ? ";
			$stmt = $conn->prepare($sqlBoth);
			$stmt->bind_param("s",$Courses_Given[$i]);
			$stmt->execute();
			$stmt->bind_result($ORS1,$ORS2); //gets the whole string under the column prereq AND
			$stmt->fetch();
			$stmt->close();
			$prereq_ORS1 =  explode(",",$ORS1) ; //splits the string 
			$prereq_ORS2 =  explode(",",$ORS2) ; //splits the string 
		
			$countofsame1 = count(array_intersect($prereq_ORS1,$courses_taken)) > 0;
			$countofsame2 = count(array_intersect($prereq_ORS2,$courses_taken)) > 0;
			if($countofsame1  & $countofsame2 ){
			//echo "You can take this course";
			array_push($Elegible_Courses,$Courses_Given[$i]); 
			}//end if 
		}
		break;
	}//end if for ECOR 2606
	
	if ($Courses_Given == "SYSC 4602"){
	
		$sqlOR = "SELECT prereq_ORS FROM prereq2 WHERE Courses = ? ";
		$stmt = $conn->prepare($sqlOR);
		$stmt->bind_param("s",$Courses_Given[$i]);
		$stmt->execute();
		$stmt->bind_result($OR_prereq); //gets the whole string under the column prereq OR 
			/* fetch value */
		$stmt->fetch();
		$stmt->close();
		$prereq_OR =  explode(",",$OR_prereq) ; //splits the string 
		$countofsame = count(array_intersect($prereq_OR,$courses_taken)) > 0;
			if($countofsame){ 
			// strcmp($StreamGiven, "SE") == 0
				if($StatusGiven == 4 & ($StreamGiven == 'SE'|| $StreamGiven == 'BIO-ELEC'|| $StreamGiven == 'BIO-ELEC""CSE') || ($StatusGiven == 3 & $StreamGiven == "Comms")) {
				array_push($Elegible_Courses,$Courses_Given[$i]); //pushes this given course in the existing list of elegible courses 		
				//echo "You are eligible to take " .$Courses_Given[$i]."as you meet the prerequisites <br>" ;
				}//end if 
			} //end if 
			
			break;
	}//end if
	
	
	if ($Courses_Given[$i] == "SYSC 4502"){
		$sqlANDOR = "SELECT prereq_ANDS,prereq_ORS FROM prereq2 WHERE Courses = ? ";
		$stmt = $conn->prepare($sqlANDOR);
		$stmt->bind_param("s",$Courses_Given[$i]);
		$stmt->execute();
		$stmt->bind_result($AND_prereq,$OR_prereq); //gets the whole string under the column prereq AND
		$stmt->fetch();
		$stmt->close();
		$prereq_OR =  explode(",",$OR_prereq) ; //splits the string 
		$prereq_AND =  explode(",",$AND_prereq) ; //splits the string 
		//print_r($prereq_AND);
		//print_r($prereq_OR);
		
		$contains_all_needed = 0 == count(array_diff($prereq_AND,$courses_taken)) ;
		if ($contains_all_needed){
			$countofsame = count(array_intersect($prereq_OR,$courses_taken)) > 0;
			if($countofsame){
				if (($StatusGiven == 4 & ($StreamGiven == 'CSE' || $StreamGiven == 'SE')) || ($StatusGiven == 3 & $StreamGiven == "Comms")){
				//echo "You can take this course";
				array_push($Elegible_Courses,$Courses_Given[$i]);
				}//end if 
			}//end if 
			break;
		} //end if 
	
	}//end if 
	break;
	
	case 8://Checks status and stream 
		if ($StatusGiven == $Status & $StreamGiven == $Stream ){
			array_push($Elegible_Courses,$Courses_Given[$i]); 		
			//echo "You are eligible to take " .$Courses_Given."as you meet the prerequisites <br>" ;
		} //end if 
		break;
		
		
	case 9: 
		$sqlCourses = "SELECT prereq_ORS FROM prereq2 WHERE Courses = ? ";
		$stmt = $conn->prepare(	$sqlCourses);
		$stmt->bind_param("s",$Courses_Given[$i]);
		$stmt->execute();
		$stmt->bind_result($OR_prereq); //gets the whole string under the column prereq OR 
			 
		$stmt->fetch();
		$stmt->close();
		$prereq_OR =  explode(",",$OR_prereq) ; //splits the string 
		$countofsame = count(array_intersect($prereq_OR,$courses_taken)) > 0;
		if($countofsame == true & $StatusGiven == $Status & $StreamGiven == $Stream){
			array_push($Elegible_Courses,$Courses_Given[$i]); //pushes this given course in the existing list of elegible courses 		
			//echo "You are eligible to take " .$Courses_Given[$i]."as you meet the prerequisites <br>" ;
		}//end if 
		 break;
	
	
} //end of switch statement 
	
}else{

	array_push($Elegible_Courses,$Courses_Given[$i]) ; 
}//end if 
}//end if 
    //$stmt->close();	
}//end for  
//print_r($Elegible_Courses);
 $Elegible_FinalCourses = array_diff($Elegible_Courses,$courses_taken);
 $keys = array_keys( $Elegible_FinalCourses );
 //print_r($Elegible_FinalCourses) ;
 
 
 
 //======================================================================
 
 $courseids_array = array();
 for($y=0; $y < count($Elegible_FinalCourses); $y++){
  
 //$sqlCourses = "SELECT prereq_ORS FROM prereq2 WHERE Courses = ? ";
 $test = $Elegible_FinalCourses[$keys[$y]] ;
 
 //echo  $test;

 
$subj_crsnm = explode(" ",$test);

$subj= $subj_crsnm[0];
$crsnm= $subj_crsnm[1];
$sqlfindcourse_id = "SELECT course_id FROM courses WHERE SUBJ = ? AND CRSE_NUM = ?";
$stmt = $conn->prepare($sqlfindcourse_id);
$stmt->bind_param("si",$subj,$crsnm);
$stmt->execute();
$stmt->bind_result($WantedCourse_id);
$stmt->fetch();
$stmt->close();
array_push ($courseids_array,$WantedCourse_id);
 
}//end for 
 //print_r($courseids_array);
 ////////////////////////////////////////////////////////////////////////////////////////////////////////////
 
//$arrayFallodd=newTimeTable() ;
//$arrayFallaeven=newTimeTable() ;
//$arrayWinterodd =newTimeTable() ;
//$arrayWintereven =  newTimeTable() ;

//print_r($arrayFallodd);
//list of rray from numbers 1 to 6 
//$numarray= array (1,2,3,4,5,6) ; 


$shed_elec= offTrackShed($courseids_array,$Semester,$complementery) ;
print_r($shed_elec);
/* 
				for($l=0;$i<count($shed_elec);$l++){
					functioncall($shed_elec[$l][2],$shed_elec[$l][4],$shed_elec[$l][6],$shed_elec[$l][7],$shed_elec[$l][5],$shed_elec[$l][3],'B');
					if(count($shed_elec[$l])>9){	
						functionCall($shed_elec[$l][2],$shed_elec[$l][9][3],$shed_elec[$l][9][5],$shed_elec[$l][9][6],$shed_elec[$l][9][4],$shed_elec[$l][9][2],$shed_elec[$l][9][8]);
					}//end if 	
				}//end for 


 
  */
 
 
 
 
 
$conn->close();
?>
