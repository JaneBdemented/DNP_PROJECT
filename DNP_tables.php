<?php
$servername = "localhost";
$username = "root";
$password = ""; //<-your password
$DNPDB = "DNP_PROJECT";
echo "starting code";
// Create connection
$conn = new mysqli($servername, $username, $password);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Create database
$sql = "CREATE DATABASE DNP_PROJECT";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully";
} else {
    echo "Error creating database: " . $conn->error;
}
$conn->close();
//Create tables
$conn = new mysqli($servername, $username, $password, $DNPDB);
 //Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$sql = "CREATE TABLE Stream (stream_id tinyint(4) PRIMARY KEY, stream TEXT)";
if ($conn->query($sql) === TRUE) {
    echo "TABLE stream created successfully";
} else {
    echo "Error creating TABLE: " . $conn->error;
}
$sql = "CREATE TABLE Courses (course_id smallint primary key NOT NULL, 
							  subject char(4) NOT NULL, 
							  courseNUM smallint NOT NULL, 
							  name text, 
							  semester bit NOT NULL,
							  has_lab bit, 
							  has_tut bit)";
if ($conn->query($sql) === TRUE) {
    echo "TABLE Couses created successfully";
} else {
    echo "Error creating TABLE: " . $conn->error;
}
$sql = "CREATE TABLE Course_Timing (CoursTime_id smallint primary key NOT NULL, 
									course_id smallint, 
									type char(3), 
									time_s smallint, 
									time_e int, 
									room smallint, 
									capacity smallint, 
									has_prereq bit, 
									has_yearStanding tinyint(4),
									FOREIGN KEY (course_id) REFERENCES Courses(course_id) )";
if ($conn->query($sql) === TRUE) {
    echo "TABLE Couses_timing created successfully";
} else {
    echo "Error creating TABLE: " . $conn->error;
}
$sql = "CREATE TABLE Stream_Courses (StremCours_id smallint primary key NOT NULL, 
									 stream_id tinyint(4) NOT NULL, 
									 course_id smallint NOT NULL, 
									 year tinyint(4),
									 FOREIGN KEY (stream_id) REFERENCES Stream(stream_id),
									 FOREIGN KEY (course_id) REFERENCES Course_Timing(course_id))";
if ($conn->query($sql) === TRUE) {
    echo "TABLE Stream_Couses created successfully";
} else {
    echo "Error creating TABLE: " . $conn->error;
}
$conn->close();
?> 
