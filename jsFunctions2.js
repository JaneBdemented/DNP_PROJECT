//scheduleStamp(courseName, courseNumber, courseSection, beginTime, endTime, weeks, day);
function scheduleStamp(courseName, courseNumber, courseSection, beginTime, endTime, weeks, day){
 
        //alert(courseName+courseNumber+" "+courseSection+" "+beginTime+" "+endTime+" "+weeks);
        var explode = beginTime.split(":");      // i.e. (00, 08, 35)   explode the time string in order to reconstruct it in the desired form
        var timeStamp;                          //Time stamp that will go into the Time column in the schedule
        var initialTime, initialTimeMinute, end, endMinutes;
        var classStart = "";
        var classEnd = "";
 
        /* If statments that will determine the time in minutes and will give the desired time stamp
           There are two times' structures expected, one with 3 number (835, 905 ..etc) and the other
           structure is with 4 numbers (1005, 1135 .. etc), and hence I'm checking for length == 3 or 4
           if 3 then the first number is the hour but if 4 then the first two numbers are the hour */
        //alert(explode[1][0] + explode[1][1]);
        if(explode[1][0] == '0'){
 
          //Here I'm extracting the time information in order to convert it to minutes (i.e. 835)
          initialTime = parseInt(explode[1][1]);        //Get the hour 8
          initialTimeMinute = parseInt(explode[2]);  //Get the minutes 35
 
          //alert("initialTime: "+ initialTime + " initialTimeMinute: " + initialTimeMinute);
        } else {
          //Here I'm extracting the time information in order to convert it to minutes (i.e. 1005)
          initialTime = parseInt(explode[1]);        //Get the hour 10
          initialTimeMinute = parseInt(explode[2]);  //Get the minutes 05
 
          //alert("initialTime: "+ initialTime + " initialTimeMinute: " + initialTimeMinute);
        }
        classStart = (classStart.concat(initialTime)).concat(explode[2]);
        //alert(classStart);
 
        //Same reasoning as the previous if-else statment
        var explode = endTime.split(":");
        if(explode[1][0] == '0'){
          end = parseInt(explode[1][1]);
          endMinutes = parseInt(explode[2]);
          //alert("end: "+ end + " endMinutes: " + endMinutes);
        } else {
          end = parseInt(explode[1]);
          endMinutes = parseInt(explode[2]);
          //alert("end: "+ end + " endMinutes: " + endMinutes);
        }
        classEnd = (classEnd.concat(end)).concat(explode[2]);
        //alert(classEnd);
 
        //This is the class duration in minutes
        var classDuration = (((end-8)*60)+endMinutes) - (((initialTime-8)*60)+initialTimeMinute) + 10;
        //alert(classDuration);
        //Span will specify how many rows this will span
        var span = classDuration/30 + 1;
        //alert(span);
 
        //Determine the date and then stamp accordingly - This may change depending on the info passed in
        var days = 0;
        if(day.toLowerCase() =='mon') days = 0;
        if(day.toLowerCase() =='tue') days = 1;
        if(day.toLowerCase() =='wed') days = 2;
        if(day.toLowerCase() =='thu') days = 3;
        if(day.toLowerCase() =='fri') days = 4;
         
 
        var colors;
        if(courseName.toLowerCase() == "math") colors = "#98FB98";
        else if(courseName.toLowerCase() == "sysc") colors = "#DDA0DD";
        else if(courseName.toLowerCase() == "elec") colors = "#FFB733";
        else if(courseName.toLowerCase() == "ecor") colors = "#4D4D4D";
        else colors = "lightgray";
 
        //-------------------------------------------------------------------------------------------
 
        /* All the concat() you will see in the following code is used to make the string that will identify the cell
           in the table */
 
        var initialTimeNum = Number(initialTime);
        //alert(initialTimeNum);
        var initialTimeMinuteNum = Number(initialTimeMinute);
        //alert(initialTimeMinuteNum);
        //classStart = (classStart.concat(initialTime)).concat(initialTimeMinute);
        //alert(classStart);
 
 
 
        //Stamp the course to the very first row that it will span
        // ***************************** Stamp ***************************** //
        var cellToChange = document.getElementById((classStart.concat(day)).concat(weeks));
        cellToChange.innerHTML = courseName+courseNumber + " " + courseSection;
        cellToChange.style.backgroundColor = colors;
        if(courseName.toLowerCase() == "ecor"){
          cellToChange.style.color = "white";
        }
        cellToChange.style.borderTop = "2px solid white";
        // ***************************** Stamp ***************************** //
 
        if(day != "FRI"){ //This if statement was added because the right border of the Fri cell will override the border of the table, NOT GOOD
              cellToChange.style.borderRight = "2px solid white";
        }
        cellToChange.style.borderLeft = "2px solid white";
        var done = 0;
 
        for(var index=1; index < span-1 ; index++){
          var zeroMin = "0";
          var td_id = "";
          initialTimeMinuteNum += 30;
          if(initialTimeMinuteNum>60){
            initialTimeMinuteNum-=60;
            initialTimeNum+=1;
 
            zeroMin = zeroMin.concat(initialTimeMinuteNum.toString());
            td_id = (((td_id.concat(initialTimeNum.toString())).concat(zeroMin)).concat(day)).concat(weeks);
            //alert(td_id);
            // *********************** Stamp *********************** //
            var cellToChange = document.getElementById(td_id);
            if(done == 0){
              cellToChange.innerHTML = classStart + " - " + classEnd;
              done+=1;
            } else {
              cellToChange.innerHTML = "";
            }
            if(courseName.toLowerCase() == "ecor"){
              cellToChange.style.color = "white";
            }
            cellToChange.style.backgroundColor = colors;
            if(day != "FRI"){
              cellToChange.style.borderRight = "2px solid white";
            }
            cellToChange.style.borderLeft = "2px solid white";
            // *********************** Stamp *********************** //
 
 
          } else {
 
            td_id = (((td_id.concat(initialTimeNum.toString())).concat(initialTimeMinuteNum.toString())).concat(day)).concat(weeks);
            //alert(td_id);
            // *********************** Stamp *********************** //
            var cellToChange = document.getElementById(td_id);
            if(done == 0){
              cellToChange.innerHTML = classStart + " - " + classEnd;
              done+=1;
            } else {
              cellToChange.innerHTML = "";
            }
            cellToChange.style.backgroundColor = colors;
            if(courseName.toLowerCase() == "ecor"){
              cellToChange.style.color = "white";
            }
            if(day != "FRI"){
              cellToChange.style.borderRight = "2px solid white";
            }
            cellToChange.style.borderLeft = "2px solid white";
            // *********************** Stamp *********************** //
 
          }
        }
      }
 
      function runTest(){
 
        // CourseName,Type, beginTime, endTime, days, courseSection, EOB
        //alert("IN runTest");
        testNumber = Math.floor((Math.random() * 4) + 1);
 
        if(testNumber == 1){
          testStamp('MATH1004','LEC','00:08:35','00:09:55','MTWRF','A','B');
          testStamp('PHYS1004','LAB','00:10:05','00:11:25','MTWRF','A1','B');
          testStamp('SYSC1004','LAB','00:11:35','00:12:55','MTWRF','A1','B');
          testStamp('ECOR1004','LAB','00:13:05','00:14:25','MTWRF','A1','B');
          testStamp('ELEC1004','LAB','00:14:35','00:15:55','MTWRF','A1','B');
          testStamp('PHYS1004','LAB','00:16:05','00:17:25','MTWRF','A1','B');
          testStamp('ELEC1004','LAB','00:17:35','00:18:55','MTWRF','A1','B');
          testStamp('ECOR1004','LAB','00:19:05','00:20:25','MTWRF','A1','B');
          testStamp('ECOR1004','LAB','00:20:35','00:21:55','MTWRF','A1','B');
        } else if(testNumber == 2){
          testStamp('MATH1004','LEC','00:08:35','00:09:55','MTWRF','A','O');
          testStamp('PHYS1004','LAB','00:10:05','00:11:25','MTWRF','A1','O');
          testStamp('SYSC1004','LAB','00:11:35','00:12:55','MTWRF','A1','O');
          testStamp('ECOR1004','LAB','00:13:05','00:14:25','MTWRF','A1','O');
          testStamp('ELEC1004','LAB','00:14:35','00:15:55','MTWRF','A1','O');
          testStamp('PHYS1004','LAB','00:16:05','00:17:25','MTWRF','A1','O');
          testStamp('ELEC1004','LAB','00:17:35','00:18:55','MTWRF','A1','O');
          testStamp('ECOR1004','LAB','00:19:05','00:20:25','MTWRF','A1','O');
          testStamp('ECOR1004','LAB','00:20:35','00:21:55','MTWRF','A1','O');
        } else if(testNumber == 3){
          testStamp('MATH1004','LEC','00:08:35','00:09:55','MTWRF','A','E');
          testStamp('PHYS1004','LAB','00:10:05','00:11:25','MTWRF','A1','E');
          testStamp('SYSC1004','LAB','00:11:35','00:12:55','MTWRF','A1','E');
          testStamp('ECOR1004','LAB','00:13:05','00:14:25','MTWRF','A1','E');
          testStamp('ELEC1004','LAB','00:14:35','00:15:55','MTWRF','A1','E');
          testStamp('PHYS1004','LAB','00:16:05','00:17:25','MTWRF','A1','E');
          testStamp('ELEC1004','LAB','00:17:35','00:18:55','MTWRF','A1','E');
          testStamp('ECOR1004','LAB','00:19:05','00:20:25','MTWRF','A1','E');
          testStamp('ECOR1004','LAB','00:20:35','00:21:55','MTWRF','A1','E');
        } else {
          testStamp('MATH1004','LEC','00:08:35','00:09:55','MTWRF','A','B');
          testStamp('PHYS1004','LAB','00:10:05','00:11:25','MTWRF','A1','O');
          testStamp('SYSC1004','LAB','00:11:35','00:12:55','MTWRF','A1','O');
          testStamp('ECOR1004','LAB','00:13:05','00:14:25','MTWRF','A1','O');
          testStamp('ELEC1004','LAB','00:14:35','00:15:55','MTWRF','A1','E');
          testStamp('PHYS1004','LAB','00:16:05','00:17:25','MTWRF','A1','O');
          testStamp('ELEC1004','LAB','00:17:35','00:18:55','MTWRF','A1','O');
          testStamp('ECOR1004','LAB','00:19:05','00:20:25','MTWRF','A1','E');
          testStamp('ECOR1004','LAB','00:20:35','00:21:55','MTWRF','A1','E');
        }
      }
 
 
      function testStamp(courseCode, type, beginTime, endTime, days, courseSection, week){
                      // CourseName,Type, beginTime, endTime, days, courseSection, EOB
        alert(courseCode +" "+courseSection+" "+beginTime+" "+endTime+" "+days);
        var index = 0;
        var day;
        var dayArray = days.split("");
        var courseName = courseCode.substr(0,4);
        var courseNumber = courseCode.substr(4,7);
 
        for(index = 0; index < (days.split("")).length; index++) {
           
 
          if(dayArray[index].toLowerCase() == "m"){
            day = "MON";
          } else if(dayArray[index].toLowerCase() == "t") {
            day = "TUE";
          } else if(dayArray[index].toLowerCase() == "w") {
            day = "WED";
          } else if(dayArray[index].toLowerCase() == "r") {
            day = "THU";
          } else {
            day = "FRI";
          }
          //alert(day);
 
          if(week.toLowerCase() == "b"){
            //alert(week);
            var index2;
            for(index2 = 0; index2 < 2; index2++){
              //alert(index2);
              if(index2 == 0){
                scheduleStamp(courseName, courseNumber, courseSection, beginTime, endTime, 'O', day);
                /*alert("courseName: "+courseName+ " | "+ "courseNumber: "+courseNumber +" | "+ "courseSection: "+courseSection +" | "+
                  "beginTime: "+beginTime+ " | "+ "endTime: "+endTime +" | "+ "weeks: "+week +" | "+ "day: "+day);*/
              } else if(index2 == 1){
                scheduleStamp(courseName, courseNumber, courseSection, beginTime, endTime, 'E', day);
              } else {
                alert("Error");
              }
            }
 
          } else if(week.toLowerCase() == "o") {
            scheduleStamp(courseName, courseNumber, courseSection, beginTime, endTime, 'O', day);
          } else if(week.toLowerCase() == "e") {
            scheduleStamp(courseName, courseNumber, courseSection, beginTime, endTime, 'E', day);
          } else {
            alert("Error");
          }
 
        }
 
    }
 
 
       
 
      /*
       
      function evenOddVisible(){
        var evenWeek = document.getElementById("evenWeek");
        var oddWeek = document.getElementById("oddWeek");
 
        evenWeek.style.visibility="visible";
        oddWeek.style.visibility="visible";
 
        evenWeek.style.height= "100%";
        oddWeek.style.height= "100%";
 
      }
 
      function evenVisible(){
 
        var evenWeek = document.getElementById("evenWeek");
        var oddWeek = document.getElementById("oddWeek");
 
        evenWeek.style.visibility="visible";
        oddWeek.style.visibility="hidden";
 
        evenWeek.style.height= "100%";
        //oddWeek.style.height= 0%;
 
 
      }
 
      function oddVisible(){
 
        var evenWeek = document.getElementById("evenWeek");
        var oddWeek = document.getElementById("oddWeek");
 
        evenWeek.style.visibility="hidden";
        oddWeek.style.visibility="visible";
 
        oddWeek.style.height= "100%";
 
      }
       
      */
      function shed(arrayOne, arrayTwo){
      	for(var i=0;i<arrayOne.length;i++){
					    testStamp(arrayOne[i][1],arrayOne[i][4],arrayOne[i][6],arrayOne[i][7],arrayOne[i][5],arrayOne[i][3],'B');
					if(arrayOne[i].length>9){	
						testStamp(arrayOne[i][1],arrayOne[i][9][3],arrayOne[i][9][5],arrayOne[i][9][6],arrayOne[i][9][4],arrayOne[i][9][2],arrayOne[i][9][8]);
					}	
				}	
      }
