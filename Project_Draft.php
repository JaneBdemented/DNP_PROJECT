<?php
$conn=mysqli_connect("localhost","root","1234","DNP_PROJECT");
// Check connection
if (mysqli_connect_errno())
{
echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
/* $sql = "SELECT courses.SUBJ, courses.CRSE_NUM, courses.NAME FROM courses INNER JOIN stream_courses 
%ON courses.course_id = stream_courses.course_id INNER JOIN  stream ON stream_courses.stream_id " 
*/
$result = mysqli_query($conn,"SELECT SUBJ, CRSE_NUM,NAME  FROM courses WHERE SUBJ ='AERO'");

echo "<table border='3' align='center'>
<tr>
<th>SUBJ</th>
<th>CRSE_NUM</th>
<th>NAME</th>
</tr>";

while($row = mysqli_fetch_array($result))
{
$SUBJ = $row['SUBJ'] ;
$CRSE_NUM= $row['CRSE_NUM'] ;
$NAME= $row['NAME'];
echo "<tr>";
//echo '<td><input type='radio' value='$SUBJ'/>" </td>'; 
//echo '<td><input type="radio" value="$SUBJ"> . $SUBJ .</td>';
//echo "<input type='radio' value='$SUBJ' name='staff'> $name<br />"; 
echo "<td><input type='checkbox' value='$SUBJ'/>" . $SUBJ . "</td>";
echo "<td>" . $CRSE_NUM . "</td>";
echo "<td>" .$NAME . "</td>";

echo "</tr>";
}
echo "</table>";

mysqli_close($con);
?>
