<?php  
/**===========================================================================
*              GUnet e-Class 2.0
*       E-learning and Course Management Program
* ===========================================================================
*       Copyright(c) 2003-2006  Greek Universities Network - GUnet
*       Á full copyright notice can be read in "/info/copyright.txt".
*
*  Authors:     Dimitris Tsachalis <ditsa@ccf.auth.gr>
*
*       For a full list of contributors, see "credits.txt".
*
*       This program is a free software under the terms of the GNU
*       (General Public License) as published by the Free Software
*       Foundation. See the GNU License for more details.
*       The full license can be read in "license.txt".
*
*       Contact address:        GUnet Asynchronous Teleteaching Group,
*                                               Network Operations Center, University of Athens,
*                                               Panepistimiopolis Ilissia, 15784, Athens, Greece
*                                               eMail: eclassadmin@gunet.gr
============================================================================*/
/**
 * conference
 * 
 * @author Dimitris Tsachalis <ditsa@ccf.auth.gr>
 * @version $Id$
 * 
 * @abstract 
 *
 */


$require_current_course = TRUE;
$langFiles = 'conference';
$require_help = TRUE;
$helpTopic = 'Conference';
include '../../include/baseTheme.php';


//$nameTools = "conference";


//HEADER
$head_content='


<meta http-equiv="refresh" content="400; url=\''.$_SERVER['PHP_SELF'].'\'">

<script type="text/javascript" src="js/prototype-1.4.0.js"></script>
<script type="text/javascript" src="js/media_player.js"></script>
<script>

var video_div="";
var video_type="";

/*Clear in the server side all the chat messages*/
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
	var set_video_type = function(t) {
		if(t.responseText=="\nvideo" && video_type!=t.responseText){
			var set_video = function(t1) {
				document.getElementById("video").innerHTML=t1.responseText;
				}
			
	  		new Ajax.Request("pass_parameters.php", {method:"post", postBody:"variable=video", onSuccess:set_video, onFailure:errFunc});
		}
		else if(t.responseText=="\nnetmeeting" && video_type!=t.responseText){
				var player="<object ID=\'NetMeeting\' CLASSID=\'CLSID:3E9BAF2D-7A79-11d2-9334-0000F875AE17\'>\
				            <PARAM NAME =\'MODE\' VALUE =\'RemoteOnly\'>\
					     </object>";
				document.getElementById("video").innerHTML=player;
				NetMeeting.CallTo("'.$currentCourseID.'@'.$MCU.'");

		} else if(t.responseText=="\nnone"){
			document.getElementById("video").innerHTML="'.$langVideoTeleconference_content.'";
		}
		
		video_type=t.responseText;
	}
	var set_presantation = function(t) {
    		if(unescape(t.responseText)!="\n"+document.getElementById("presantation_window").innerHTML){
		//	alert(">"+document.getElementById("presantation_window").innerHTML+"<"+"\n"+">"+unescape(t.responseText)+"<");
			document.getElementById("presantation_window").innerHTML=t.responseText;
    		}
	}
	var errFunc = function(t) {
    		alert("Error " + t.status + " -- " + t.statusText);
	}
	new Ajax.Request("pass_parameters.php", {method:"post", postBody:"variable=video_type", onSuccess:set_video_type, onFailure:errFunc});

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


/* teacher set netmeeting*/
function netmeeting()
	{
		document.getElementById("video_control").innerHTML="";
		var player="<object ID=\'NetMeeting\' CLASSID=\'CLSID:3E9BAF2D-7A79-11d2-9334-0000F875AE17\'>\
		<PARAM NAME =\'MODE\' VALUE =\'RemoteOnly\'>\
		</object>";
		document.getElementById("video").innerHTML=player;
		var netmeeting_number="'.$currentCourseID.'@'.$MCU.'";
		NetMeeting.CallTo(netmeeting_number);
		new Ajax.Request("pass_parameters.php", {method:"post", postBody:"video_div="+document.getElementById("video").innerHTML+"&netmeeting_number="+netmeeting_number+"&video_type=netmeeting"});
	}




/* teacher set video*/
function mediaplayer()
	{
		document.getElementById("video_control").innerHTML=\''.$langsetvideo.'<br><input type="text" id="Video_URL" size="20"><input type="submit" value=" Play ">\';


	}



