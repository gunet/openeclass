<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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


/* @version $Id: forum_admin.php,v 1.10 2011-06-24 13:40:34 adia Exp $
 */

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'For';
$require_editor = true;

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'functions.php';
require_once 'modules/search/indexer.class.php';
require_once 'modules/search/forumindexer.class.php';
require_once 'modules/search/forumtopicindexer.class.php';
require_once 'modules/search/forumpostindexer.class.php';

$idx = new Indexer();
$fidx = new ForumIndexer($idx);
$ftdx = new ForumTopicIndexer($idx);
$fpdx = new ForumPostIndexer($idx);

$nameTools = $langCatForumAdmin;
$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langForums);

$forum_id = isset($_REQUEST['forum_id']) ? intval($_REQUEST['forum_id']) : '';
$cat_id = isset($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : '';

$head_content .= <<<hContent
<script type="text/javascript">
function checkrequired(which, entry) {
	var pass=true;
	if (document.images) {
		for (i=0;i<which.length;i++) {
			var tempobj=which.elements[i];
			if (tempobj.name == entry) {
				if (tempobj.type=="text"&&tempobj.value=='') {
					pass=false;
					break;
		  		}
	  		}
		}
	}
	if (!pass) {
		alert("$langEmptyCat");
		return false;
	} else {
		return true;
	}
}
</script>
hContent;
// forum go
if (isset($_GET['forumgo'])) {
    $ctg = category_name($cat_id);
    $tool_content .= "
        <form action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;forumgoadd=yes&amp;cat_id=$cat_id' method='post' onsubmit=\"return checkrequired(this,'forum_name');\">
        <fieldset>
                <legend>$langAddForCat</legend>
                <table class='tbl' width='100%'>
                <tr>
                <th>$langCat:</th>
                <td>$ctg</td>
                </tr>
                <tr>
                <th>$langForName:</th>
                <td><input type='text' name='forum_name' size='40'></td>
                </tr>
                <tr>
                <th valign='top'>$langDescription:</th>
                <td><textarea name='forum_desc' cols='37' rows='3'></textarea></td>
                </tr>
                <tr>
                <th>&nbsp;</th>
                <td class='right'><input type='submit' value='$langAdd'></td>
                </tr>
                </table>
        </fieldset>
        </form>
        <p>&laquo; <a href='index.php?course=$course_code'>$langBack</a></p>";
}
// forum go edit
elseif (isset($_GET['forumgoedit'])) {
    $result = db_query("SELECT id, name, `desc`, cat_id
                                        FROM forum
                                        WHERE id = $forum_id
                                        AND course_id = $course_id");
    list($forum_id, $forum_name, $forum_desc, $cat_id_1) = mysql_fetch_row($result);
    $tool_content .= "
                <form action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;forumgosave=yes&amp;cat_id=$cat_id' method='post' onsubmit=\"return checkrequired(this,'forum_name');\">
                <input type='hidden' name='forum_id' value='$forum_id'>
                <fieldset>
                <legend>$langChangeForum</legend>
                <table class='tbl' width='100%'>
                <tr>
                <th>$langForName</th>
                <td><input type='text' name='forum_name' size='50' value='" . q($forum_name) . "'></td>
                </tr>
                <tr>
                <th valign='top'>$langDescription</th>
                <td><textarea name='forum_desc' cols='47' rows='3'>" . q($forum_desc) . "</textarea></td>
                </tr>
                <tr>
                <th>$langChangeCat</th>
                <td>
                <select name='cat_id'>";
    $result = db_query("SELECT id, cat_title FROM forum_category WHERE course_id = $course_id");
    while (list($cat_id, $cat_title) = mysql_fetch_row($result)) {
        if ($cat_id == $cat_id_1) {
            $tool_content .= "<option value='$cat_id' selected>$cat_title</option>";
        } else {
            $tool_content .= "<option value='$cat_id'>$cat_title</option>";
        }
    }
    $tool_content .= "</select></td></tr>
        <tr><th>&nbsp;</th>
        <td class='right'><input type='submit' value='$langModify'></td>
        </tr></table>
        </fieldset>
        </form>";
}

// edit forum category
elseif (isset($_GET['forumcatedit'])) {
    $result = db_query("SELECT id, cat_title FROM forum_category
                                WHERE id = $cat_id
                                AND course_id = $course_id");
    list($cat_id, $cat_title) = mysql_fetch_row($result);
    $tool_content .= "
        <form action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;forumcatsave=yes' method='post' onsubmit=\"return checkrequired(this,'cat_title');\">
        <input type='hidden' name='cat_id' value='$cat_id'>
        <fieldset>
        <legend>$langModCatName</legend>
        <table class='tbl' width='100%'>
        <tr>
        <th>$langCat</th>
        <td><input type='text' name='cat_title' size='55' value='$cat_title' /></td>
        </tr>
        <tr>
        <th>&nbsp;</th>
        <td class='right'><input type='submit' value='$langModify'></td>
        </tr>
        </table>
        </fieldset>
        </form>";
}

// Save forum category
elseif (isset($_GET['forumcatsave'])) {
    db_query("UPDATE forum_category SET cat_title = " . autoquote($_POST['cat_title']) . "
                                        WHERE id = $cat_id AND course_id = $course_id");
    $tool_content .= "<p class='success'>$langNameCatMod</p>
                                <p>&laquo; <a href='index.php?course=$course_code'>$langBack</a></p>";
}
// Save forum
elseif (isset($_GET['forumgosave'])) {
    $nameTools = $langDelete;
    $navigation[] = array("url" => "../forum_admin/forum_admin.php?course=$course_code", "name" => $langCatForumAdmin);
    db_query("UPDATE forum SET name = " . autoquote($_POST['forum_name']) . ",
                                   `desc` = " . autoquote(purify($_POST['forum_desc'])) . ",
                                   cat_id = $cat_id
                                WHERE id = $forum_id
                                AND course_id = $course_id");
    $fidx->store($forum_id);
    $tool_content .= "<p class='success'>$langForumDataChanged</p>
                                <p>&laquo; <a href='index.php?course=$course_code'>$langBack</a></p>";
}

// Add category to forums
elseif (isset($_GET['forumcatadd'])) {
    db_query("INSERT INTO forum_category
                        SET cat_title = " . autoquote($_POST['categories']) . ",
                        course_id = $course_id");
    $tool_content .= "<p class='success'>$langCatAdded</p>
                                <p>&laquo; <a href='index.php?course=$course_code'>$langBack</a></p>";
}

// forum go add
elseif (isset($_GET['forumgoadd'])) {
    $nameTools = $langAdd;
    $navigation[] = array('url' => "../forum_admin/forum_admin.php?course=$course_code",
        'name' => $langCatForumAdmin);
    $ctg = category_name($cat_id);
    db_query("INSERT INTO forum (name, `desc`, cat_id, course_id)
                                VALUES (" . autoquote($_POST['forum_name']) . ",
                                        " . autoquote($_POST['forum_desc']) . ",
                                        $cat_id, $course_id)");
    $forid = mysql_insert_id();
    $fidx->store($forid);
    // --------------------------------
    // notify users
    // --------------------------------
    $subject_notify = "$logo - $langCatNotify";
    $sql = db_query("SELECT DISTINCT user_id FROM forum_notify
                                WHERE cat_id = $cat_id AND
                                        notify_sent = 1 AND
                                        course_id = $course_id AND
                                        user_id <> $uid", $mysqlMainDb);
    $body_topic_notify = "$langBodyCatNotify $langInCat '$ctg' \n\n$gunet";
    while ($r = mysql_fetch_array($sql)) {
        if (get_user_email_notification($r['user_id'], $course_id)) {
            $linkhere = "&nbsp;<a href='${urlServer}main/profile/emailunsubscribe.php?cid=$course_id'>$langHere</a>.";
            $unsubscribe = "<br /><br />$langNote: " . sprintf($langLinkUnsubscribe, $title);
            $body_topic_notify .= $unsubscribe . $linkhere;
            $emailaddr = uid_to_email($r['user_id']);
            send_mail('', '', '', $emailaddr, $subject_notify, $body_topic_notify, $charset);
        }
    }
    // end of notification
    $tool_content .= "<p class='success'>$langForumCategoryAdded</p>
                                <p>&laquo; <a href='index.php?course=$course_code'>$langBack</a></p>";
}

// delete forum category
elseif (isset($_GET['forumcatdel'])) {
    $result = db_query("SELECT id FROM forum WHERE cat_id = $cat_id AND course_id = $course_id");
    while (list($forum_id) = mysql_fetch_row($result)) {
        $result2 = db_query("SELECT id FROM forum_topic WHERE forum_id = " . intval($forum_id));
        while (list($topic_id) = mysql_fetch_row($result2)) {
            db_query("DELETE FROM forum_post WHERE topic_id = $topic_id");
            $fpdx->removeByTopic($topic_id);
        }
        db_query("DELETE FROM forum_topic WHERE forum_id = $forum_id");
        $ftdx->removeByForum($forum_id);
        db_query("DELETE FROM forum_notify WHERE forum_id = $forum_id AND course_id = $course_id");
        $fidx->remove($forum_id);
    }
    db_query("DELETE FROM forum WHERE cat_id = $cat_id AND course_id = $course_id");
    db_query("DELETE FROM forum_notify WHERE cat_id = $cat_id AND course_id = $course_id");
    db_query("DELETE FROM forum_category WHERE id = $cat_id AND course_id = $course_id");
    $tool_content .= "<p class='success'>$langCatForumDelete</p>
                                <p>&laquo; <a href='index.php?course=$course_code'>$langBack</a></p>";
}

// delete forum
elseif (isset($_GET['forumgodel'])) {
    $nameTools = $langDelete;
    $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langCatForumAdmin);
    $result = db_query("SELECT id FROM forum WHERE id = $forum_id AND course_id = $course_id");
    while (list($forum_id) = mysql_fetch_row($result)) {
        $result2 = db_query("SELECT id FROM forum_topic WHERE forum_id = " . intval($forum_id));
        while (list($topic_id) = mysql_fetch_row($result2)) {
            db_query("DELETE FROM forum_post WHERE topic_id = $topic_id");
            $fpdx->removeByTopic($topic_id);
        }
        db_query("DELETE FROM forum_topic WHERE forum_id = $forum_id");
        $ftdx->removeByForum($forum_id);
        db_query("DELETE FROM forum_notify WHERE forum_id = $forum_id AND course_id = $course_id");
        db_query("DELETE FROM forum WHERE id = $forum_id AND course_id = $course_id");
        $fidx->remove($forum_id);
        db_query("UPDATE `group` SET forum_id = 0
                        WHERE forum_id = $forum_id
                        AND course_id = $course_id");
    }
    $tool_content .= "<p class='success'>$langForumDelete</p>
                                <p>&laquo; <a href='index.php?course=$course_code'>$langBack</a></p>";
} else {
    $tool_content .= "
        <form action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;forumcatadd=yes' method='post' onsubmit=\"return checkrequired(this,'categories');\">
        <fieldset>
        <legend>$langAddCategory</legend>
        <table class='tbl' width='100%'>
        <tr><th>$langCat</th><td><input type=text name=categories size=50></td></tr>
        <tr>
        <th>&nbsp;</th>
        <td class='right'><input type=submit value='$langAdd'></td>
        </tr>
        </table>
        </fieldset>
        </form>";
}
draw($tool_content, 2, null, $head_content);
