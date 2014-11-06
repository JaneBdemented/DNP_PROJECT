<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="style.css">
	
	<?php
		$servername = "localhost";
		$username = "root";
		$password = ""; //<-your password
		$DNPDB = "DNP_PROJECT";
		//map posted info
		$Name = $_POST["user_ID"];
		$Std_num = intval($_POST["STD_NUM"]);
		$Stream = $_POST["Stream"];
		$year = intval($_POST["year"]);
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

		if ($stmt = $conn->prepare("INSERT INTO User (Stu_Num, Stream_id, Year) VALUES (?,?,?)")){
			$ret = $stmt->bind_param("iii",$Std_num, $stream_id, $year);
			$stmt->execute();
		}
		else 
		{
				    printf('errno: %d, error: %s', $conn->errno, $conn->error);
		}		
	$engStream = intval($Stream_id);
	?>
	
 </head>
  <body>
  	<div id="container">
  		<h1>Course Select</h1>
  	</div>
  	<script type="text/javascript">
   		function swap() {
    		<?echo ("buttion was pushed");?>
		}
    </script> 
    <div class="Trackswitch", id='select'>
    	<input type="checkbox" name="Trackswitch" class="Trackswitch-checkbox" id="trackSel" onclick = "swap()" checked>
    		<label class="Trackswitch-label" for="trackSel">
    			<span class="Trackswitch-inner"></span>
    		</label>
    </div> 
  		<?
  			$sql = "SELECT course_id from Stream_Courses Where (YEAR = ? AND stream_id = ?)";
  			$sql_read = "SELECT SUBJ, CRSE_NUM, NAME, SMSTR FROM Courses WHERE course_id = ? AND HAS_LAB = (1 or 0) ";
  			$stmt = $conn->prepare($sql);
  			$stmt->bind_param("ii",$year, $engStream);
			$stmt->execute();
			$stmt->bind_result($course_id);
			$Cour = array();
			while( $stmt->fetch())
			{
				$Cour[]=$course_id;
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
			}
		?>
	<div id= 'block'>	
		
			<table id='list' align = 'left' padding = '10%'> 
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
				echo "<td><input type='checkbox' value='$SUBJ'/><b>" . $SUBJ . "</b></td>";
				echo "<td><b>" . $CRSE_NUM . "</b></td>";
				echo "<td><b>" .$NAME . "</b></td>";
				echo "</tr>";
				}
			}
		?>
		
			</table>
			<table id = 'list' align='right'> 
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
				echo "<td><input type='checkbox' value='$SUBJ'/><b>" . $SUBJ . "</b></td>";
				echo "<td><b>" . $CRSE_NUM . "</b></td>";
				echo "<td><b>" .$NAME . "</b></td>";
				echo "</tr>";
				}
			}
		?>
			</table>
		
	<div/>
</body>
</html>