/* load media player or netmeeting */
function play_video()
	{

	/*	
		var video_url=document.getElementById("Video_URL").value;
		var player="<OBJECT id=\'VIDEO\' width=\'199\' height=\'199\' \
			CLASSID=\'CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6\'\
			type=\'application/x-oleobject\'>\
			<PARAM NAME=\'URL\' VALUE=\'"+video_url+"\'>\
			<PARAM NAME=\'SendPlayStateChangeEvents\' VALUE=\'True\'>\
			<PARAM NAME=\'AutoStart\' VALUE=\'True\'>\
			<PARAM name=\'uiMode\' value=\'none\'>\
			<PARAM name=\'PlayCount\' value=\'9999\'>\
		</OBJECT>";

		document.getElementById("video").innerHTML=player;
		*/
		mediaLink(document.getElementById("video"),document.getElementById("Video_URL").value);
		new Ajax.Request("pass_parameters.php", {method:"post", postBody:"video_div="+document.getElementById("video").innerHTML+"&video_type=video"});
return false;


	}






	
/* load presantation in right iframe*/
function show_presantation()
	{
var presantation_url=\'<iframe style="height:500px; width:700px;" src="\'+document.getElementById("Presantation_URL").value+\'"></iframe>\';
document.getElementById("presantation_window").innerHTML=presantation_url;
new Ajax.Request("pass_parameters.php", {method:"post", postBody:"presantation_URL="+escape(document.getElementById("presantation_window").innerHTML)});
return false;
	
	}
function clean_vars()
	{
		new Ajax.Request("pass_parameters.php", {method:"post", postBody:"action=clean"});
		
	}

var pe;
if (pe) pe.stop();
';
$refreshtime="5";

if ($is_adminOfCourse) {
	$head_content.='pe = new PeriodicalExecuter(refresh_teacher, '.$refreshtime.');';
}
else{
	$head_content.='pe = new PeriodicalExecuter(refresh_student, '.$refreshtime.');';
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
'
	<div id="video" style="position:absolute;height: 200px;width: 200px;border:groove;top:210px;left:200px;">
'.$langVideoTeleconference_content.'	
	</div>


';

if ($is_adminOfCourse) {
@$tool_content.='<div  style="position:absolute;height:291px;width: 200px;border:groove;top:420px;left:200px;">
<form id="video_form" onSubmit="return play_video();">
<BR>'.$Video_URL.'<BR>
<table>
<tr>';
if(isset($MCU)) {
$tool_content.='
<td>
    <label>
      <input type="radio" name="video_type" id="video_type1" value="netmeeting" onclick="javascript:netmeeting();" />
      <br>'.$langconference.'</label>
</td>';
}
$tool_content.='
<td>
    <label>
      <input type="radio" name="video_type" id="video_type2" value="video" onclick="javascript:mediaplayer();" />
<br>video</label>
</td>
</tr>
</table>
    <br>
    <div id="video_control"> 
</div>
</form>

<form id="Presantation_form" onSubmit = "return show_presantation();">
<BR>'.$langpresantation.'<BR>
<input type="text" id="Presentation_URL" name="Presantation_URL" size="20">
<input type="submit" value="OK">
</form>
<a href="javascript:clean_vars();">'.$langWashValues.'</a>
</div>
';

}

$tool_content.='
	<div id="presantation_window"  style="position:absolute;height: 500px;width: 700px;border:groove;top:210px;left:410px;" >
	'.$langPresantation_content.'

	</div>

	<div align="center" style="position:absolute;border:groove;top:720px;left:200px;width:910px;" >
		<div align="left" id="chat" style="position: relative;height: 60px;width: 600px; overflow: auto;">
		</div>

		<form name = "chatForm" action = "conference.php#bottom" method = "get" target = "conference" onSubmit = "return prepare_message();">

		<div align="center"  style="position: relative; width:750px">
			<input type="text" name="msg" size="80">
			<input type="hidden" name="chatLine">
			<input type="submit" value=" >> ">';

		if ($is_adminOfCourse) {
			$tool_content.=' 
        		<a href="conference.php?reset=true" onclick="return clear_chat();">'.$langWash.'</a>';
 		}

$tool_content.='
			</form>
';
		$tool_content.='

		</div>
	</div>

	</div>
	<div style="height:600px;width:910px;">
	</div>
';


//END CONTENT
draw($tool_content, 2, 'user', $head_content,$body_action);
?>
