<?php  
/**===========================================================================
*              GUnet e-Class 2.0
*       E-learning and Course Management Program
* ===========================================================================
*       Copyright(c) 2003-2006  Greek Universities Network - GUnet
*       A full copyright notice can be read in "/info/copyright.txt".
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
if(!isset($MCU))
	$MCU="";

/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_CHAT');
/**************************************/


$nameTools = $langConference;


//HEADER
$head_content='



<script type="text/javascript" src="js/prototype-1.4.0.js"></script>
<script type="text/javascript" src="js/media_player.js"></script>
<script>

var video_URL="";
var presantation_URL="";
var conference_set="";

/*Clear in the server side all the chat messages*/
function clear_chat()
	{
	  	new Ajax.Request("refresh_chat.php", {method:"post", postBody:"reset=true"});
		return false;
	}
function save_chat()
	{ 
	var set_presantation = function(t) {
		                alert(t.responseText);
			}
		
	  	new Ajax.Request("refresh_chat.php", {method:"post", postBody:"store=true",onSuccess:set_presantation});
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
	    refresh_student();
	}
/* when teacher page load chat div load*/
function init_teacher()
	{
	    var url = "refresh_chat.php";
	    var target = "chat";
	    var myAjax = new Ajax.Updater(target, url);

        var set_presantation = function(t) {
		if(t.responseText==""){
			document.getElementById("presantation_window").innerHTML="'.$langPresantation_content.'";
		} else{
			var presantation=\'<iframe id="iframe" src="\'+t.responseText+\'"></iframe>\';
                        document.getElementById("presantation_window").innerHTML=presantation;
                }
        }
        var errFunc = function(t) {
                alert("Error " + t.status + " -- " + t.statusText);
        }


        var set_video = function(t) {
		if(t.responseText==""){
			document.getElementById("video").innerHTML="'.$langVideo_content.'";
		} else{
			mediaLink(document.getElementById("video"),t.responseText);
                }
        }
        var errFunc = function(t) {
                alert("Error " + t.status + " -- " + t.statusText);
        }
        var set_conference = function(t) {
		if(t.responseText=="false"){
			document.getElementById("conference").innerHTML="'.$langTeleconference_content.'";
			document.getElementById("conference_control").checked=false;
		} else if(t.responseText=="true"){
			var player="<object ID=\'NetMeeting\' CLASSID=\'CLSID:3E9BAF2D-7A79-11d2-9334-0000F875AE17\'>\
                                            <PARAM NAME =\'MODE\' VALUE =\'RemoteOnly\'>\
                                             </object>";
                        document.getElementById("conference").innerHTML=player;
                        NetMeeting.CallTo("'.$currentCourseID.'@'.$MCU.'");
			document.getElementById("conference_control").checked=true;
                }
        }
        var errFunc = function(t) {
                alert("Error " + t.status + " -- " + t.statusText);
        }





       new Ajax.Request("pass_parameters.php", {method:"post", postBody:"variable=video_URL", onSuccess:set_video, onFailure:errFunc});

        new Ajax.Request("pass_parameters.php", {method:"post", postBody:"variable=presantation_URL", onSuccess:set_presantation, onFailure:errFunc});
        new Ajax.Request("pass_parameters.php", {method:"post", postBody:"variable=netmeeting_show", onSuccess:set_conference, onFailure:errFunc});

	}

