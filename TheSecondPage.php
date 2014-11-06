<!DOCTYPE html>
<html>
<head>
<!--<link rel="stylesheet" type="text/css" href="style.css">-->
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
  <?
  	$sql = "SELECT course_id from Stream_Courses Where YEAR = ? AND stream_id = ?"; //setting up sql query
  	$sql_read = "SELECT SUBJ, CRSE_NUM, NAME FROM Courses WHERE course_id = ?";  //setting up second sql query
  	$stmt = $conn->prepare($sql);
  	$stmt->bind_param("ii",$year, $engStream);
	  $stmt->execute();
	  $stmt->bind_result($course_id);
	  echo "</table>";
	  echo "<table border='3' align='center'>
	  <tr>
	  <th>SUBJ</th>
	  <th>CRSE_NUM</th>
	  <th>NAME</th>
	  </tr>";
	  $Cour = array(); //for storing the retrived course_id info from Stream_courses
		while( $stmt->fetch())
		{
			$Cour[]=$course_id;
		}
	$stmt->close(); //closing the first query
	$classeslist = array(); //2d array for the subject number and name of class
	for($x=0;$x<count($Cour); $x++){ //individually querries all course_id values
		  $stmt = $conn->prepare($sql_read);
  		$stmt->bind_param("i", $Cour[$x]);
		  $stmt->execute();
		  $stmt->bind_result($subj,$courseNum,$courseName);
		  $stmt->fetch();
		  $stmt->close();
		  $classeslist[$x][]= $subj;  //storing in 2d array
		  $classeslist[$x][]= $courseNum;
		  $classeslist[$x][]= $courseName;
		}
	for($row = 0; $row<count($Cour);$row++)  //displaying to page
		{
			$SUBJ = $classeslist[$row][0];
			$CRSE_NUM= $classeslist[$row][1];
			$NAME= $classeslist[$row][2];
			echo "<tr>";
			echo "<td><input type='checkbox' value='$SUBJ'/>" . $SUBJ . "</td>";
			echo "<td>" . $CRSE_NUM . "</td>";
			echo "<td>" .$NAME . "</td>";
			echo "</tr>";
		}
	
	?>
  <script type="text/javascript"> //stuff i am not done yet!
   		function swap() {
    		<?echo ("buttion was pushed");?>
		}
    </script> 
    <div class="Trackswitch">
    <input type="checkbox" name="Trackswitch" class="Trackswitch-checkbox" id="trackSel" onclick = "swap()" checked>
    <label class="Trackswitch-label" for="trackSel">
    <span class="Trackswitch-inner"></span>
    </label>
    </div> 
    
</body>
</html>
