DNP_PROJECT
===========

check your droopbox for the .csv files needed to populate the tables. 

notes:
 1. if has_LAB or has_TUT is NULL the entry is either a lab,tut,pa, grp, or any other extention.
 2. A 1 in the has_TUT coloum ca indicate any of the following: tut,pa,grp,film,.... 
 3. if has_TUT and/or has_LAB is 1 there is an associated lab and or tutorial
 4. Stream_Courses is the junction table for all streams all years and every semester link, semester = 0 means fall, semester = 1 means winter.
