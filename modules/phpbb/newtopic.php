<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/
/*===========================================================================
        phpbb/newtopic.php
        @last update: 2006-07-23 by Artemios G. Voyiatzis
        @authors list: Artemios G. Voyiatzis <bogart@upnet.gr>

        based on Claroline version 1.7 licensed under GPL
              copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

        Claroline authors: Piraux Sebastien <pir@cerdecam.be>
                      Lederer Guillaume <led@cerdecam.be>

	based on phpBB version 1.4.1 licensed under GPL
		copyright (c) 2001, The phpBB Group
==============================================================================
    @Description: This module implements a per course forum for supporting
	discussions between teachers and students or group of students.
	It is a heavily modified adaptation of phpBB for (initially) Claroline
	and (later) eclass. In the future, a new forum should be developed.
	Currently we use only a fraction of phpBB tables and functionality
	(viewforum, viewtopic, post_reply, newtopic); the time cost is
	enormous for both core phpBB code upgrades and migration from an
	existing (phpBB-based) to a new eclass forum :-(

    @Comments:

    @todo:
==============================================================================
*/

/*
 * Open eClass 2.x standard stuff
 */
$require_current_course = TRUE;
$require_login = TRUE;
$require_help = FALSE;
include '../../include/baseTheme.php';
include '../../include/sendMail.inc.php';
$tool_content = $head_content = "";
$lang_editor = langname_to_code($language);

$head_content = <<<hContent
<script type="text/javascript" src="$urlAppend/include/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
tinyMCE.init({
	// General options
		language : "$lang_editor",
		mode : "textareas",
		theme : "advanced",
		plugins : "pagebreak,style,save,advimage,advlink,inlinepopups,media,print,contextmenu,paste,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,emotions,preview",

		// Theme options
		theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontsizeselect,forecolor,backcolor,removeformat,hr",
		theme_advanced_buttons2 : "pasteword,|,bullist,numlist,|indent,blockquote,|,sub,sup,|,undo,redo,|,link,unlink,|,charmap,media,emotions,image,|,preview,cleanup,code",
		theme_advanced_buttons3 : "",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,

		// Example content CSS (should be your site CSS)
		content_css : "$urlAppend/template/classic/img/tool.css",

		// Drop lists for link/image/media/template dialogs
		template_external_list_url : "lists/template_list.js",
		external_link_list_url : "lists/link_list.js",
		external_image_list_url : "lists/image_list.js",
		media_external_list_url : "lists/media_list.js",

		// Style formats
		style_formats : [
			{title : 'Bold text', inline : 'b'},
			{title : 'Red text', inline : 'span', styles : {color : '#ff0000'}},
			{title : 'Red header', block : 'h1', styles : {color : '#ff0000'}},
			{title : 'Example 1', inline : 'span', classes : 'example1'},
			{title : 'Example 2', inline : 'span', classes : 'example2'},
			{title : 'Table styles'},
			{title : 'Table row 1', selector : 'tr', classes : 'tablerow1'}
		],

		// Replace values for the template plugin
		template_replace_values : {
			username : "Open eClass",
			staffid : "991234"
		}
});
</script>
hContent;

include_once("./config.php");
include("functions.php"); // application logic for phpBB

/******************************************************************************
 * Actual code starts here
 *****************************************************************************/
if (isset($_GET['forum'])) {
	$forum = intval($_GET['forum']);
}
if (isset($_GET['topic'])) {
	$topic = intval($_GET['topic']);
} else $topic = '';

$sql = "SELECT forum_name, forum_access, forum_type FROM forums
	WHERE (forum_id = '$forum')";
if (!$result = db_query($sql, $currentCourseID)) {
	$tool_content .= $langErrorDataForum;
	draw($tool_content, 2, '', $head_content);
	exit;
}
$myrow = mysql_fetch_array($result);
$forum_name = $myrow["forum_name"];
$forum_access = $myrow["forum_access"];
$forum_type = $myrow["forum_type"];
$forum_id = $forum;

$nameTools = $langNewTopic;
$navigation[]= array ("url"=>"index.php", "name"=> $langForums);
$navigation[]= array ("url"=>"viewforum.php?forum=$forum_id", "name"=> $forum_name);

if (!does_exists($forum, $currentCourseID, "forum")) {
	$tool_content .= $langErrorPost;
}

