<html>
<head>
	<link rel="stylesheet" type="text/css" href="style.css">
	<script type='text/javascript'>
	function findTimesFAll(){
		var classValFall[0][] = document.getElementsType('list');
		var classArray=[];
		for (i=0;i < classVal.length;i++){
			if(calssValFall[0][i]==selected){
			classArray[i] = classValFAll[];
			}
		}
		alert(classArray.length);
	}
	function findTimesWint(){
		var classValWint[1][] = document.getElementsType('list');
		var classArray=[];
		for (i=0;i < classVal.length;i++){
			classArray[i][i] = classVal;
		}
		alert(classArray.length);
	}
	</script>
	<?php
		$Name = $_GET["user_ID"];
		$Std_num = intval($_GET["STD_NUM"]);
		$Stream = $_GET["Stream"];
		$year = intval($_GET["year"]);	
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
		//store student info in table
		if ($stmt = $conn->prepare("SELECT stream_id FROM Stream WHERE stream=?")){
			$stmt->bind_param("s",$Stream);
			$stmt->execute();
			$stmt->bind_result($Stream_id);
			$stmt->fetch();
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
	?>
	
 </head>
 <body style="background-image: url('grid.jpg');">
<?
			$yearPlus = $year+1;
			echo $yearPlus;
  			$sql = "SELECT course_id from Stream_Courses Where ((YEAR = ? or YEAR = ?) AND stream_id = ?)";
  			$sql_read = "SELECT SUBJ, CRSE_NUM, NAME, SMSTR FROM Courses WHERE course_id = ? AND (HAS_LAB = (1 or 0) or HAS_TUT = (1 or 0))";
  			$stmt = $conn->prepare($sql);
  			$stmt->bind_param("iii",$year,$yearPlus,$Stream_id);
			$stmt->execute();
			$stmt->bind_result($course_id);
			$Cour = array();
			while( $stmt->fetch())
			{
				$Cour[]=$course_id;
			}
			$stmt->close();
			echo $course_id[1];
			$classeslist = array();
			for($x=0;$x<count($Cour); $x++)
			{
				$stmt = $conn->prepare($sql_read);
  				$stmt->bind_param("i", $Cour[$x]);
				$stmt->execute();
				$stmt->bind_result($subj,$courseNum,$courseName,$semester);
				$stmt->fetch();
				$stmt->close();
				$classeslist[$x][]= $subj;
				$classeslist[$x][]= $courseNum;
				$classeslist[$x][]= $courseName;
				$classeslist[$x][]= $semester;
				$classeslist[$x][]= $Cour[$x];
			}
		?>	
	<div id= 'block'>	
			<h2>Select the Courses you would like to take:</h2>
			<table id = 'list' name='Fall' align = 'left' padding = '10%'> 
				<th colspan='3'>Fall Semester</th>
				<tr>
					<th>SUBJ</th>
					<th>Course #</th>
					<th>NAME</th>
				</tr>
		<?
			for($row = 0; $row<count($Cour);$row++)
			{
				$SUBJ = $classeslist[$row][0];
				$CRSE_NUM= $classeslist[$row][1];
				$NAME= $classeslist[$row][2];
				$SEMSTR = $classeslist[$row][3];
				
				if(!is_null($SUBJ)&&$SEMSTR==0)
				{
				echo "<tr>";
				echo "<td><input type='checkbox' id = ".$row." value='SUBJ'/><b>" . $SUBJ . "</b></td>";
				echo "<td><b>" . $CRSE_NUM . "</b></td>";
				echo "<td><b>" .$NAME . "</b></td>";
				echo "</tr>";
				}
			}
		?>
		
			</table>
			<table id = 'list' name = 'wint' align='right'> 
				<th colspan='3'>Winter Semester</th>
				<tr>
					<th>SUBJ</th>
					<th>Course #</th>
					<th>NAME</th>
				</tr>	
		<?
			for($row = 0; $row<count($Cour);$row++)
			{
				$SUBJ = $classeslist[$row][0];
				$CRSE_NUM= $classeslist[$row][1];
				$NAME= $classeslist[$row][2];
				$SEMSTR = $classeslist[$row][3];
				
				if(!is_null($SUBJ)&&$SEMSTR==1)
				{
				echo "<tr>";
				echo "<td><input type='checkbox' id=".$row." value='$SUBJ'/><b>" . $SUBJ . "</b></td>";
				echo "<td><b>" . $CRSE_NUM . "</b></td>";
				echo "<td><b>" .$NAME . "</b></td>";
				echo "</tr>";
				}
			}
		?>
			</table>
			<input id = 'select' type = 'button' value = 'Select' onClick= 'findTimes();'/>	
	</div>
	</body>
	</html>
