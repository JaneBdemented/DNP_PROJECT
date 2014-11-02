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
		$year_stand = intval($_POST["year"]);
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
			$stmt->bind_result($stream_id);
			$stmt->fetch();
			$stmt->close();
		}

		if ($stmt = $conn->prepare("INSERT INTO User (Stu_Num, stream_id, Year) VALUES (?,?,?)")){
			$ret = $stmt->bind_param("iii",$Std_num, $stream_id, $year_stand);
			$stmt->execute();
			$stmt->close();
		}
		else 
		{
				    printf('errno: %d, error: %s', $conn->errno, $conn->error);
		}		
	?>
 </head>
  <body>
  <script type="text/javascript">
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
