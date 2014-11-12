<html>
<head>
	<link rel="stylesheet" type="text/css" href="style.css">
	<script type='text/javascript'>
	function findTimes(){
		var classVal = document.getElementsType('list');
		var classArray=[];
		for (i=0;i < classVal.length;i++){
			classArray[i] = classVal;
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
  			$sql = "SELECT course_id, YEAR from Stream_Courses Where stream_id = ?";
  			$sql_read = "SELECT SUBJ, CRSE_NUM, NAME, SMSTR FROM Courses WHERE course_id = ? AND (HAS_LAB = (1 or 0) or HAS_TUT = (1 or 0))";

  			$stmt = $conn->prepare($sql);
  			$stmt->bind_param("i", $Stream_id);
			$stmt->execute();
			$stmt->bind_result($course_id, $year);
			$Cour = array();
			$count = 0;
			while( $stmt->fetch())
			{
				$Cour[$count]=$course_id;
				$YearArray[$count]=$year;
				$count++;
			}
			$stmt->close();
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
				$classeslist[$x][]= $YearArray[$x];
			}
		?>	
	<div id= 'block'>	
		<h2>Select the Courses you have taken:</h2>
			<table id = 'list' name='Fall' align = 'left' padding = '10%'> 
				<th colspan='3'>Year 1 Courses</th>
				<tr>
					<th>SUBJ</th>
					<th>Course #</th>
					<th>NAME</th>
				</tr>
		<?
			$tempSubj= NULL;
			$tempCurs_num = NULL;
			for($row = 0; $row<count($Cour);$row++)
			{
				$SUBJ = $classeslist[$row][0];
				$CRSE_NUM= $classeslist[$row][1];
				$NAME= $classeslist[$row][2];
				$Year = $classeslist[$row][5];
				if(($SUBJ != $tempSubj) && ($CRSE_NUM != $tempCurs_num)){
				
					if(!is_null($SUBJ)&&$Year==1)
					{
						echo "<tr>";
						echo "<td><input type='checkbox' id = ".intval($classeslist[$row][4])." value='SUBJ'/><b>" . $SUBJ . "</b></td>";
						echo "<td><b>" . $CRSE_NUM . "</b></td>";
						echo "<td><b>" .$NAME . "</b></td>";
						echo "</tr>";
					}
				}
				if(!is_null($SUBJ)){
					$tempSubj = $SUBJ;
					$tempCurs_num = $CRSE_NUM;
				}
			}
		?>	
			</table>
			<table id = 'list' name = 'wint' align='right'> 
				<th colspan='3'>Year 2 Courses</th>
				<tr>
					<th>SUBJ</th>
					<th>Course #</th>
					<th>NAME</th>
				</tr>	
		<?
			$tempSubj= NULL;
			$tempCurs_num = NULL;
			for($row = 0; $row<count($Cour);$row++)
			{
				$SUBJ = $classeslist[$row][0];
				$CRSE_NUM= $classeslist[$row][1];
				$NAME= $classeslist[$row][2];
				$Year = $classeslist[$row][5];
				if(($SUBJ != $tempSubj) && ($CRSE_NUM != $tempCurs_num)){
					if(!is_null($SUBJ)&&$Year==2)
					{
						echo "<tr>";
						echo "<td><input type='checkbox' id=".intval($classeslist[$row][4])." value='$SUBJ'/><b>" . $SUBJ . "</b></td>";
						echo "<td><b>" . $CRSE_NUM . "</b></td>";
						echo "<td><b>" .$NAME . "</b></td>";
						echo "</tr>";
					}
				}
				if(!is_null($SUBJ)){
					$tempSubj = $SUBJ;
					$tempCurs_num = $CRSE_NUM;
				}
			}	
		?>
			</table>
			<table id = 'list' name = 'wint' align='right'> 
				<th colspan='3'>Year 3 Courses</th>
				<tr>
					<th>SUBJ</th>
					<th>Course #</th>
					<th>NAME</th>
				</tr>	
		<?
			$tempSubj= NULL;
			$tempCurs_num = NULL;		
			for($row = 0; $row<count($Cour);$row++)
			{
				$SUBJ = $classeslist[$row][0];
				$CRSE_NUM= $classeslist[$row][1];
				$NAME= $classeslist[$row][2];
				$Year = $classeslist[$row][5];
				
				if(($SUBJ != $tempSubj) && ($CRSE_NUM != $tempCurs_num)){
					if(!is_null($SUBJ)&&$Year==3)
					{
						echo "<tr>";
						echo "<td><input type='checkbox' id=".intval($classeslist[$row][4])." value='$SUBJ'/><b>" . $SUBJ . "</b></td>";
						echo "<td><b>" . $CRSE_NUM . "</b></td>";
						echo "<td><b>" .$NAME . "</b></td>";
						echo "</tr>";
					}
				}
				if(!is_null($SUBJ)){
					$tempSubj = $SUBJ;
					$tempCurs_num = $CRSE_NUM;
				}
			}	
		?>
			</table>
			<table id = 'list' name = 'wint' align='right'> 
				<th colspan='3'>Year 4 Courses</th>
				<tr>
					<th>SUBJ</th>
					<th>Course #</th>
					<th>NAME</th>
				</tr>	
		<?
			$tempSubj= NULL;
			$tempCurs_num = NULL;		
			for($row = 0; $row<count($Cour);$row++)
			{
				$SUBJ = $classeslist[$row][0];
				$CRSE_NUM= $classeslist[$row][1];
				$NAME= $classeslist[$row][2];
				$Year = $classeslist[$row][5];
				
				if(($SUBJ != $tempSubj) && ($CRSE_NUM != $tempCurs_num)){
					if(!is_null($SUBJ)&&$Year==4)
					{
						echo "<tr>";
						echo "<td><input type='checkbox' id=".intval($classeslist[$row][4])." value='$SUBJ'/><b>" . $SUBJ . "</b></td>";
						echo "<td><b>" . $CRSE_NUM . "</b></td>";
						echo "<td><b>" .$NAME . "</b></td>";
						echo "</tr>";
					}
				}
				if(!is_null($SUBJ)){
					$tempSubj = $SUBJ;
					$tempCurs_num = $CRSE_NUM;
				}
			}	
		?>
			</table>
		<input type = 'button' value = 'Select' onClick= 'findTimes();'/>	
	</div>
	</body>
</html>
