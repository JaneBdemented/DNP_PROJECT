<?php

		$Name = $_POST["user_ID"];
		$Stream = $_POST["Stream"];
		$year = intval($_POST["Year"]);
		$Semester = $_POST["Semester"] ;
				
		$servername = "localhost";
		$username = "root";
		$password = "1234"; 
		$DNPDB = "DNP_PROJECT";
		// Create connection
		$conn = new mysqli($servername, $username, $password, $DNPDB);
		// Check connection
		if ($conn->connect_error) {
		echo("Connection failed: " . $conn->connect_error);	
		}
/********************************************************************************************************
	Converting user entered information to known database variables
********************************************************************************************************/			
		
		if ($stmt = $conn->prepare("SELECT stream_id FROM Stream WHERE stream=?")){
			$stmt->bind_param("s",$Stream);
			$stmt->execute();
			$stmt->bind_result($Stream_id);
			$stmt->fetch();
			$stmt->close();
		}
		
/********************************************************************************************************
	Initializing user specific database tables
********************************************************************************************************/	
		
		if($stmt = $conn->prepare("DELETE FROM `TempUser_Courses` WHERE 1")){
			$stmt->execute();
			$stmt->close();
		}
/********************************************************************************************************
	SQL statments 
********************************************************************************************************/		
  		$sqlAll = "INSERT INTO TempUser_Courses (course_id) SELECT course_id FROM Stream_Courses WHERE  stream_id = ? AND type_id = 0 ";		
  		$sql_read = "SELECT SUBJ, CRSE_NUM FROM Courses WHERE course_id = ?";
  		$sqlLab = "SELECT Lec_Complement.Lab_id, Lec_Complement.course_id FROM Lec_Complement JOIN TempUser_Courses ON Lec_Complement.course_id=TempUser_Courses.course_id";
  		$sqlCombo = "DELETE FROM TempUser_Courses WHERE course_id = ? ";
  		$sqlLec = "SELECT course_id FROM TempUser_Courses WHERE 1";
  		$sqlTiming = "SELECT SUB_VAL, TYPE, DAYS, time_s, time_e,ROOM_CAP FROM Course_Timing Where course_id = ?";
 		$sqlTree = "SELECT classes,Type From Stream_Tree WHERE stream_id = ? ";
 		$sqlElec = "SELECT course_id, type_id FROM Stream_Courses WHERE type_id = ? AND stream_id = ?";
		//$sqlBs= "SELECT streamtree_index FROM Stream_Tree WHERE stream_id = ? AND Type = 2 ";
/********************************************************************************************************
	Retrieving from database all relevant classes for stream year combo and storing into 
	database table TempUser_Courses
********************************************************************************************************/	  			
  			
  			$stmt = $conn->prepare($sqlAll);
  			$stmt->bind_param("i",$Stream_id);
			$stmt->execute();
			$stmt->close();
/********************************************************************************************************
	Pulling short-list of fall and winter courses for stream year combo from database.
	Note '0' indicates 'core' courses.
********************************************************************************************************/
			$TreeListFall=array();
			$TreeListWinter=array();
			$EleListFall=array();
			$EleListWinter=array();
			$stmt = $conn->prepare($sqlTree);
			$stmt->bind_param("i",$Stream_id);
			$stmt->execute();
			$stmt->bind_result($cla,$kind);
			while($stmt->fetch()){
				if($Semester== 0){
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
			
			
			//for($c=0; $c< count($complementery); $c++) { 
			
			//echo "<input type= 'hidden'  name='complementery[][]' value = '$complementery[$c]'/>" ;
			//} //end for 
			
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
			// print_r($Lectures);
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
	Linking course code, name, day and timing information into two arrays specified by semester of 
	occurrence. 
********************************************************************************************************/					
			
			//$classeslist = array();
			//$CourseListFall = array();
			//$CourseListWinter = array();
			for($x=0;$x<count($Lectures); $x++)
			{
				$stmt = $conn->prepare($sql_read);
  				$stmt->bind_param("i", $Lectures[$x]);
				$stmt->execute();
				$stmt->bind_result($subj,$courseNum);
				$stmt->fetch();
				$stmt->close();
				//if($Semester == 0){
					$SelectedCourseFallWinter[] = $subj." ".$courseNum ; 

					//$classesFall[]= array($SelectedCourse);
					
					//$classesFall2 = array_unique($classesFall);
					//$classesFall2key= array_keys($classesFall2);
					//print_r($SelectedCoursewinter);
		
				//}else{
					//$classesWinter[]= array($Lectures[$x],''.$subj." ".$courseNum.'', $courseName);
					//$SelectedCourseWinter[] = $subj." ".$courseNum ; 
					

					//$classesWinter[]= array($SelectedCourse);
					
				//}
				
			}
							//print_r($SelectedCourseFall);


			
			
						// Getting rid of duplaicate entries from the fall array  
						$UniqueFallWinter = array();
						foreach($SelectedCourseFallWinter as $value){
						
							if(!in_array($value,$UniqueFallWinter)== true){
							array_push(	$UniqueFallWinter,$value);
							}//end if 
						}//end foreach
						//print_r($UniqueFall);
						/* echo "<br>" ;
						echo "<br>" ;
						echo "<br>" ; */
						
						// Getting rid of duplaicate entries from the winter array  
						//$UniqueWinter = array();
						//foreach($SelectedCourseWinter as $value){
						
							//if(!in_array($value,$UniqueWinter) == true){
							//array_push($UniqueWinter,$value);
							//}//end if 
						//}//end foreach
						//print_r($UniqueWinter);
						
						//$result = array_merge($UniqueFall,$UniqueWinter);
						//print_r($result);
						
						$resultUnique = array() ;
						foreach($UniqueFallWinter as $value){
						if(!in_array($value,$resultUnique)== true){
							array_push($resultUnique,$value);
							}//end if 
						}//end foreach
						//print_r($resultUnique); 
						 
						//echo "<form method='post' action='Test23rdNov.php' name='edit'>";
						echo "<form method='post' action='displayprojectdraft.php' name='edit'>";
						echo "<h1 align='center'>Please select the courses you have taken</h1> " ;
						echo "<table width='200' border='4' align='center' ><tr>
						<th align='center' >Course Code</th></tr>";
						$arraylength = count($resultUnique) ;
						for($y =0; $y < $arraylength; $y++) {
						//echo $resultUnique[$y];
							if(empty($resultUnique)){
							echo 'No Data';
							}else{
							echo "<tr align='center' >";
							echo "<td > <input type='checkbox' name='querycourses[]' value='$resultUnique[$y]' />" .$resultUnique[$y]."</td>";"</tr>";
							echo "<input type = hidden  name='resultUnique[]' value = '$resultUnique[$y]'/>" ;
							echo "<input type = hidden name='Stream' value= '$Stream'/> ";
							echo "<input type = hidden name='Semester' value= $Semester/>";		
						
							}//end if 
						}//end for
						
						foreach( $complementery as $comp ) {
							foreach( $comp as $value ) {
							echo "<input type= 'hidden'  name='complementery[][]' value =".$value."/>" ;
							}
						}
						 echo "</table>;<input type='submit' value='Submit'> 
						 </form>";
						//end if 
					
				/* foreach ($resultUnique as $error)
				{
				echo "<br />Error: " . $error;
				} */
						//print_r($classesFall);
				
			
			?>
