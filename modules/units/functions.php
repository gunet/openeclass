<?php

//
// Units utility functions	
//

// Process resource actions
function process_actions()
{
        global $tool_content, $id, $mysqlMainDb, $langResourceCourseUnitDeleted, $langResourceUnitModified;

        if (isset($_REQUEST['edit'])) {
                $res_id = intval($_GET['edit']);
                if ($id = check_admin_unit_resource($res_id)) {
                        add_html_editor();
                        return edit_res($res_id);
                }
        } elseif (isset($_REQUEST['edit_res_submit'])) { // edit resource
                $res_id = intval($_REQUEST['resource_id']);	
                if ($id = check_admin_unit_resource($res_id)) {
                        @$restitle = autoquote(trim($_REQUEST['restitle']));
                        $rescomments = autoquote(trim($_REQUEST['rescomments']));
                        $result = db_query("UPDATE unit_resources SET
                                        title = $restitle,
                                        comments = $rescomments
                                        WHERE unit_id = $id AND id = $res_id");
                }
                $tool_content .= "<p class='success'>$langResourceUnitModified</p>";
        } elseif (isset($_REQUEST['del'])) { // delete resource from course unit
                $res_id = intval($_GET['del']);
                if ($id = check_admin_unit_resource($res_id)) {
                        db_query("DELETE FROM unit_resources WHERE id = '$res_id'", $mysqlMainDb);
                        $tool_content .= "<p class='success'>$langResourceCourseUnitDeleted</p>";
                }
        } elseif (isset($_REQUEST['vis'])) { // modify visibility in text resources only 
                $res_id = intval($_REQUEST['vis']);
                if ($id = check_admin_unit_resource($res_id)) {
                        $sql = db_query("SELECT `visibility` FROM unit_resources WHERE id=$res_id");
                        list($vis) = mysql_fetch_row($sql);
                        $newvis = ($vis == 'v')? 'i': 'v';
                        db_query("UPDATE unit_resources SET visibility = '$newvis' WHERE id = $res_id");
                }
        } elseif (isset($_REQUEST['down'])) { // change order down
                $res_id = intval($_REQUEST['down']);
                if ($id = check_admin_unit_resource($res_id)) {
                        move_order('unit_resources', 'id', $res_id, 'order', 'down',
                                   "unit_id=$id");
                }
        } elseif (isset($_REQUEST['up'])) { // change order up
                $res_id = intval($_REQUEST['up']);
                if ($id = check_admin_unit_resource($res_id)) {
                        move_order('unit_resources', 'id', $res_id, 'order', 'up',
                                   "unit_id=$id");
                }
        }
	return '';
}


// Check that a specified resource id belongs to a resource in the
// current course, and that the user is an admin in this course.
// Return the id of the unit or false if user is not an admin 
function check_admin_unit_resource($resource_id)
{
	global $cours_id, $is_adminOfCourse;
	
	if ($is_adminOfCourse) {
		$q = db_query("SELECT course_units.id FROM course_units,unit_resources WHERE
			course_units.course_id = $cours_id AND course_units.id = unit_resources.unit_id
			AND unit_resources.id = $resource_id");
		if (mysql_num_rows($q) > 0) {
			list($unit_id) = mysql_fetch_row($q);
			return $unit_id;
		}
	}
	return false;
}


// Display resources for unit with id=$id
function show_resources($unit_id)
{
	global $tool_content, $max_resource_id;
	$req = db_query("SELECT * FROM unit_resources WHERE unit_id = $unit_id AND `order` >= 0 ORDER BY `order`");
	if (mysql_num_rows($req) > 0) {
		list($max_resource_id) = mysql_fetch_row(db_query("SELECT id FROM unit_resources
                                WHERE unit_id = $unit_id ORDER BY `order` DESC LIMIT 1"));
		$tool_content .= "
        <table class='tbl_alt_bordless' width='99%'>";
		while ($info = mysql_fetch_array($req)) {
			$info['comments'] = standard_text_escape($info['comments']);
			show_resource($info);
		}	
		$tool_content .= "
        </table>\n";
	}
}


function show_resource($info)
{
        global $tool_content, $langUnknownResType, $is_adminOfCourse;
	
        if ($info['visibility'] == 'i' and !$is_adminOfCourse) {
                return;
        }
        switch ($info['type']) {
                case 'doc':
                        $tool_content .= show_doc($info['title'], $info['comments'], $info['id'], $info['res_id']);
                        break;
                case 'text':
                        $tool_content .= show_text($info['comments'], $info['id'], $info['visibility']);
                        break;
                case 'description':
                        $tool_content .= show_description($info['title'], $info['comments'], $info['id'], $info['res_id'], $info['visibility']);
                        break;
		case 'lp':
                        $tool_content .= show_lp($info['title'], $info['comments'], $info['id'], $info['res_id']);
                        break;
		case 'video':
		case 'videolinks':
                        $tool_content .= show_video($info['type'], $info['title'], $info['comments'], $info['id'], $info['res_id'], $info['visibility']);
                        break;
		case 'exercise':
                        $tool_content .= show_exercise($info['title'], $info['comments'], $info['id'], $info['res_id'], $info['visibility']);
                        break;
		case 'work':
                        $tool_content .= show_work($info['title'], $info['comments'], $info['id'], $info['res_id'], $info['visibility']);
                        break;
		case 'topic':
		case 'forum':
                        $tool_content .= show_forum($info['type'], $info['title'], $info['comments'], $info['id'], $info['res_id'], $info['visibility']);
                        break;
		case 'wiki':
                        $tool_content .= show_wiki($info['title'], $info['comments'], $info['id'], $info['res_id'], $info['visibility']);
                        break;
		case 'link':
                        $tool_content .= show_link($info['title'], $info['comments'], $info['id'], $info['res_id'], $info['visibility']);
                        break;
		case 'linkcategory':
                        $tool_content .= show_linkcat($info['title'], $info['comments'], $info['id'], $info['res_id'], $info['visibility']);
                        break;
                default:
                        $tool_content .= $langUnknownResType;
        }
}


// display resource documents
function show_doc($title, $comments, $resource_id, $file_id)
{
        global $mysqlMainDb, $is_adminOfCourse, $currentCourseID, $cours_id,
               $langWasDeleted, $visibility_check, $urlServer, $id;

        $title = htmlspecialchars($title);
        $r = db_query("SELECT * FROM `$mysqlMainDb`.document
	               WHERE course_id = $cours_id AND id =" . intval($file_id) ." $visibility_check");
        if (mysql_num_rows($r) == 0) {
                if (!$is_adminOfCourse) {
                        return '';
                }
                $status = 'del';
                $image = '../../template/classic/img/delete.gif';
                $link = "<span class='invisible'>$title ($langWasDeleted)</span>";
        } else {
                $file = mysql_fetch_array($r, MYSQL_ASSOC);
                $status = $file['visibility'];
                if ($file['format'] == '.dir') {
                        $image = '../../template/classic/img/folder.png';
                        $link = "<a href='{$urlServer}modules/document/document.php?openDir=$file[path]&amp;unit=$id'>";
                } else {
                        $image = '../document/img/' .
                                choose_image('.' . $file['format']);
                        $link = "<a href='" . file_url($file['path'], $file['filename']) . "' target='_blank'>";
                }
        }
	$class_vis = ($status == 'i' or $status == 'del')? ' class="invisible"': ' class="even"';
        if (!empty($comments)) {
                $comment = '<br />' . $comments;
        } else {
                $comment = '';
        }
        return "
        <tr$class_vis>
          <td width='1'>$link<img src='$image' /></a></td>
          <td align='left'>$link$title</a>$comment</td>" .
                actions('doc', $resource_id, $status) .
                '</tr>';
}


// display resource text
function show_text($comments, $resource_id, $visibility)
{
        global $tool_content;

        $class_vis = ($visibility == 'i')? ' class="invisible"': ' class="even"';
	$comments = mathfilter($comments, 12, "../../courses/mathimg/");
        $tool_content .= "
        <tr$class_vis>
          <td colspan='2'>$comments</td>" .
		actions('text', $resource_id, $visibility) .
                "
        </tr>";
}


// display course description resource
function show_description($title, $comments, $id, $res_id, $visibility)
{
        global $tool_content;

	$comments = mathfilter($comments, 12, "../../courses/mathimg/");
        $tool_content .= "
        <tr>
          <td colspan='2'>
            <div class='title'>" . q($title) .  "</div>
            <div class='content'>$comments</div>
          </td>" .  actions('description', $id, $visibility, $res_id) .  "
        </tr>";
}

// display resource learning path
function show_lp($title, $comments, $resource_id, $lp_id)
{
	global $id, $tool_content, $mysqlMainDb, $urlServer, $is_adminOfCourse,
               $langWasDeleted, $currentCourseID;

	$comment_box = $class_vis = $imagelink = $link = '';
        $title = htmlspecialchars($title);
	$r = db_query("SELECT * FROM lp_learnPath WHERE learnPath_id = $lp_id",
                      $currentCourseID);
	if (mysql_num_rows($r) == 0) { // check if lp was deleted
		if (!$is_adminOfCourse) {
			return '';
		} else {
			$status = 'del';
			$imagelink = "<img src='../../template/classic/img/delete.gif' />";
			$link = "<span class='invisible'>$title ($langWasDeleted)</span>";
		}
	} else {
                $lp = mysql_fetch_array($r, MYSQL_ASSOC);
		$status = ($lp['visibility'] == 'SHOW')? 'v': 'i';
		$link = "<a href='${urlServer}modules/learnPath/learningPath.php?path_id=$lp_id&amp;unit=$id'>";
		$imagelink = "<img src='../../template/classic/img/lp_" .
			($status == 'i'? 'off': 'on') . ".gif' />";
	}
        if ($status != 'v' and !$is_adminOfCourse) {
			return '';
        }

        if (!empty($comments)) {
                $comment_box = "<br />$comments";
        } else {
                $comment_box = '';
        }
        $class_vis = ($status == 'i' or $status == 'del')?  ' class="invisible"': ' class="even"';
	return "
        <tr$class_vis>
          <td width='1'>$link$imagelink</a></td>
          <td>$link$title</a>$comment_box</td>" .
		actions('lp', $resource_id, $status) .  '
        </tr>';
}


// display resource video
function show_video($table, $title, $comments, $resource_id, $video_id, $visibility)
{
        global $is_adminOfCourse, $currentCourseID, $tool_content;

        $result = db_query("SELECT * FROM $table WHERE id=$video_id",
                           $currentCourseID);
        if ($result and mysql_num_rows($result) > 0) {
                $row = mysql_fetch_array($result, MYSQL_ASSOC);
                $link = "<a href='" .
                             video_url($table, $row['url'], @$row['path']) .
                             "' target='_blank'>";
                $videolink = $link . htmlspecialchars($title) . '</a>';
                $imagelink = $link .
                             "<img src='../../template/classic/img/videos_" .
                             ($visibility == 'i'? 'off': 'on') . ".gif' /></a>";
        } else {
                if (!$is_adminOfCourse) {
                        return;
                }
                $videolink = $title;
                $imagelink = "<img src='../../template/classic/img/delete.gif' />";
                $visibility = 'del';
        }
        $class_vis = ($visibility == 'v')? ' class="even"': ' class="invisible"';


        if (!empty($comments)) {
                $comment_box = "<br />$comments";
        } else {
                $comment_box = "";
        }
        $tool_content .= "
        <tr$class_vis>
          <td width='1'>$imagelink</td>
          <td>$videolink $comment_box</td>" .  actions('video', $resource_id, $visibility) . "
        </tr>";
}


// display resource work (assignment)
function show_work($title, $comments, $resource_id, $work_id, $visibility)
{
	global $id, $tool_content, $mysqlMainDb, $urlServer, $is_adminOfCourse,
               $langWasDeleted, $currentCourseID;

	$comment_box = $class_vis = $imagelink = $link = '';
        $title = htmlspecialchars($title);
	$r = db_query("SELECT * FROM assignments WHERE id = $work_id",
                      $currentCourseID);
	if (mysql_num_rows($r) == 0) { // check if it was deleted
		if (!$is_adminOfCourse) {
			return '';
		} else {
			$status = 'del';
			$imagelink = "<img src='../../template/classic/img/delete.gif' />";
			$exlink = "<span class='invisible'>$title ($langWasDeleted)</span>";
		}
	} else {
                $work = mysql_fetch_array($r, MYSQL_ASSOC);
		$link = "<a href='${urlServer}modules/work/work.php?id=$work_id&amp;unit=$id'>";
                $exlink = $link . "$title</a>";
		$imagelink = $link .
                        "<img src='../../template/classic/img/assignments_" .
			($visibility == 'i'? 'off': 'on') . ".gif' /></a>";
	}
	$class_vis = ($visibility == 'v')? ' class="even"': ' class="invisible"';

        if (!empty($comments)) {
                $comment_box = "<br />$comments";
	} else {
                $comment_box = '';
        }
	return "
        <tr$class_vis>
          <td width='1'>$imagelink</td>
          <td>$exlink $comment_box</td>" .
		actions('lp', $resource_id, $visibility) .  '
        </tr>';
}


// display resource exercise
function show_exercise($title, $comments, $resource_id, $exercise_id, $visibility)
{
	global $id, $tool_content, $mysqlMainDb, $urlServer, $is_adminOfCourse,
               $langWasDeleted, $currentCourseID;

	$comment_box = $class_vis = $imagelink = $link = '';
        $title = htmlspecialchars($title);
	$r = db_query("SELECT * FROM exercices WHERE id = $exercise_id",
                      $currentCourseID);
	if (mysql_num_rows($r) == 0) { // check if it was deleted
		if (!$is_adminOfCourse) {
			return '';
		} else {
			$status = 'del';
			$imagelink = "<img src='../../template/classic/img/delete.gif' />";
			$exlink = "<span class='invisible'>$title ($langWasDeleted)</span>";
		}
	} else {
                $exercise = mysql_fetch_array($r, MYSQL_ASSOC);
		$link = "<a href='${urlServer}modules/exercice/exercice_submit.php?exerciseId=$exercise_id&amp;unit=$id'>";
                $exlink = $link . "$title</a>";
		$imagelink = $link .
                        "<img src='../../template/classic/img/exercise_" .
			($visibility == 'i'? 'off': 'on') . ".gif' /></a>";
	}
	$class_vis = ($visibility == 'v')? ' class="even"': ' class="invisible"';


        if (!empty($comments)) {
                $comment_box = "<br />$comments";
	} else {
                $comment_box = "";
        }

	return "
        <tr$class_vis>
          <td width='3'>$imagelink</td>
          <td>$exlink $comment_box</td>" .  actions('lp', $resource_id, $visibility) . "
        </tr>";
}


// display resource forum
function show_forum($type, $title, $comments, $resource_id, $ft_id, $visibility)
{
	global $id, $tool_content, $mysqlMainDb, $urlServer, $is_adminOfCourse, $currentCourseID;
	$comment_box = '';
	$class_vis = ($visibility == 'i')? ' class="invisible"': ' class="even"';
        $title = htmlspecialchars($title);
	if ($type == 'forum') {
		$link = "<a href='${urlServer}modules/phpbb/viewforum.php?forum=$ft_id&amp;unit=$id'>";
                $forumlink = $link . "$title</a>";
	} else {
		$r = db_query("SELECT forum_id FROM topics WHERE topic_id = $ft_id", $currentCourseID);
		list($forum_id) = mysql_fetch_array($r);
		$link = "<a href='${urlServer}modules/phpbb/viewtopic.php?topic=$ft_id&amp;forum=$forum_id&amp;unit=$id'>";
                $forumlink = $link . "$title</a>";
	}

	$imagelink = $link . "<img src='../../template/classic/img/forum_" .
			($visibility == 'i'? 'off': 'on') . ".gif' /></a>";


        if (!empty($comments)) {
                $comment_box = "<br />$comments";
	} else {
                $comment_box = '';
        }

	return "
        <tr$class_vis>
          <td width='1'>$imagelink</td>
          <td>$forumlink $comment_box</td>" .
		actions('forum', $resource_id, $visibility) .  '
        </tr>';
}


// display resource wiki
function show_wiki($title, $comments, $resource_id, $wiki_id, $visibility)
{
	global $id, $tool_content, $mysqlMainDb, $urlServer, $is_adminOfCourse,
               $langWasDeleted, $currentCourseID;

	$comment_box = $imagelink = $link = $class_vis = '';
	$class_vis = ($visibility == 'i')? ' class="invisible"': ' class="even"';
        $title = htmlspecialchars($title);
	$r = db_query("SELECT * FROM wiki_properties WHERE id = $wiki_id",
                      $currentCourseID);
	if (mysql_num_rows($r) == 0) { // check if it was deleted
		if (!$is_adminOfCourse) {
			return '';
		} else {
			$status = 'del';
			$imagelink = "<img src='../../template/classic/img/delete.gif' />";
			$exlink = "<span class='invisible'>$title ($langWasDeleted)</span>";
		}
	} else {
                $wiki = mysql_fetch_array($r, MYSQL_ASSOC);
		$link = "<a href='${urlServer}modules/wiki/page.php?wikiId=$wiki_id&amp;action=show&amp;unit=$id'>";
                $wikilink = $link . "$title</a>";
		$imagelink = $link .
                        "<img src='../../template/classic/img/wiki_" .
			($visibility == 'i'? 'off': 'on') . ".gif' /></a>";
	}

        if (!empty($comments)) {
                $comment_box = "<br />$comments";
	} else {
                $comment_box = '';
        }
	return "
        <tr$class_vis>
          <td width='1'>$imagelink</td>
          <td>$wikilink $comment_box</td>" .
		actions('wiki', $resource_id, $visibility) .  '
        </tr>';
}


// display resource link
function show_link($title, $comments, $resource_id, $link_id, $visibility)
{
	global $id, $tool_content, $mysqlMainDb, $urlServer, $is_adminOfCourse,
               $langWasDeleted, $cours_id, $currentCourseID;

	$comment_box = $class_vis = $imagelink = $link = '';
        $title = htmlspecialchars($title);
	$r = db_query("SELECT * FROM `$mysqlMainDb`.link WHERE course_id = $cours_id AND id = $link_id");
	if (mysql_num_rows($r) == 0) { // check if it was deleted
		if (!$is_adminOfCourse) {
			return '';
		} else {
			$status = 'del';
			$imagelink = "<img src='../../template/classic/img/delete.gif' />";
			$exlink = "<span class='invisible'>" . q($title) . " ($langWasDeleted)</span>";
		}
	} else {
                $l = mysql_fetch_array($r, MYSQL_ASSOC);
		$link = "<a href='${urlServer}modules/link/go.php?c=$currentCourseID&amp;id=$link_id&amp;url=$l[url]' target='_blank'>";
                $exlink = $link . "$title</a>";
		$imagelink = $link .
                        "<img src='../../template/classic/img/links_" .
			($visibility == 'i'? 'off': 'on') . ".gif' /></a>";
	}
	$class_vis = ($visibility == 'v')? ' class="even"': ' class="invisible"';


        if (!empty($comments)) {
                $comment_box = '<br />' . standard_text_escape($comments);
	} else {
                $comment_box = '';
        }

	return "
        <tr$class_vis>
          <td>$imagelink</td>
          <td>$exlink $comment_box</td>" .  actions('link', $resource_id, $visibility) . "
        </tr>";
}

// display resource link category
function show_linkcat($title, $comments, $resource_id, $linkcat_id, $visibility)
{
	global $id, $tool_content, $mysqlMainDb, $urlServer, $is_adminOfCourse,
               $langWasDeleted, $cours_id, $currentCourseID;
	
	$content = $linkcontent = '';
	$comment_box = $class_vis = $imagelink = $link = '';
        $title = htmlspecialchars($title);
	$sql = db_query("SELECT * FROM link_category WHERE course_id = $cours_id AND id = $linkcat_id");
	if (mysql_num_rows($sql) == 0) { // check if it was deleted
		if (!$is_adminOfCourse) {
			return '';
		} else {
			$status = 'del';
			$imagelink = "<img src='../../template/classic/img/delete.gif' />";
			$exlink = "<span class='invisible'>" . q($title) . " ($langWasDeleted)</span>";
		}
	} else {
                $class_vis = ($visibility == 'v')? ' class="even"': ' class="invisible"';
		while ($lcat = mysql_fetch_array($sql)) {
			$content .= "
        <tr$class_vis>
          <td width='1'><img src='../../template/classic/img/opendir.gif' /></td>
          <td>" . q($lcat['name']);
			if (!empty($lcat['description'])) {
				$comment_box = "<br />$lcat[description]";
			} else {
                                $comment_box = '';
                        }

			$sql2 = db_query("SELECT * FROM link WHERE course_id = $cours_id AND category = $lcat[id]");
			while ($l = mysql_fetch_array($sql2, MYSQL_ASSOC)) {
				$imagelink = "<img src='../../template/classic/img/links_" .
				($visibility == 'i'? 'off': 'on') . ".gif' />";
				$linkcontent .= "<br />$imagelink&nbsp;&nbsp;<a href='${urlServer}modules/link/go.php?c=$currentCourseID&amp;id=$l[id]&amp;url=$l[url]' target='_blank'>" . q($l['title']) . "</a>";
			}
		}
	}
	return $content . $comment_box . $linkcontent .'
           </td>'. actions('linkcategory', $resource_id, $visibility) .
		'</tr>';
}

// resource actions
function actions($res_type, $resource_id, $status, $res_id = false)
{
        global $is_adminOfCourse, $langEdit, $langDelete, $langVisibility, $langDown, $langUp, $mysqlMainDb;

        static $first = true;

	if (!$is_adminOfCourse) {
		return '';
	}

        if ($res_type == 'description') {
                $icon_vis = ($status == 'v')? 'checkbox_on.gif': 'checkbox_off.gif';
                $edit_link = "edit.php?numBloc=$res_id";
        } else {
                $icon_vis = ($status == 'v')? 'visible.gif': 'invisible.gif';
                $edit_link = "$_SERVER[PHP_SELF]?edit=$resource_id";
        }

        if ($status != 'del') {
                $content = "\n          <td width='3'><a href='$edit_link'>" .  "<img src='../../template/classic/img/edit.png' title='$langEdit' /></a></td>";
        } else {
                $content = "\n          <td width='3'>&nbsp;</td>";
        }
        $content .= "\n          <td width='3'><a href='$_SERVER[PHP_SELF]?del=$resource_id'" .
                    " onClick=\"return confirmation();\">" .
                    "<img src='../../template/classic/img/delete.gif' " .
                    "title='$langDelete'></img></a></td>";
	 
	if ($status != 'del') {
		if (in_array($res_type, array('description', 'text', 'video', 'forum', 'topic'))) { 
			$content .= "\n          <td width='3'><a href='$_SERVER[PHP_SELF]?vis=$resource_id'>" .
                                    "<img src='../../template/classic/img/$icon_vis' " .
                                    "title='$langVisibility'></img></a></td>";
		} else {
			$content .= "\n          <td width='3'>&nbsp;</td>";
		}
        } else {
                $content .= "\n          <td width='3'>&nbsp;</td>";
        }
        if ($resource_id != $GLOBALS['max_resource_id']) {
                $content .= "\n          <td width='12'><div align='right'><a href='$_SERVER[PHP_SELF]?down=$resource_id'>" .
                            "<img src='../../template/classic/img/down.gif' title='$langDown'></img></a></div></td>";
	} else {
		$content .= "\n          <td width='12'>&nbsp;</td>";
	}
        if (!$first) {
                $content .= "<td width='12'><div align='left'><a href='$_SERVER[PHP_SELF]?up=$resource_id'>" .
                            "<img src='../../template/classic/img/up.gif' title='$langUp'></img></a></div></td>";
        } else {
                $content .= "\n          <td width='12'>&nbsp;</td>";
        }
        $first = false;
        return $content;
}


// edit resource
function edit_res($resource_id) 
{
	global $id, $urlServer, $langTitle, $langDescr, $langEditForum, $langContents, $langModify;
	 
        $sql = db_query("SELECT id, title, comments, type FROM unit_resources WHERE id='$resource_id'");
        $ru = mysql_fetch_array($sql);
        $restitle = " value='" . htmlspecialchars($ru['title'], ENT_QUOTES) . "'";
        $rescomments = $ru['comments'];
        $resource_id = $ru['id'];
        $resource_type = $ru['type'];

	$tool_content = "\n  <form method='post' action='${urlServer}modules/units/'>" .
                        "\n  <fieldset>".
                        "\n  <legend>$langEditForum</legend>".
	                "\n    <input type='hidden' name='id' value='$id'>" .
                        "\n    <input type='hidden' name='resource_id' value='$resource_id'>";
	if ($resource_type != 'text') {
		$tool_content .= "\n    <table class='tbl'>" .
                                 "\n    <tr>" .
                                 "\n      <th>$langTitle:</th>" .
                                 "\n      <td><input type='text' name='restitle' size='50' maxlength='255' $restitle></td>" .
                                 "\n    </tr>";
		$message = $langDescr;
	} else {
		$message = $langContents;
	}
        $tool_content .= "\n    <tr>" .
                         "\n      <th>$message:</th>" .
                         "\n      <td>" .  rich_text_editor('rescomments', 4, 20, $rescomments) . "      </td>" .
                         "\n    </tr>" .
                         "\n    <tr>" .
                         "\n      <th>&nbsp;</th>" .
                         "\n      <td><input type='submit' name='edit_res_submit' value='$langModify'></td>" .
                         "\n    </tr> " .
                         "\n    </table>" .
                         "\n  </fieldset>" .
                         "\n  </form>";

	return $tool_content;
}
