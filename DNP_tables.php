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
$sql = "CREATE TABLE Stream (stream_id int(4) PRIMARY KEY, stream varchar(8))";
if ($conn->query($sql) === TRUE) {
echo "TABLE stream created successfully";
} else {
echo "Error creating TABLE Stream: " . $conn->error;
}
$sql = "CREATE TABLE Courses (course_id int(4) primary key NOT NULL,
SUBJ varchar(4) NOT NULL,
CRSE_NUM int(4) NOT NULL,
NAME text,
SMSTR int(1) NOT NULL,
HAS_LAB varchar(1),
HAS_TUT varchar(1))";
if ($conn->query($sql) === TRUE) {
echo "TABLE Couses created successfully";
} else {
echo "Error creating TABLE Courses: " . $conn->error;
}
$sql = "CREATE TABLE Course_Timing (CoursTime_id int(4) primary key NOT NULL,
course_id int(4),
SUB_VAL varchar(3),
TYPE varchar(3),
DAYS varchar(4),
time_s time(4), 
time_e time(4),
ROOM_CAP varchar(3),
has_prereq int(4),
has_yearStanding int(1),
FOREIGN KEY (course_id) REFERENCES Courses(course_id) )";
if ($conn->query($sql) === TRUE) {
echo "TABLE Couses_timing created successfully";
} else {
echo "Error creating TABLE Course_Timing: " . $conn->error;
}
$sql = "CREATE TABLE Course_Type (type_id int(1) primary key NOT NULL,
Type varchar(10))";
if ($conn->query($sql) === TRUE) {
echo "TABLE Couses_Type created successfully";
} else {
echo "Error creating TABLE Course_Type: " .$conn->error;
}
$sql = "CREATE TABLE Stream_Courses (Stream_Courses_id smallint primary key NOT NULL,
stream_id int(4) NOT NULL,
course_id int(1) NOT NULL,
YEAR tinyint(1),
type_id int(1),
FOREIGN KEY (stream_id) REFERENCES Stream(stream_id),
FOREIGN KEY (course_id) REFERENCES Course_Timing(course_id),
FOREIGN KEY (type_id) REFERENCES Course_Type(type_id))";
if ($conn->query($sql) === TRUE) {
echo "TABLE Stream_Couses created successfully";
} else {
echo "Error creating TABLE Stream_Courses: " .$conn->error;
}

$sql = "CREATE TABLE Stream_tree (streamtree_index int(11) primary key AUTO_INCREMENT,
Stream_id int(1) NOT NULL,
year int(1) NOT NULL,
semester int(1) NOT NULL,
classes varchar(9),
Type int(1),
FOREIGN KEY (Stream_id) REFERENCES Stream(stream_id))";
if ($conn->query($sql) === TRUE) {
echo "TABLE Stream_Couses created successfully";
} else {
echo "Error creating TABLE Stream_tree: " . $conn->error;
}
$sql = "CREATE TABLE Lec_Complement (Lab_index smallint primary key AUTO_INCREMENT,
LAB_id int(4) NOT NULL,
course_id int(1) NOT NULL,
FOREIGN KEY (Lab_id) REFERENCES Courses(course_id),
FOREIGN KEY (course_id) REFERENCES Courses(course_id))";
if ($conn->query($sql) === TRUE) {
echo "TABLE Stream_Couses created successfully";
} else {
echo "Error creating TABLE Lec_Complement: " . $conn->error;
}
$sql = "CREATE TABLE TempUser_courses ( TempUser_index smallint primary key AUTO_INCREMENT,
course_id int(1) NOT NULL,
FOREIGN KEY (course_id) REFERENCES Courses(course_id))";
if ($conn->query($sql) === TRUE) {
echo "TABLE Stream_Couses created successfully";
} else {
echo "Error creating TABLE TempUser_courses: " . $conn->error;
}
$conn->close();
?> 