/* refresh video div, chat div, page div for student*/
function refresh_student()
	{
	var url = "refresh_chat.php";
	var target = "chat";
	var myAjax = new Ajax.Updater(target, url);

	
        var set_presantation = function(t) {
		if(t.responseText==""){
			presantation_URL="";
			document.getElementById("presantation_window").innerHTML="'.$langPresantation_content.'";
		} else if( t.responseText!=presantation_URL){
			presantation_URL=t.responseText;
			var presantation=\'<iframe id="iframe" src="\'+t.responseText+\'"></iframe>\';
                        document.getElementById("presantation_window").innerHTML=presantation;
                }
        }
        var errFunc = function(t) {
                alert("Error " + t.status + " -- " + t.statusText);
        }


        var set_video = function(t) {
		if(t.responseText==""){
			video_URL="";
			document.getElementById("video").innerHTML="'.$langVideo_content.'";
		} else if(t.responseText!=video_URL){
			video_URL=t.responseText;
			mediaLink(document.getElementById("video"),t.responseText);
                }
        }
        var errFunc = function(t) {
                alert("Error " + t.status + " -- " + t.statusText);
        }

        var set_conference = function(t) {
		if(t.responseText=="false" || t.responseText==""){
			conference_set="false";
			document.getElementById("conference").innerHTML="'.$langTeleconference_content.'";
			document.getElementById("conference_control").checked=false;
		} else if(t.responseText=="true" && t.responseText!=conference_set){
			conference_set="true";
			var player="<object ID=\'NetMeeting\' CLASSID=\'CLSID:3E9BAF2D-7A79-11d2-9334-0000F875AE17\'>\
                                            <PARAM NAME =\'MODE\' VALUE =\'RemoteOnly\'>\
                                             </object>";
                        document.getElementById("conference").innerHTML=player;
                        NetMeeting.CallTo("'.$currentCourseID.'@'.$MCU.'");
			document.getElementById("conference_control").checked=true;
                }
        }
        var errFunc = function(t) {
                alert("Error " + t.status + " -- " + t.statusText);
        }





       new Ajax.Request("pass_parameters.php", {method:"post", postBody:"variable=video_URL", onSuccess:set_video, onFailure:errFunc});

        new Ajax.Request("pass_parameters.php", {method:"post", postBody:"variable=presantation_URL", onSuccess:set_presantation, onFailure:errFunc});
        new Ajax.Request("pass_parameters.php", {method:"post", postBody:"variable=netmeeting_show", onSuccess:set_conference, onFailure:errFunc});
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

		if(document.getElementById("conference_control").checked)
			{
			var player="<object ID=\'NetMeeting\' CLASSID=\'CLSID:3E9BAF2D-7A79-11d2-9334-0000F875AE17\'>\
			<PARAM NAME =\'MODE\' VALUE =\'RemoteOnly\'>\
			</object>";
			document.getElementById("conference").innerHTML=player;
			var netmeeting_number="'.$currentCourseID.'@'.$MCU.'";
			NetMeeting.CallTo(netmeeting_number);
			new Ajax.Request("pass_parameters.php", {method:"post", postBody:"netmeeting_show=true"});
			}
		else
			{
			document.getElementById("conference").innerHTML="'.$langTeleconference_content.'";
			new Ajax.Request("pass_parameters.php", {method:"post", postBody:"netmeeting_show=false"});
			}
	}




/* load media player or netmeeting */
function play_video()
	{	
		mediaLink(document.getElementById("video"),document.getElementById("Video_URL").value);
		new Ajax.Request("pass_parameters.php", {method:"post", postBody:"video_URL="+document.getElementById("Video_URL").value});
return false;


	}






	
/* load presantation in right iframe*/
function show_presantation()
	{
var presantation_url=\'<iframe src="\'+document.getElementById("Presantation_URL").value+\'"></iframe>\';
document.getElementById("presantation_window").innerHTML=presantation_url;
new Ajax.Request("pass_parameters.php", {method:"post", postBody:"presantation_URL="+escape(document.getElementById("Presantation_URL").value)});
return false;
	}

function clean_presantation()
	{	document.getElementById("presantation_window").innerHTML="'.$langPresantation_content.'";
		new Ajax.Request("pass_parameters.php", {method:"post", postBody:"action=clean_presantation"});
		
	}
function clean_video()
	{	document.getElementById("video").innerHTML="'.$langVideo_content.'";
		new Ajax.Request("pass_parameters.php", {method:"post", postBody:"action=clean_video"});
		
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
	<div id="conference">
'.$langTeleconference_content.'	
	</div>
	<div id="video">
'.$langVideo_content.'	
	</div>


';

if ($is_adminOfCourse) {
@$tool_content.='<div  id="video_presantation_control">
<form id="video_form" onSubmit="return play_video();">
';
if($MCU!="") {
$tool_content.='
      <p>'.$langconference.'
      <input type="checkbox" name="conference_control" id="conference_control" onclick="javascript:netmeeting();" /></p>
';
}
$tool_content.='
    <br>
    <p>'.$langsetvideo.'</p><input type="text" id="Video_URL" size="15"><br><input type="submit" value="'.$langButtonVideo.'">
	<a href="javascript:clean_video();">'.$langWashVideo.'</a>
</form>

<form id="Presantation_form" onSubmit = "return show_presantation();">
<p>'.$langpresantation.'</p>
<input type="text" id="Presantation_URL" name="Presantation_URL" size="20"><br>
<input type="submit" value="'.$langButtonPresantation.'">
<a href="javascript:clean_presantation();">'.$langWashPresanation.'</a>
</form>
</div>
';

}

$tool_content.='
	<div id="presantation_window">
	'.$langPresantation_content.'

	</div>

	<div id="chat_div" align="center">
		<div align="left" id="chat">
		</div>

		<form name = "chatForm" action = "conference.php#bottom" method = "get" target = "conference" onSubmit = "return prepare_message();">

		<div align="center"  id="chat_control">
			<input type="text" name="msg" size="80">
			<input type="hidden" name="chatLine">
			<input type="submit" value=" >> ">';

		if ($is_adminOfCourse) {
			$tool_content.=' 
        		<br><a href="conference.php?reset=true" onclick="return clear_chat();">'.$langWash.'</a> |
        		<a href="conference.php?save=true" onclick="return save_chat();">'.$langSaveChat.'</a>';
 		}

$tool_content.='
			</form>
';
		$tool_content.='

		</div>
	</div>

	</div>
	<div id="background">
	</div>
';


//END CONTENT
//draw($tool_content, 2, 'conference', $head_content, $body_action,true);
draw($tool_content, 2, 'conference', $head_content, $body_action);
?>
