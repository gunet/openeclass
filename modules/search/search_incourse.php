<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */


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
include '../../include/lib/textLib.inc.php';
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

if(isset($_REQUEST['search_terms'])) {
	$search_terms = mysql_real_escape_string($_REQUEST['search_terms']);
	$query = " AGAINST ('".$search_terms."";
	$query .= "' IN BOOLEAN MODE)";
}

if(empty($search_terms)) {
	
	// display form
	$tool_content .= "
	    <form method='post' action='$_SERVER[SCRIPT_NAME]'>
	    <fieldset>
	    <legend>$langSearchCriteria</legend>
	    <table width='100%' class='tbl'>
	    <tr>
	      <th class='left' width='120'>$langOR</th>
	      <td colspan='2'><input name='search_terms' type='text' size='80'/></td>
	    </tr>
	    <tr>
	      <th width='30%' class='left' valign='top' rowspan='4'>$langSearchIn</th>
	      <td width='35%'><input type='checkbox' name='announcements' checked='checked' />$langAnnouncements</td>
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
	     <td colspan='2' class='right'><input type='submit' name='submit' value='$langDoSearch' /></td>
	   </tr>
	   </table>
	   </fieldset>
	   </form>";
} else {
	$tool_content .= "
        <div id=\"operations_container\">
	  <ul id='opslist'>
	    <li><a href='$_SERVER[SCRIPT_NAME]'>$langNewSearch</a></li>
	  </ul>
	</div>";
	$tool_content .= "
        <p class='sub_title1'>$langResults</p>";
	
	// search in announcements
	if ($announcements) 
	{
		$myquery = "SELECT title, contenu, temps FROM annonces
				WHERE cours_id = $cours_id
				AND visibility = 'v'
				AND MATCH (title, contenu)".$query;
		$result = db_query($myquery, $mysqlMainDb);
		if(mysql_num_rows($result) > 0) {
		$tool_content .= "
                  <script type='text/javascript' src='../auth/sorttable.js'></script>
                  <table width=\"99%\" class=\"sortable\" id='t1' align=\"left\">
		  <tr>
		    <th colspan='2'>$langAnnouncements:</th>
                  </tr>";
                        $numLine = 0;
			while($res = mysql_fetch_array($result))
			{
                             if ($numLine%2 == 0) {
                               $class_view = 'class="even"';
                             } else {
                               $class_view = 'class="odd"';
                             }

		  $tool_content .= "
                  <tr $class_view>
                    <td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                    <td><b>" . q($res['title']) ."</b>&nbsp;&nbsp;";	    
				$tool_content .= "<small>("
				.nice_format(claro_format_locale_date($dateFormatLong, strtotime($res['temps']))).
				")</small><br />$res[contenu]
                    </td>
                  </tr>";
                        $numLine++;
			}
		$tool_content .= "
                  </table>\n\n\n";
		$found = true;
		}
	}
	// search in agenda
	if ($agenda) {
		$myquery = "SELECT titre, contenu, day, hour, lasting FROM agenda
				WHERE visibility = 'v'
				AND MATCH (titre, contenu)".$query;
		$result = db_query($myquery, $currentCourseID);	
		if(mysql_num_rows($result) > 0) {
			$tool_content .= "
                  <script type='text/javascript' src='../auth/sorttable.js'></script>
                  <table width='99%' class='sortable' id='t2' align='left'>
		  <tr>
		    <th colspan='2' class=\"left\">$langAgenda:</th>
                  </tr>";
                        $numLine = 0;
			while($res = mysql_fetch_array($result))
			{
                             if ($numLine%2 == 0) {
                               $class_view = 'class="even"';
                             } else {
                               $class_view = 'class="odd"';
                             }
                  $tool_content .= "
                  <tr $class_view>
                    <td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                    <td>";
				$message = $langUnknown;
				if ($res["lasting"] != "") {
					if ($res["lasting"] == 1)
						$message = $langHour;
					else
						$message = $langHours;
				}
				$tool_content .= "<span class=day>".
				ucfirst(claro_format_locale_date($dateFormatLong,strtotime($res["day"]))).
				"</span> ($langHour: ".ucfirst(date("H:i",strtotime($res["hour"]))).")<br />"				
				.$res['titre']." (".$langDuration.": ".$res["lasting"]." $message) ".$res['contenu']."
                    </td>
                  </tr>";
                        $numLine++;
			}
			$tool_content .= "
                  </table>\n\n\n";
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
			$tool_content .= "
                <script type='text/javascript' src='../auth/sorttable.js'></script>
                <table width='99%' class='sortable' id='t3' align='left'>
		<tr>
		  <th colspan='2' class='left'>$langDoc:</th>
                </tr>";
                $numLine = 0;
		while($res = mysql_fetch_array($result))
		{
                   if ($numLine%2 == 0) {
                      $class_view = 'class="even"';
                   } else {
                      $class_view = 'class="odd"';
                   }
                $tool_content .= "
                <tr $class_view>
                  <td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                  <td>";
				if (empty($res['comment']))  { 
					$add_comment = "";
				} else {
					$add_comment = "<br /><span class='smaller'> ($res[comment])</span>";
				}
				$link_document = "{$urlServer}modules/document/document.php?action2=download&amp;id=$res[path]";
				$tool_content .= "<a href='$link_document'>".$res['filename']."</a>$add_comment
                  </td>
                </tr>";
                $numLine++;
		}		
		$tool_content .= "
                </table>\n\n\n";
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
			$tool_content .= "
                <script type='text/javascript' src='../auth/sorttable.js'></script>
                <table width=\"99%\" class='sortable' id='t4' align='left'>
		<tr>
		  <th colspan='2' class='left'>$langExercices:</th>
                </tr>";
                $numLine = 0;
		while($res = mysql_fetch_array($result))
		{
                   if ($numLine%2 == 0) {
                      $class_view = 'class="even"';
                   } else {
                      $class_view = 'class="odd"';
                   }
                $tool_content .= "
                <tr $class_view>
                  <td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                  <td>";

				if (empty($res['description'])) { 
					$desc_text = "";
				} else { 
					$desc_text = "<br /> <span class='smaller'>$res[description]</span>";
				}
				$link_exercise =" ${urlServer}/modules/exercice/exercice_submit.php?exerciseId=$res[id]";
				$tool_content .= "<a href='$link_exercise'>".$res['titre']."</a>$desc_text
                  </td>
                </tr>";
                $numLine++;
		}
		$tool_content .= "
                </table>\n\n\n";
		$found = true;
		}
	}

	// search in forums
	if ($forums) {
		$myquery = "SELECT * FROM forums WHERE MATCH (forum_name, forum_desc)".$query;
		$result = db_query($myquery, $currentCourseID);
		if(mysql_num_rows($result) > 0) {
		  $tool_content .= "
                  <script type='text/javascript' src='../auth/sorttable.js'></script>
                  <table width='99%' class='sortable' id='t5' align='left'>
		  <tr>
		    <th colspan='2' class=\"left\">$langForum ($langCategories):</th>
                  </tr>";
        
                  $numLine = 0;
		  while($res = mysql_fetch_array($result))
		  {
                   if ($numLine%2 == 0) {
                      $class_view = 'class="even"';
                   } else {
                      $class_view = 'class="odd"';
                   }
                  $tool_content .= "
                  <tr $class_view>
                    <td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                    <td>";

				if (empty($res['forum_desc'])) { 
					$desc_text = "";
				} else { 
					$desc_text = "<br /><span class='smaller'>($res[forum_desc])</span>";
				}
				$link_forum = "${urlServer}/modules/phpbb/viewforum.php?forum=$res[forum_id]";
				$tool_content .= "<a href='$link_forum'>".$res['forum_name']."</a> $desc_text
                    </td>
                  </tr>";				
                  $numLine++;
		  }
		  $tool_content .= "
                  </table>\n\n\n";
		  $found = true;
		}
		$myquery = "SELECT forum_id, topic_title FROM topics WHERE MATCH (topic_title)".$query;		
		$result = db_query($myquery, $currentCourseID);
		if(mysql_num_rows($result) > 0) {
			$tool_content .= "
                <script type='text/javascript' src='../auth/sorttable.js'></script>
                <table width='99%' class='sortable' id='t6' align='left'>
		<tr>
		  <th colspan='2' class=\"left\">$langForum ($langSubjects - $langMessages):</th>
                </tr>";
                $numLine = 0;
		while($res = mysql_fetch_array($result))
		{
                   if ($numLine%2 == 0) {
                      $class_view = 'class="even"';
                   } else {
                      $class_view = 'class="odd"';
                   }
                  $tool_content .= "
                  <tr $class_view>
                    <td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                    <td>";
			$link_topic = "${urlServer}/modules/phpbb/viewforum.php?forum=$res[forum_id]";
			$tool_content .= "<strong>$langSubject</strong>: <a href='$link_topic'>".$res['topic_title']."</a>";
			$myquery2 = "SELECT posts.topic_id AS topicid, posts_text.post_text AS posttext
					FROM posts, posts_text
					WHERE posts.forum_id = $res[forum_id]
						AND posts.post_id = posts_text.post_id 
						AND MATCH (posts_text.post_text)".$query;		
			$result2 = db_query($myquery2, $currentCourseID);
			if(mysql_num_rows($result2) > 0) {
			while($res2 = mysql_fetch_array($result2))
			{
			  $link_post = "${urlServer}/modules/phpbb/viewtopic.php?topic=$res2[topicid]&amp;forum=$res[forum_id]";
			  $tool_content .= "<br /><strong>$langMessage</strong> <a href='$link_post'>".$res2['posttext']."</a>";
			}
	          }
                  $tool_content .= "
                    </td>
                  </tr>";
                  $numLine++;
		  }
		  $tool_content .= "
                  </table>";
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
		$tool_content .= "
                <script type='text/javascript' src='../auth/sorttable.js'></script>
                <table width='99%' class='sortable' id='t7' align='left'>
		<tr>
                  <th colspan='2' class='left'>$langLinks:</th>
                </tr>";
                $numLine = 0;
		while($res = mysql_fetch_array($result))
		{
                   if ($numLine%2 == 0) {
                      $class_view = 'class="even"';
                   } else {
                      $class_view = 'class="odd"';
                   }
                  $tool_content .= "
                  <tr $class_view>
                    <td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                    <td>";

				if (empty($res['description'])) { 
					$desc_text = "";
				} else { 
					$desc_text = "<span class='smaller'>$res[description]</span>";
				}
				$link_url = "{$urlServer}modules/link/go.php?c=$currentCourseID&amp;id=$res[id]&amp;link_url=$res[url]"; 
				$tool_content .= "<a href='$link_url' target=_blank> ".$res['title']."</a> $desc_text
                  </td>
                </tr>";
                 $numLine++;
		 }
		$tool_content .= "
                </table>\n\n\n";
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
		$tool_content .= "
                <script type='text/javascript' src='../auth/sorttable.js'></script>
                <table width='99%' class='sortable'  id='t8' align='left'>
		<tr>
                  <th colspan='2' class='left'>$langVideo:</th>
                </tr>";
                $numLine = 0;
		while($res = mysql_fetch_array($result))
		{
                   if ($numLine%2 == 0) {
                      $class_view = 'class="even"';
                   } else {
                      $class_view = 'class="odd"';
                   }
                  $tool_content .= "
                  <tr $class_view>
                    <td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                    <td>";

				if (empty($res['description'])) {
					$desc_text = "";
				} else {
					$desc_text = "<span class='smaller'>($res[description])</span>";
				}
				$link_video = "${urlServer}modules/video/video.php?action=download&amp;id=$res[path]";				
				$tool_content .= "<a href='$link_video'>".$res['titre']."</a> $desc_text
                  </td>
                </tr>";
                $numLine++;
		}
			$tool_content .= "
                </table>\n\n\n";
			$found = true;
		}
		$myquery = "SELECT * FROM videolinks WHERE MATCH (url, titre, description)".$query;
		$result = db_query($myquery, $currentCourseID);
		if(mysql_num_rows($result) > 0)
		{
		  $tool_content .= "
                  <script type='text/javascript' src='../auth/sorttable.js'></script>
                  <table width='99%' class='sortable' id='t9' align='left'>
		  <tr>
                    <th colspan='2' class='left'>$langLinks:</th>
                  </tr>";
                $numLine =0;
		while($res = mysql_fetch_array($result))
		{
                    if ($numLine%2 == 0) {
                       $class_view = 'class="even"';
                    } else {
                       $class_view = 'class="odd"';
                    }
                  $tool_content .= "
                  <tr $class_view>
                    <td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                    <td>";

		    if (empty($res['description'])) {
		 	$desc_text = "";
		    } else {
			$desc_text = "<span class='smaller'>($res[description])</span>";
		    }
		    $link_video = $res['url'];
		  $tool_content .= "<a href='$link_video' target=_blank>".$res['titre']."</a><br /> $desc_text
                  </td>
                </tr>";
                  $numLine++;
		}
		$tool_content .= "
                  </table>\n\n\n";
			$found = true;
		}
	}
	// search in cours_units and unit_resources
	if ($course_units)
	{
		$myquery = "SELECT id, title, comments FROM course_units
				WHERE course_id = $cours_id
				AND visibility = 'v' 
				AND MATCH (title, comments)".$query;
		$result = db_query($myquery, $mysqlMainDb);
		if(mysql_num_rows($result) > 0)
		{
			$tool_content .= "
			<script type='text/javascript' src='../auth/sorttable.js'></script>
			<table width='99%' class='sortable' id='t11' align='left'>
			<tr>
			  <th colspan='2' class='left'>$langCourseUnits:</th>
			</tr>";
			$numLine = 0;
			while($res = mysql_fetch_array($result))
			{
			      if ($numLine%2 == 0) {
				 $class_view = 'class="even"';
			      } else {
				 $class_view = 'class="odd"';
			      }
			    $tool_content .= "
			    <tr $class_view>
			      <td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
			      <td>";
	  
			      if (empty($res['comments'])) {
				  $comments_text = "";
			      } else {
				  $comments_text = " $res[comments]";
			      }
				  $link = "${urlServer}modules/units/?id=$res[id]";
				  $tool_content .= "<a href='$link'>".$res['title']."</a> $comments_text</td></tr>";
			      $numLine++;
			}
			$tool_content .= "</table>\n\n\n";
			$found = true;
		}
		$myquery2 = $myquery2 = "SELECT unit_resources.unit_id AS id,
				unit_resources.title AS title,
				unit_resources.comments AS comments
			FROM unit_resources, course_units
				WHERE unit_resources.unit_id = course_units.id
				AND course_units.course_id = $cours_id
				AND course_units.visibility = 'v'
			AND MATCH(unit_resources.title, unit_resources.comments)".$query;
		$result2 = db_query($myquery2, $mysqlMainDb);
		if (mysql_num_rows($result2) > 0) {
						$tool_content .= "
			<script type='text/javascript' src='../auth/sorttable.js'></script>
			<table width='99%' class='sortable' id='t11' align='left'>
			<tr>
			  <th colspan='2' class='left'>$langCourseUnits:</th>
			</tr>";
			$numLine = 0;
			while ($res2 = mysql_fetch_array($result2)) {
				if ($numLine%2 == 0) {
				 $class_view = 'class="even"';
			      } else {
				 $class_view = 'class="odd"';
			      }
			      $tool_content .= "
				<tr $class_view>
			      <td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
			      <td>";
				if (empty($res2['comments'])) {
					$comments_text = "";
				} else {
					$comments_text = "<span class='smaller'> $res2[comments]</span>";
				}
				$unitlink = "${urlServer}modules/units/?id=$res2[id]";				
				$tool_content .= "$res2[title]<a href='$unitlink'>".$comments_text."</a></td></tr>";
				$numLine++;
			}
			$tool_content .= "</table>\n\n\n";
			$found = true;
		}
	}
	// else ... no results found
	if ($found == false) {
		$tool_content .= "<p class='alert1'>$langNoResult</p>";
	}
} // end of search

draw($tool_content, 2);
