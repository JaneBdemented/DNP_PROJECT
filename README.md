DNP_PROJECT
===========

check your droopbox for the .csv files needed to populate the tables. 
stream tabel
stream_id   stream
1            "Comms"
2            "BIO_ELEC"
3            "SE"
4            "CSE"

notes:
 1. if has_LAB or has_TUT is NULL the entry is either a lab,tut,pa, grp, or any other extention.
 2. A 1 in the has_TUT coloum ca indicate any of the following: tut,pa,grp,film,.... 
 3. if has_TUT and/or has_LAB is 1 there is an associated lab and or tutorial
 4. Stream_Courses is the junction table for all streams all years and every semester link, semester = 0 means fall, semester = 1 means winter.
 5. check "DAY" to determin if both lectures are at the same time on two diffrent days by the number of chars in the coloum. if day >= 3char this entry holdes all lectures elst if day<3char you need to get the second lecture from the entry tabel.
 6. 
 
To DO:
-HAS_PREQ
-HAS_YrStnd
-ELECTIVES
-PREQ table
-ENG ELECTIVES

7-If you are on pattern you pay attention to the years collumn, but if off pattern you ignore it 
8- Table course_stream under 
type O represents core 
     1 represents complementary study elective, 
     2 represents Basic science , 
     3 represents communications elective, 
     4 would represent sysc/elec course in the 3000/4000 level, 
     5 would represent specific computer sysstem elective, 
     6 represents Biomed-elec group 1 
     7 represents Biomed=elec group 2  
     8 represents Software sepecific 1 
     9 represnts software specififc 2
9- Need to look at 1 credit complementary courses 
10- If you are searching complementary study electives, use 5 as your stream ID. 

