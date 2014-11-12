<!DOCTYPE html>
<html>

<head>
	<meta http-equiv="Content-Type'" content="text/html; charset=utf-8"/>
	<meta content="utf-8" http-equiv="encoding">
	<link rel="stylesheet" type="text/css" href="style.css">	
	
	<script type = "text/javascript">
		
		function iframeChange(Track)
			{
				switch(Track){
					case 0:
							document.getElementById('frame').setAttribute('style','display:none');
							document.getElementById('frame2').setAttribute('style','display:inline');
							document.getElementById('showFrame1').setAttribute('style','display:none');
							document.getElementById('showFrame2').setAttribute('style','display:none');
							break;
					case 1:
							document.getElementById('frame').setAttribute('style','display:inline');
							document.getElementById('frame2').setAttribute('style','display:none');
							document.getElementById('showFrame1').setAttribute('style','display:none');
							document.getElementById('showFrame2').setAttribute('style','display:none');
							break;
					case defult:
							break;
				}
    		}
    </script>
	</head>
	<body id = 'fancy'>	
		<?//getting posted info to pass to iframe
		$Name = $_POST["user_ID"];
		$Std_num = intval($_POST["STD_NUM"]);
		$Stream = $_POST["Stream"];
		$year = intval($_POST["year"]);	
		$urliFrame = "on_track.php?user_ID=".$Name."&STD_NUM=".$Std_num."&Stream=".$Stream."&year=".$year;
		$urliFrameOff = "off_track.php?user_ID=".$Name."&STD_NUM=".$Std_num."&Stream=".$Stream."&year=".$year;
		?>	
    		<div id="container">
    			<h1>Course Select</h1>
    		</div>
    		<input type="button" id="showFrame1" value = "Following pre-req Tree" onClick="iframeChange(1)" style='display:inline'/>
    		<input type="button" id="showFrame2" value = "Not Following pre-req Tree" onClick="iframeChange(0)" style = 'display:inline'/>
    		<iframe  src="<?echo $urliFrame?>" id = "frame" name = "Frame_onTrack" style = "display: none" frameborder="0">
    		</iframe>
    		<iframe  src="<?echo $urliFrameOff?>" id = "frame2" name = "Frame_oFFTrack" style = "display:none" frameborder="0">
    		</iframe>



    	</body>
    	</html>
