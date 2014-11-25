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
 		$sqlElec = "SELECT course_id, type_id FROM Stream_Courses WHERE type_id = ? AND stream_id = ?";

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
			$stmt->close();

/********************************************************************************************************
	Retriving list of Required electives distinguished by type (complementry studys, Basic science,
	note A,b,c,...ect.) 
********************************************************************************************************/			
			
			$Tmp1 = array_unique($EleListFall);
			$Tmp2 = array_unique($EleListWinter);
			$electivesFall=array();
			$electivesWinter=array();
			$Hold=array();
			$stmt = $conn->prepare($sqlElec);
			$stmt->execute();

			for($i=0;$i<count($Tmp1);$i++){
				$stmt->bind_param("ii",$Tmp1[$i], $year);
				$stmt->bind_result($elec_id, $type);
				while($stmt->fetch()){
					$Hold[]=$elec_id;
					$Hold[]=$type;
				}
				$electivesFall = array_merge($electivesFall, $Hold);
			}	
			for($i=0;$i<count($Tmp2);$i++){
				$stmt->bind_param("ii",$Tmp2[$i], $year);
				$stmt->bind_result($elec_id, $type);
				while($stmt->fetch()){
					$Hold[]=$elec_id;
					$Hold[]=$type;
				}
				$electivesWinter = array_merge($electivesWinter, $Hold);
			}	

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
			
						/*Concatinating the Complementry class scheduial information to the complementry id*/
						array_push($complementery[$row], $sec, $type, $days, $s_time, $e_time,$space);
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
	$offset1 = array_search($start, $timeE[$day]);
	$offset2 = array_search($stop, $timeE[$day]);
	$offset3 = array_search($start, $timeO[$day]);
	$offset4 = array_search($stop, $timeO[$day]);

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
function fallClasses($TreeListFall,$classesFall,$complementery){			
			$shedF = array();
			shuffle($TreeListFall);
			shuffle($classesFall);
			Shuffle($complementery);
			$timeE=newTimeTable();
			$timeO=newTimeTable();


			for($i=0;$i<count($TreeListFall);$i++){
				
				$NoMatch = 0;	
				$check=array();
				$select = $TreeListFall[$i];
				for($k=0;$k<Count($classesFall);$k++){
					$day = 0;
					if ($select == $classesFall[$k][1]){
						$day = str_split($classesFall[$k][5]);
						$j=0;
						if(count($day)>1){
							if((in_array($classesFall[$k][6],$timeE[$day[$j]])==in_array($classesFall[$k][7],$timeE[$day[$j]]))
								&&(in_array($classesFall[$k][6],$timeO[$day[$j]])==in_array($classesFall[$k][7],$timeO[$day[$j]]))){
								if((in_array($classesFall[$k][6],$timeE[$day[$j+1]])==in_array($classesFall[$k][7],$timeE[$day[$j+1]]))
									&&(in_array($classesFall[$k][6],$timeO[$day[$j+1]])==in_array($classesFall[$k][7],$timeO[$day[$j+1]]))&&($classesFall[$k][8]>=1)){
									$check[0]=1;
								}
							}else{
									$check[0]=0;
							}			
						}else{
							if( (in_array($classesFall[$k][6],$timeE[$day[$j]])==in_array($classesFall[$k][7],$timeE[$day[$j]]))
								&&(in_array($classesFall[$k][6],$timeO[$day[$j]])==in_array($classesFall[$k][7],$timeO[$day[$j]])&&($classesFall[$k][8]>=1))){
								$check[0]=1;
							}
						}	

						if(count($check)>0 && $check[0]==1){
							$compTemp = subArray($complementery, $classesFall[$k][0]);
						
							if (is_null($compTemp)){
								$shedF[$i]=$classesFall[$k];
								cleartime($classesFall[$k][6],$classesFall[$k][7],$timeE,$timeO,$day[$j],'B');
								
								
								if(count($day)>1){
									cleartime($classesFall[$k][6],$classesFall[$k][7],$timeE,$timeO,$day[$j+1],'B');
									
								}
								$NoMatch=1;
								break;

							}elseif(is_array($compTemp)){
								$days = str_split($compTemp[4]);
									if(count($days)==1){
										$week = str_split($compTemp[3]);		
										if(in_array('O', $week)){
											if((in_array($compTemp[5],$timeO[$compTemp[4]])==in_array($compTemp[6],$timeO[$compTemp[4]]))&&($compTemp[7]>=1)){
											
												$shedF[$i]=$classesFall[$k];
												array_push($shedF[$i], $compTemp);									
												cleartime($classesFall[$k][6],$classesFall[$k][7],$timeE,$timeO,$day[$j],'B');
												cleartime($compTemp[5],$compTemp[6],$timeE,$timeO,$compTemp[4],'O');
												if(count($day)>1){											
												cleartime($classesFall[$k][6],$classesFall[$k][7],$timeE,$timeO,$day[$j+1],'B');					
												}
										
											$NoMatch=1;
											break;
										}
									}elseif(in_array('E',$week)){
										if((in_array($compTemp[5],$timeE[$compTemp[4]])==in_array($compTemp[6],$timeE[$compTemp[4]]))&&($compTemp[7]>=1)){
										
											$shedF[$i]=$classesFall[$k];
											array_push($shedF[$i], $compTemp);
											cleartime($classesFall[$k][6],$classesFall[$k][7],$timeE,$timeO,$day[$j],'B');						
											cleartime($compTemp[5],$compTemp[6],$timeE,$timeO,$compTemp[4],'E');
											if(count($day)>1){											
												cleartime($classesFall[$k][6],$classesFall[$k][7],$timeE,$timeO,$day[$j+1],'B');
											}
											
											$NoMatch=1;
											break;
										}
									}elseif((in_array($compTemp[5],$timeO[$compTemp[4]])==in_array($compTemp[6],$timeO[$compTemp[4]]))&&
										(in_array($compTemp[5],$timeE[$compTemp[4]])==in_array($compTemp[6],$timeE[$compTemp[4]]))&&($compTemp[7]>=1)){

										$shedF[$i]=$classesFall[$k];
										array_push($shedF[$i], $compTemp);										
										cleartime($classesFall[$k][6],$classesFall[$k][7],$timeE,$timeO,$day[$j],'B');						
										cleartime($compTemp[5],$compTemp[6],$timeE,$timeO,$compTemp[4],'B');
										if(count($day)>1){										
											cleartime($classesFall[$k][6],$classesFall[$k][7],$timeE,$timeO,$day[$j+1],'E');
										}
										
										$NoMatch=1;
										break;
									}else{
										$NoMatch=0;
									}
								}else{
									if((in_array($compTemp[5],$timeO[$compTemp[4]])==in_array($compTemp[6],$timeO[$days[0]]))&&
									   (in_array($compTemp[5],$timeE[$compTemp[4]])==in_array($compTemp[6],$timeE[$days[0]]))&&
									   (in_array($compTemp[5],$timeO[$compTemp[4]])==in_array($compTemp[6],$timeO[$days[1]]))&&
									   (in_array($compTemp[5],$timeE[$compTemp[4]])==in_array($compTemp[6],$timeE[$days[1]]))&&($compTemp[7]>=1)){
											$shedF[$i]=$classesFall[$k];
											array_push($shedF[$i], $compTemp);										
											cleartime($classesFall[$k][6],$classesFall[$k][7],$timeE,$timeO,$day[$j],'B');						
											cleartime($compTemp[5],$compTemp[6],$timeE,$timeO,$days[0],'B');
											cleartime($compTemp[5],$compTemp[6],$timeE,$timeO,$days[1],'B');
											if(count($day)>1){										
											cleartime($classesFall[$k][6],$classesFall[$k][7],$timeE,$timeO,$day[$j+1],'E');
											}

									   }
								}
							}		
						}
					}
				}

			}
			return($shedF);	
}				
function winterClasses($TreeListWinter,$classesWinter,$complementery){			
			$shedW = array();
			shuffle($TreeListWinter);
			shuffle($classesWinter);
			Shuffle($complementery);
			$timeE=newTimeTable();
			$timeO=newTimeTable();


			for($i=0;$i<count($TreeListWinter);$i++){
				
				$NoMatch = 0;	
				$check=array();
				$select = $TreeListWinter[$i];
				for($k=0;$k<Count($classesWinter);$k++){
					$day = 0;
					if ($select == $classesWinter[$k][1]){
						$day = str_split($classesWinter[$k][5]);
						$j=0;
						if(count($day)>1){
							if((in_array($classesWinter[$k][6],$timeE[$day[$j]])==in_array($classesWinter[$k][7],$timeE[$day[$j]]))
								&&(in_array($classesWinter[$k][6],$timeO[$day[$j]])==in_array($classesWinter[$k][7],$timeO[$day[$j]]))){
								if((in_array($classesWinter[$k][6],$timeE[$day[$j+1]])==in_array($classesWinter[$k][7],$timeE[$day[$j+1]]))
									&&(in_array($classesWinter[$k][6],$timeO[$day[$j+1]])==in_array($classesWinter[$k][7],$timeO[$day[$j+1]]))
									&&$classesWinter[$k][8]>=1){
									$check[0]=1;
								}
							}else{
									$check[0]=0;
							}			
						}else{
							if( (in_array($classesWinter[$k][6],$timeE[$day[$j]])==in_array($classesWinter[$k][7],$timeE[$day[$j]]))
								&&(in_array($classesWinter[$k][6],$timeO[$day[$j]])==in_array($classesWinter[$k][7],$timeO[$day[$j]]))
								&&$classesWinter[$k][8]>=1){
								$check[0]=1;
							}
						}	

						if(count($check)>0 && $check[0]==1){
							$compTemp = subArray($complementery, $classesWinter[$k][0]);
						
							if (is_null($compTemp)){
								$shedW[$i]=$classesWinter[$k];
								cleartime($classesWinter[$k][6],$classesWinter[$k][7],$timeE,$timeO,$day[$j],'B');
								
								
								if(count($day)>1){
									cleartime($classesWinter[$k][6],$classesWinter[$k][7],$timeE,$timeO,$day[$j+1],'B');
									
								}
								$NoMatch=1;
								break;

							}elseif(is_array($compTemp)){

								$week = str_split($compTemp[3]);
								$days = str_split($compTemp[4]);		
								if(in_array('O', $week)){
									if((in_array($compTemp[5],$timeO[$days[0]])==in_array($compTemp[6],$timeO[$days[0]]))&&
									   (in_array($compTemp[5],$timeO[$days[1]])==in_array($compTemp[6],$timeO[$days[1]]))&&$compTemp[7]>=1){
										
										$shedW[$i]=$classesWinter[$k];
										array_push($shedW[$i], $compTemp);									
										cleartime($classesWinter[$k][6],$classesWinter[$k][7],$timeE,$timeO,$day[$j],'B');
										cleartime($compTemp[5],$compTemp[6],$timeE,$timeO,$days[0],'O');

										if(count($day)>1){											
											cleartime($classesWinter[$k][6],$classesWinter[$k][7],$timeE,$timeO,$day[$j+1],'B');					
										}
										
										$NoMatch=1;
										break;
									}
								}elseif(in_array('E',$week)){
									if((in_array($compTemp[5],$timeE[$days[0]])==in_array($compTemp[6],$timeE[$days[0]]))&&
									   (in_array($compTemp[5],$timeE[$days[1]])==in_array($compTemp[6],$timeE[$days[1]]))&& $compTemp[7]>=1){
										
										$shedW[$i]=$classesWinter[$k];
										array_push($shedW[$i], $compTemp);
										cleartime($classesWinter[$k][6],$classesWinter[$k][7],$timeE,$timeO,$day[$j],'B');						
										cleartime($compTemp[5],$compTemp[6],$timeE,$timeO,$days[0],'E');
										if(count($day)>1){											
											cleartime($classesWinter[$k][6],$classesWinter[$k][7],$timeE,$timeO,$day[$j+1],'B');
										}
										
										$NoMatch=1;
										break;
									}
								}elseif(count($days)>1)
									if((in_array($compTemp[5],$timeO[$days[1]])==in_array($compTemp[6],$timeO[$days[1]]))&&
									  (in_array($compTemp[5],$timeE[$days[1]])==in_array($compTemp[6],$timeE[$days[1]]))&&
									  (in_array($compTemp[5],$timeO[$days[0]])==in_array($compTemp[6],$timeO[$days[0]]))&&
									  (in_array($compTemp[5],$timeE[$days[0]])==in_array($compTemp[6],$timeE[$days[0]]))&&$compTemp[7]>=1){

										$shedW[$i]=$classesWinter[$k];
										array_push($shedW[$i], $compTemp);										
										cleartime($classesWinter[$k][6],$classesWinter[$k][7],$timeE,$timeO,$day[$j],'B');						
										cleartime($compTemp[5],$compTemp[6],$timeE,$timeO,$days[0],'B');
										if(count($days)>1){
											cleartime($compTemp[5],$compTemp[6],$timeE,$timeO,$days[1],'B');	
										}
										if(count($day)>1){										
											cleartime($classesWinter[$k][6],$classesWinter[$k][7],$timeE,$timeO,$day[$j+1],'E');
										}
										
										$NoMatch=1;
										break;
								}elseif((in_array($compTemp[5],$timeO[$days[0]])==in_array($compTemp[6],$timeO[$days[0]]))&&
									  (in_array($compTemp[5],$timeE[$days[0]])==in_array($compTemp[6],$timeE[$days[0]]))&&$compTemp[7]>=1){
										$shedW[$i]=$classesWinter[$k];
										array_push($shedW[$i], $compTemp);										
										cleartime($classesWinter[$k][6],$classesWinter[$k][7],$timeE,$timeO,$day[$j],'B');						
										cleartime($compTemp[5],$compTemp[6],$timeE,$timeO,$days[0],'B');
										if(count($day)>1){										
											cleartime($classesWinter[$k][6],$classesWinter[$k][7],$timeE,$timeO,$day[$j+1],'E');
										}
										$NoMatch=1;
								}else{
									$NoMatch=0;
								}
								
							}		
						}
					}
				}
				//if($NoMatch==0){
				//	$tryAgain= winterClasses($TreeListWinter,$classesWinter,$complementery);
				//	return($tryAgain);
				//}
			}
			return($shedW);	
}										
				
		

		/*****************************************************************
			selectElectives() Selecting required electives, second to 
			core courses. selection based solely on first fit. Note 
			shuffling of array allows for random behaviours in selecting. 
		*****************************************************************/
		function selectElectives(){

		}
	


				$shed1F = fallClasses($TreeListFall,$classesFall,$complementery);
				//$shed2F = fallClasses($TreeListFall,$classesFall,$complementery);
				$shed1W = winterClasses($TreeListWinter,$classesWinter,$complementery);
			//	$shed2W = winterClasses($TreeListWinter,$classesWinter,$complementery);

				$conn->close();			
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
									echo	"<td>".$shed1F[$i][9][2]."-".$shed1F[$i][9][3]."-".$shed1F[$i][9][4]."-".$shed1F[$i][9][5]."-".$shed1F[$i][9][6]."</td></tr>";
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
								if(count($shed2F[$i])>8){	
									echo	"<td>".$shed2F[$i][8][2]."-".$shed2F[$i][8][3]."-".$shed2F[$i][8][4]."-".$shed2F[$i][8][5]."-".$shed2F[$i][8][6]."</td></tr>";
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
								if($shed2W[$i][8]!=0){	
									echo "<td>".$shed2W[$i][8][2]."-".$shed2W[$i][8][3]."-".$shed2W[$i][8][4]."-".$shed2W[$i][8][5]."-".$shed2W[$i][8][6]."</td></tr>";
								}else{
									echo "<td></td></tr>";
								}	
							 
					}
				?>	
			</table>				
	
