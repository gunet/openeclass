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
	search_incourse.php
	@version $Id$
	@authors list: Agorastos Sakis <th_agorastos@hotmail.com>
==============================================================================
        @Description: Search function that searches data within a course.
        Requires $dbname to point to the course DB

   	This is an example of the MySQL queries used for searching:
   	SELECT * FROM articles WHERE MATCH (title,body,more_fields) AGAINST ('database') OR ('Security') AND ('lala')
==============================================================================*/


$require_current_course = TRUE;

$guest_allowed = true;
include '../../include/baseTheme.php';

$nameTools = $langSearch;
$found = false;
register_posted_variables(array('announcements' => true,
				'agenda' => true,
			  	'course_units' => true,
				'documents' => true,
				'exercises' => true,
				'forums' => true,
				'links' => true,
				'video' => true),
			  'all');

if (isset($_GET['all'])) {
	$all = intval($_GET['all']);
	$announcements = $agenda = $course_units = $documents = $exercises = $forums = $links = $video = 1; 		     
}

if(isset($_POST['search_terms'])) {
	$search_terms = mysql_real_escape_string($_POST['search_terms']);
	$query = " AGAINST ('".$search_terms." ";
	$query .= "' IN BOOLEAN MODE)";
}


if(empty($search_terms)) {
	
	// display form
	$tool_content .= "
	    <form method='post' action='$_SERVER[PHP_SELF]'>
	    <fieldset>
	    <legend>$langSearchCriteria</legend>
	    <table width='99%' class='tbl'>
	    <tr>
	      <th class='left' width='120'>$langOR</th>
	      <td colspan='2'><input name='search_terms' type='text' size='80'/></td>
	    </tr>
	    <tr>
	      <th width='30%' class='left' valign='top' rowspan='4'>$langSearchIn</th>
	      <td width='35%'><input type='checkbox' name='anouncements' checked='checked' />$langAnnouncements</td>
	      <td width='35%'><input type='checkbox' name='agenda' checked='checked' />$langAgenda</td>
	    </tr>
	    <tr>
	      <td><input type='checkbox' name='course_units' checked='checked' />$langCourseUnits</td>
	      <td><input type='checkbox' name='documents' checked='checked' />$langDoc</td>
	    </tr>
	    <tr>
	      <td><input type='checkbox' name='forums' checked='checked' />$langForums</td>
	      <td><input type='checkbox' name='exercises' checked='checked' />$langExercices</td>
	    </tr>
	   <tr>
	      <td><input type='checkbox' name='video' checked='checked' />$langVideo</td>
	      <td><input type='checkbox' name='links' checked='checked' />$langLinks</td>
	   </tr>
	   <tr>
	     <th>&nbsp;</th>
	     <td colspan='2'><input type='submit' name='submit' value='$langDoSearch' /></td>
	   </tr>
	   </table>
	   </fieldset>
	   </form>";
} else {
	$tool_content .= "<div id=\"operations_container\">
	  <ul id='opslist'>
	    <li><a href='$_SERVER[PHP_SELF]'>$langNewSearch</a></li>
	  </ul>
	</div>";
	$tool_content .= "
	    <table width='99%' class='FormData' align='left'>
	    <tbody>
	    <tr>
	      <th width='180' class='left'>&nbsp;</th>
	      <td><b>$langResults</b></td>
	    </tr>
	    </tbody>
	    </table>";
	
	// search in announcements
	if ($announcements) 
	{
		$myquery = "SELECT title, contenu, temps FROM annonces
				WHERE cours_id = $cours_id
				AND visibility = 'v'
				AND MATCH (title, contenu)".$query;
		$result = db_query($myquery, $mysqlMainDb);
		if(mysql_num_rows($result) > 0) {
			$tool_content .= "<table width=\"99%\" class=\"Search\" align=\"left\">
			<tr>
			<th width=\"180\">$langAnnouncements:</th></tr>
			<tr><td>";
			while($res = mysql_fetch_array($result))
			{
				$tool_content .= "<div class='Results'><b>" . q($res['title']) ."</b>&nbsp;&nbsp;";	    
				$tool_content .= "<small>(" . nice_format($res['temps']). ")</small>
					<br />$res[contenu]<br /></div>";
			}
		$tool_content .= "</td></tr></table>\n";
		$found = true;
		}
	}
	// search in agenda
	if ($agenda) {
		$myquery = "SELECT titre, contenu FROM agenda
				WHERE visibility = 'v'
				AND MATCH (titre, contenu)".$query;
		$result = db_query($myquery, $currentCourseID);	
		if(mysql_num_rows($result) > 0) {
			$tool_content .= "<table width='99%' class='Search' align='left'>
			<tr>
			<th width=\"180\" class=\"left\">$langAgenda:</th></tr>
			<tr><td>";
			while($res = mysql_fetch_array($result))
			{
				$tool_content .= "<div class='Results'>".$res['titre'].": ".$res['contenu']."</div>";
			}
			$tool_content .= "</td></tr></table>\n";
			$found = true;
		}
	}
	// search in documents
	if ($documents) {
		$myquery = "SELECT * FROM document
				WHERE course_id = $cours_id
				AND subsystem = 0
				AND visibility = 'v'
				AND MATCH (filename, comment, title, creator, subject, description, author, language)".$query;
		$result = db_query($myquery, $mysqlMainDb);
		if(mysql_num_rows($result) > 0) {
			$tool_content .= "<table width='99%' class='Search' align='left'>
			<tr>
			<th width='180' class='left'>$langDoc:</th></tr>
			<tr><td>";
			while($res = mysql_fetch_array($result))
			{
				if (empty($res['comment']))  { 
					$add_comment = "";
				} else {
					$add_comment = ": ($res[comment])";
				}
				$link_document = "{$urlServer}modules/document/document.php?action2=download&amp;id=$res[path]";
				$tool_content .= "<div class='Results'><b>
					<a href='$link_document'>".$res['filename']."</a></b>$add_comment</div>";
			}		
			$tool_content .= "</td></tr></table>";
			$found = true;
		}
	}

	// search in exercises	
	if ($exercises) {
		$myquery = "SELECT * FROM exercices
				WHERE active = '1'
				AND MATCH (titre, description)".$query;
		$result = db_query($myquery, $currentCourseID);
		if(mysql_num_rows($result) > 0) {
			$tool_content .= "<table width=\"99%\" class='Search' align='left'>
			<tr>
			<th width='180' class='left'>$langExercices:</th></tr>
			<tr><td>";
			while($res = mysql_fetch_array($result))
			{
				if (empty($res['description'])) { 
					$desc_text = "";
				} else { 
					$desc_text = ": ($res[description])";
				}
				$link_exercise =" ${urlServer}/modules/exercice/exercice_submit.php?exerciseId=$res[id]";
				$tool_content .= "<div class='Results'><b>
				<a href='$link_exercise'>".$res['titre']."</a>$desc_text</b></div>";
			}
			$tool_content .= "</td></tr></table>";
			$found = true;
		}
	}

	// search in forums
	if ($forums) {
		$myquery = "SELECT * FROM posts_text WHERE MATCH (post_text)".$query;
		$result = db_query($myquery, $currentCourseID);
		if(mysql_num_rows($result) > 0) {
			$tool_content .= "<table width='99%' class='Search' align='left'>
			<tr>
			<th width=\"180\" class=\"left\">$langForum:</th></tr>
			<tr><td>";
			while($res = mysql_fetch_array($result))
			{
				$tool_content .= "<td><div class='Results'><li>".$res['post_text']."</li></div></td>";
			}
			$myquery = "SELECT * FROM forums WHERE MATCH (forum_name, forum_desc)".$query;
			$result = db_query($myquery);		
			while($res = mysql_fetch_array($result))
			{
				if (empty($res['forum_desc'])) { 
					$desc_text = "";
				} else { 
					$desc_text = ": ($res[forum_desc])";
				}
				$link_posts = "${urlServer}/modules/phpbb/viewforum.php?forum=$res[forum_id]";
				$tool_content .= "<div class='Results'><a href='$link_posts'>".$res['forum_name']."</a> $desc_text</div>";
			}
			$tool_content .= "</td></tr></table>";
			$found = true;
		}
	}

	// search in links
	if ($links) {
		$myquery = "SELECT * FROM link
				WHERE course_id = $cours_id
				AND MATCH (url, title, description)".$query;
		$result = db_query($myquery, $mysqlMainDb);
		if(mysql_num_rows($result) > 0)
		{
			$tool_content .= "<table width='99%' class='Search' align='left'>
			<tr><th width='180' class='left'>$langLinks:</th></tr>
			<tr><td>";
			while($res = mysql_fetch_array($result))
			{
				if (empty($res['description'])) { 
					$desc_text = "";
				} else { 
					$desc_text = "($res[description])";
				}
				$link_url = "{$urlServer}modules/link/link_goto.php?link_id=$res[id]&amp;link_url=$res[url]"; 
				$tool_content .= "<div class='Results'>
					<a href='$link_url' target=_blank>".$res['url']."</a>: ".$res['title']." $desc_text
					</div>";
			}
			$tool_content .= "</td></tr></table>";
			$found = true;
		}
	}

	// search in video and videolinks
	if ($video)
	{
		$myquery = "SELECT * FROM video WHERE MATCH (url, titre, description)".$query;
		$result = db_query($myquery, $currentCourseID);
		if(mysql_num_rows($result) > 0)
		{
			$tool_content .= "<table width='99%' class='Search' align='left'>
			<tr><th width='180' class='left'>$langVideo:</th></tr>
			<tr><td>";
			while($res = mysql_fetch_array($result))
			{
				if (empty($res['description'])) {
					$desc_text = "";
				} else {
					$desc_text = "($res[description])";
				}
				$link_video = "${urlServer}modules/video/video.php?action2=download&amp;id=$res[path]";				
				$tool_content .= "<div class='Results'><a href='$link_video'>".$res['titre']."</a> $desc_text</div>";
			}
			$tool_content .= "</td></tr></table>";
			$found = true;
		}
		$myquery = "SELECT * FROM videolinks WHERE MATCH (url, titre, description)".$query;
		$result = db_query($myquery, $currentCourseID);
		if(mysql_num_rows($result) > 0)
		{
			$tool_content .= "<table width='99%' class='Search' align='left'>
			<tr><th width='180' class='left'>$langLinks:</th></tr>
			<tr><td>";
			while($res = mysql_fetch_array($result))
			{
				if (empty($res['description'])) {
					$desc_text = "";
				} else {
					$desc_text = "($res[description])";
				}
				$link_video = $res['url'];
				$tool_content .= "<div class='Results'>
					<a href='$link_video' target=_blank>".$res['titre']."</a> $desc_text
					</div>";
			}
			$tool_content .= "</td><tr></table>\n";
			$found = true;
		}
	}
	//ean den vrethikan apotelesmata, emfanish analogou mhnymatos
	if ($found == false) {
		$tool_content .= "<br /><p class='alert1'>$langNoResult</p>";
	}
} // end of search

draw($tool_content, 2);