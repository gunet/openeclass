<?php  
//Μετατροπή του εργαλείου για να χρησιμοποιεί το baseTheme
$require_current_course = TRUE;
$langFiles = 'conference';
$require_help = TRUE;
$helpTopic = 'User';
include '../../include/baseTheme.php';


//$nameTools = "conference";


//HEADER
$head_content='


<meta http-equiv="refresh" content="400; url=\''.$_SERVER['PHP_SELF'].'\'">

<script type="text/javascript" src="js/prototype-1.4.0.js"></script>
<script>

var video_div="";

function clear_chat()
	{
	  	new Ajax.Request("refresh_chat.php", {method:"post", postBody:"reset=true"});
		return false;
	}
function save_chat()
	{
	  	new Ajax.Request("refresh_chat.php", {method:"get", postBody:"store=true"});
		return false;
	}

/*function for chat submit*/
function prepare_message()
{
	    var pars = "chatLine="+escape(document.chatForm.msg.value);
	    var target = "chat";
	    var url = "refresh_chat.php";
	    var myAjax = new Ajax.Updater(target, url, {method: "get", parameters: pars});
        document.chatForm.msg.value = "";
        document.chatForm.msg.focus();

        return false;
}



/* when student page load chat div load*/
function init_student()
	{
	    var url = "refresh_chat.php";
	    var target = "chat";
	    var myAjax = new Ajax.Updater(target, url);
	}
/* when teacher page load chat div load*/
function init_teacher()
	{
	    var url = "refresh_chat.php";
	    var target = "chat";
	    var myAjax = new Ajax.Updater(target, url);
	}

/* refresh video div, chat div, page div for student*/
function refresh_student()
	{




var set_video = function(t) {
	if(video_div!=t.responseText)
	{	video_div=t.responseText;
		document.getElementById("video").innerHTML=t.responseText;


		var set_netmeeting_number = function(t) {
			NetMeeting.CallTo(t.responseText);

		}


	  	new Ajax.Request("pass_parameters.php", {method:"post", postBody:"variable=netmeeting_number", onSuccess:set_netmeeting_number, onFailure:errFunc});
		


	}

    }
var set_presantation = function(t) {

    if(t.responseText.substring(1,t.responseText.length)!=document.getElementById("presantation_window").src)
    {
    		document.getElementById("presantation_window").src=t.responseText;
    	}
    }
var errFunc = function(t) {
    alert("Error " + t.status + " -- " + t.statusText);
}
	  new Ajax.Request("pass_parameters.php", {method:"post", postBody:"variable=video", onSuccess:set_video, onFailure:errFunc});

    	  new Ajax.Request("pass_parameters.php", {method:"post", postBody:"variable=presantation", onSuccess:set_presantation, onFailure:errFunc});



	    var url = "refresh_chat.php";
	    var target = "chat";
	    var myAjax = new Ajax.Updater(target, url);
	}
/* refresh chat div for teacher*/
function refresh_teacher()
	{  
	    var url = "refresh_chat.php";
	    var target = "chat";
	    var myAjax = new Ajax.Updater(target, url);
	}



function netmeeting()
	{
		document.getElementById("video_control").innerHTML="";
		var player="<object ID=\'NetMeeting\' CLASSID=\'CLSID:3E9BAF2D-7A79-11d2-9334-0000F875AE17\'>\
		<PARAM NAME =\'MODE\' VALUE =\'RemoteOnly\'>\
		</object>";
		document.getElementById("video").innerHTML=player;
		var netmeeting_number="'.$currentCourseID.'@'.$MCU.'";
		NetMeeting.CallTo(netmeeting_number);
		new Ajax.Request("pass_parameters.php", {method:"post", postBody:"video_div="+player+"&netmeeting_number="+netmeeting_number});
	}
function mediaplayer()
	{
		document.getElementById("video_control").innerHTML=\'<input type="text" id="Video_URL" size="20"><input type="submit" value=" Play ">\';


	}



