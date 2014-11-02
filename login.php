<head>
<!-- refrence art http://th09.deviantart.net/fs70/PRE/f/2013/177/3/e/wip__the_grid__tron__style_background_by_bioderp-d6aroka.jpg -->
	<link rel="stylesheet" type="text/css" href="style.css">
	<?php
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

	?>
</head>
<body>

<div id ="header">
	<div class = "container" id="banner" >
		<h1>Carleton Engineering Schedualer</h1>
	
	</div>
	<div class = "container" id ="Info">
		<form name="personal_info" method="post" action="init_info.php">
			<h2>&nbsp &nbsp &nbsp &nbsp  &nbsp User Name:
				<input type = "Text" name = "user_ID" ><br>
			</h2>	
			<h2>Student Number:
				<input type = "text" name = "STD_NUM">
			</h2>
			<h2> &nbsp&nbspYear:
				<select name="year">
					<option>1</option>
					<option>2</option>
					<option>3</option>
					<option>4</option>
				</select> 
			</h2>
			<h2>&nbsp &nbsp Stream:
				<select type = select name="Stream">

					<?php //pulling data from database for dropdown select.
						$sql = "SELECT stream FROM Stream;"; //selects all values under stream
						$r = $conn->query($sql);
						while( $row = $r->fetch_assoc())
						{
							if ($row['stream'] == 'all') break;
							echo "<option>" . $row['stream'] . "</option>";
						}
						$conn->close();
					?>
				</select>
			</h2>
			<input type = "submit" value="Submit">
	</div>
</div>

</body>