if (isset($_POST['submit'])) {
	$subject = strip_tags($_POST['subject']);
	$message = $_POST['message'];
	if (trim($message) == '' || trim($subject) == '') {
		$tool_content .= "
                <p class='alert1'>$langEmptyMsg</p>
                <p class='back'>&laquo; $langClick <a href='newtopic.php?forum=$forum_id'>$langHere</a> $langReturnTopic</p>";
		draw($tool_content, 2, '', $head_content);
		exit;
	}
	if($forum_access == 3 && $user_level < 2) {
		$tool_content .= $langNoPost;
		draw($tool_content, 2, '', $head_content);
		exit;
	}
	// Check that, if this is a private forum, the current user can post here.
	if ($forum_type == 1) {
		if (!check_priv_forum_auth($uid, $forum, TRUE, $currentCourseID)) {
			$tool_content .= "$langPrivateForum $langNoPost";
			draw($tool_content, 2, '', $head_content);
			exit();
		}
	}
	$is_html_disabled = false;
	if ((isset($allow_html) && $allow_html == 0) || isset($html)) {
		$message = htmlspecialchars($message);
		$is_html_disabled = true;
	}
	if ((isset($allow_bbcode) && $allow_bbcode == 1) && !($_POST['bbcode'])) {
		$message = bbencode($message, $is_html_disabled);
	}
	$message = format_message($message);
	$poster_ip = $_SERVER['REMOTE_ADDR'];
	$time = date("Y-m-d H:i");
	$nom = addslashes($_SESSION['nom']);
	$prenom = addslashes($_SESSION['prenom']);

	if (isset($sig) && $sig) {
		$message .= "\n[addsig]";
	}
	$sql = "INSERT INTO topics (topic_title, topic_poster, forum_id, topic_time, topic_notify, nom, prenom)
			VALUES (" . autoquote($subject) . ", '$uid', '$forum', '$time', 1, '$nom', '$prenom')";
	$result = db_query($sql, $currentCourseID);

	$topic_id = mysql_insert_id();
	$sql = "INSERT INTO posts (topic_id, forum_id, poster_id, post_time, poster_ip, nom, prenom)
			VALUES ('$topic_id', '$forum', '$uid', '$time', '$poster_ip', '$nom', '$prenom')";
	if (!$result = db_query($sql, $currentCourseID)) {
		$tool_content .= $langErrorEnterPost;
		draw($tool_content, 2, '', $head_content);
		exit();
	} else {
		$post_id = mysql_insert_id();
		if ($post_id) {
			$sql = "INSERT INTO posts_text (post_id, post_text)
					VALUES ($post_id, " . autoquote($message) . ")";
			$result = db_query($sql, $currentCourseID);
			$sql = "UPDATE topics
				SET topic_last_post_id = $post_id
				WHERE topic_id = '$topic_id'";
			$result = db_query($sql, $currentCourseID);
		}
	}
	$sql = "UPDATE forums
		SET forum_posts = forum_posts+1, forum_topics = forum_topics+1, forum_last_post_id = $post_id
		WHERE forum_id = '$forum'";
	$result = db_query($sql, $currentCourseID);
	
	$topic = $topic_id;
	$total_forum = get_total_topics($forum, $currentCourseID);
	$total_topic = get_total_posts($topic, $currentCourseID, "topic")-1;  
	// Subtract 1 because we want the nr of replies, not the nr of posts.
	$forward = 1;

	// --------------------------------
	// notify users 
	// --------------------------------
	$subject_notify = "$logo - $langNewForumNotify";
	$category_id = forum_category($forum);
	$cat_name = category_name($category_id);
	$sql = db_query("SELECT DISTINCT user_id FROM forum_notify 
			WHERE (forum_id = $forum OR cat_id = $category_id) 
			AND notify_sent = 1 AND course_id = $cours_id", $mysqlMainDb);
	$c = course_code_to_title($currentCourseID);
	$body_topic_notify = "$langCourse: '$c'\n\n$langBodyForumNotify $langInForums '$forum_name' $langInCat '$cat_name' \n\n$gunet";
	while ($r = mysql_fetch_array($sql)) {
		$emailaddr = uid_to_email($r['user_id']);
		send_mail('', '', '', $emailaddr, $subject_notify, $body_topic_notify, $charset);
	}
	// end of notification
	
	$tool_content .= "
	<p class='success'>$langStored</p>
	<p class='back'>&laquo; $langClick <a href='viewtopic.php?topic=$topic_id&amp;forum=$forum&amp;$total_topic'>$langHere</a>$langViewMsg</p>
	<p class='back'>&laquo; $langClick <a href='viewforum.php?forum=$forum_id'>$langHere</a> $langReturnTopic</p>
	"; 
} elseif (isset($_POST['cancel'])) {
	header("Location: viewtopic.php?topic=$topic&forum=$forum");	
} else {
	$tool_content .= "
        <form action='$_SERVER[PHP_SELF]?topic=$topic&forum=$forum' method='post'>
        <fieldset>
          <legend>$langTopicData</legend>
	  <table class='tbl'>
	  <tr>
	    <th>$langSubject:</th>
	    <td><input type='text' name='subject' size='53' maxlength='100' /></td>
	  </tr>
	  <tr>
            <th valign='top'>$langBodyMessage:</th>
            <td>".  rich_text_editor('message', 14, 50, '', "") ."            </td>
          </tr>
	  <tr>
            <th>&nbsp;</th>
	    <td>
	       <input class='Login' type='submit' name='submit' value='$langSubmit' />&nbsp;
	       <input class='Login' type='submit' name='cancel' value='$langCancelPost' />
	    </td>
          </tr>
	  </table>
	</fieldset>
	</form>";
}
draw($tool_content, 2, '', $head_content);
?>