/* load media player or netmeeting */
function play_video()
	{

		
		var video_url=document.getElementById("video_URL").value;
		var player="<OBJECT id=\'VIDEO\' width=\'149\' height=\'149\' \
			CLASSID=\'CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6\'\
			type=\'application/x-oleobject\'>\
			<PARAM NAME=\'URL\' VALUE=\'"+video_url+"\'>\
			<PARAM NAME=\'SendPlayStateChangeEvents\' VALUE=\'True\'>\
			<PARAM NAME=\'AutoStart\' VALUE=\'True\'>\
			<PARAM name=\'uiMode\' value=\'none\'>\
			<PARAM name=\'PlayCount\' value=\'9999\'>\
		</OBJECT>";

		new Ajax.Request("pass_parameters.php", {method:"post", postBody:"video_div="+document.getElementById("video").innerHTML});
		document.getElementById("video").innerHTML=player;


return false;


	}






	
/* load presantation in right iframe*/
function show_presantation()
	{
var presantation_url=document.getElementById("Presantation_URL").value;
document.getElementById("presantation_window").src=presantation_url;
new Ajax.Request("pass_parameters.php", {method:"post", postBody:"presantation_URL="+presantation_url});
return false;
	
	}

var pe;
if (pe) pe.stop();
';

if ($is_adminOfCourse) {
	$head_content.='pe = new PeriodicalExecuter(refresh_teacher, 5);';
}
else{
	$head_content.='pe = new PeriodicalExecuter(refresh_student, 5);';
}


$head_content.='




</script>
';

//END HEADERS

//BODY



if ($is_adminOfCourse) {
$body_action='onload=init_teacher();';
}
else
{
$body_action='onload=init_student();';
}

//END BODY


//CONTENT
$tool_content = "";//initialise $tool_content



$tool_content.=
'<div>
<table >
<tr valign="top"><td width="150">
	<div id="video"  style="height: 150px;width: 150px;border:groove;">
	</div>


';

if ($is_adminOfCourse) {
$tool_content.='
<form id="video_form" onSubmit = "return play_video();">
<BR>'.$Video_URL.'<BR>
<table>
<tr>
<td>
    <label>
      <input type="radio" name="video_type" id="video_type1" value="netmeeting" onclick="javascript:netmeeting();" />
      <br>netmeeting</label>
</td>
<td>
    <label>
      <input type="radio" name="video_type" id="video_type2" value="video" onclick="javascript:mediaplayer();" />
<br>video</label>
</td>
</tr>
</table>
    <br />
    <div id="video_control"> 
</div>
  </label>

</form>
<form id="Presantation_form" onSubmit = "return show_presantation();">
<BR>'.$Presantation_URL.'<BR>
<input type="text" id="Presentation_URL" name="Presantation_URL" size="20">
<input type="submit" value="Go">
</form>
';

}

$tool_content.='
	</TD>
	<TD>


	<div id="presantation" style="height: 500px;width: 700px;border:groove;" >
	<iframe name="presantation_window" id="presantation_window" width="100%" height="100%" src="http://www.auth.gr">
	</iframe>

	</div>
	</TD></TR>
	<TR >
	<TD colspan=2>

	<div align="center" >
		<div align="left" id="chat" style="position: relative;height: 60px;width: 616px; overflow: auto;">
		</div>

		<form name = "chatForm" action = "conference.php#bottom" method = "get" target = "conference" onSubmit = "return prepare_message();">

		<div align="center"  style="position: relative; width:750px">
			<input type="text" name="msg" size="80">
			<input type="hidden" name="chatLine">
			<input type="submit" value=" >> ">
			</form><br>
';
		if ($is_adminOfCourse) {
			$tool_content.=' 
        		<a href="conference.php?reset=true" onclick="return clear_chat();">'.$langWash.'</a> <!--|
			<a href="conference.php?store=true" onclick="return save_chat()">'.$langSave.'</a>-->
			';
 		}
		$tool_content.='

		</div>
	</div>

	</TD></TR>
	</TABLE>
	</div>
';


//END CONTENT





draw($tool_content, 2, 'user', $head_content,$body_action);
?>
