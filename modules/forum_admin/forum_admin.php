<?
/*
+----------------------------------------------------------------------+
| CLAROLINE version 1.3.0 $Revision$                             |
+----------------------------------------------------------------------+
| Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
+----------------------------------------------------------------------+
|   $Id$         |
+----------------------------------------------------------------------+
|   This program is free software; you can redistribute it and/or      |
|   modify it under the terms of the GNU General Public License        |
|   as published by the Free Software Foundation; either version 2     |
|   of the License, or (at your option) any later version.             |
|                                                                      |
|   This program is distributed in the hope that it will be useful,    |
|   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
|   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
|   GNU General Public License for more details.                       |
|                                                                      |
|   You should have received a copy of the GNU General Public License  |
|   along with this program; if not, write to the Free Software        |
|   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
|   02111-1307, USA. The GNU GPL license is also available through     |
|   the world-wide-web at http://www.gnu.org/copyleft/gpl.html         |
+----------------------------------------------------------------------+
| Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
|          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
|          Christophe Gesche <gesche@ipm.ucl.ac.be>                    |
+----------------------------------------------------------------------+
*/
/* @version $Id$
@last update: 2006-12-19 by Evelthon Prodromou
*/
$require_current_course = TRUE;
$langFiles = 'forum_admin';
$require_help = TRUE;
$helpTopic = 'For';
$require_prof = true;
include '../../include/baseTheme.php';

$nameTools = $langOrganisation;
$navigation[]= array ("url"=>"../phpbb/index.php", "name"=> $langForums);

$tool_content = "";//initialise $tool_content


########### AFFICHER ####################################
#########################################################
if($is_adminOfCourse) {
	##################FORUM GO ################################
	############################################################
	if(isset($forumgo))
	{
		$tool_content .= "<p><b>$langForCat: $ctg</b> &nbsp;&nbsp;(<a href=\"$PHP_SELF?forumadmin=yes\">$langBackCat</a>)</p>
    <form action=\"forum_admin.php?forumgoadd=yes&ctg=$ctg&cat_id=$cat_id\" method=post>
    <table width=99%>
    <thead>
    <tr>
	<th>$langForName</th>
	<th>$langDescription</th>
	<th>$langFunctions</th></tr></thead></tbody>";
		$result = db_query("select forum_id, forum_name, forum_desc, forum_access, forum_moderator, forum_type from forums where cat_id='$cat_id'", $currentCourseID);
		$i=0;
		while(list($forum_id, $forum_name, $forum_desc, $forum_access,
		$forum_moderator, $forum_type) = mysql_fetch_row($result)) {
			if($i%2==0){
				$tool_content .= "<tr bgcolor=$color1>";
			}     	// IF
			elseif($i%2==1) {
				$tool_content .= "<tr bgcolor=$color2>";
			}
			$tool_content .= "<td>$forum_name</td>
	<td valign=top>$forum_desc&nbsp;</td>";

			$tool_content .= "
	<td><a href=forum_admin.php?forumgoedit=yes&forum_id=$forum_id&ctg=$ctg&cat_id=$cat_id>$langModify</a>
         | <a href=forum_admin.php?forumgodel=yes&forum_id=$forum_id&cat_id=$cat_id&ctg=$ctg&ok=0>$langDelete</a>
		</td></tr>";
			$i++;
		}
		$tool_content .= "</form></td></tr></tbody></table>
    <br><p><b>$langAddForCat: $ctg</b></p>
    <form action=\"forum_admin.php?forumgoadd=yes&ctg=$ctg&cat_id=$cat_id\" method=post>
    <table width=99%>
    <thead>
    <tr><th>$langForName</th><td><input type=text name=forum_name size=40></td></tr>
    <tr><th>$langDescription</th><td><textarea name=forum_desc cols=40 rows=3></textarea></td></tr>";

		$tool_content .= "</thead></table>
    <input type=hidden name=cat_id value=\"$cat_id\">
    <input type=hidden name=forumgoadd value=yes><br/>
    <input type=submit value=$langAdd>
    </form>";
	}

	############################################
	##########ForumGoEdit#######################
	#############################################

	elseif(isset($forumgoedit)) {

		$result = db_query("select forum_id, forum_name, forum_desc, forum_access, forum_moderator,
    cat_id, forum_type from forums where forum_id='$forum_id'", $currentCourseID);

		list($forum_id, $forum_name, $forum_desc, $forum_access, $forum_moderator, $cat_id_1,
		$forum_type) = mysql_fetch_row($result);
		$tool_content .= "<b>$langModify $forum_name</b>
    <form action=\"forum_admin.php?forumgosave=yes&ctg=$ctg&cat_id=".@$cat_id."\" method=post>
    <input type=hidden name=forum_id value=$forum_id>
    <table width=99%>
    <thead>
   
    <tr><th>$langForName</th>
    <td><input type=text name=forum_name size=50 value=\"$forum_name\"></td></tr>
    <tr><th>$langDescription</th><td>
    <textarea name=forum_desc cols=50 rows=3>$forum_desc</textarea></td></tr>
    <tr><th>$langChangeCat</th>
    <td><SELECT NAME=cat_id>";
		$result = db_query("select cat_id, cat_title from catagories", $currentCourseID);
		while(list($cat_id, $cat_title) = mysql_fetch_row($result)) {
			if ($cat_id == $cat_id_1) {
				$tool_content .= "<OPTION VALUE=\"$cat_id\" selected>$cat_title</OPTION>";          }
				else {
					$tool_content .= "<OPTION VALUE=\"$cat_id\">$cat_title</OPTION>";
				}
		}
		$tool_content .= "</SELECT></td></tr></thead></table>
    <input type=hidden name=forumgosave value=yes><br/>
    <input type=submit value=\"$langSave\">
    </form>";
	}

	#############################################################
	####################ForumCatEdit##############################
	#############################################################

	elseif(isset($forumcatedit)) {
		$result = db_query("select cat_id, cat_title from catagories where cat_id='$cat_id'", $currentCourseID);
		list($cat_id, $cat_title) = mysql_fetch_row($result);
		$tool_content .= "<b>$langModCatName</b>
    <form action=\"forum_admin.php?forumcatsave=yes\" method=post>
    <input type=hidden name=cat_id value=$cat_id>
    <table width=99%><tr><td>$langCat</td><td><input type=text name=cat_title size=55 value=\"$cat_title\"></td></tr><tr><td>
    </td></tr></table>";
		//   <input type=hidden name=forumcatsave value=yes>
		$tool_content .= "<input type=submit value=\"$langSave\">
		</form>";
	}

	#############################################################
	###############ForumCatSave###################################
	#############################################################

	elseif (isset($forumcatsave)) {
		db_query("update catagories set cat_title='$cat_title' where cat_id='$cat_id'", $currentCourseID);
		// $tool_content .= "<META http-equiv=\"REFRESH\" CONTENT=\"0; URL=\"$PHP_SELF?forumadmin=yes\"> ";
		$tool_content .= "$langNameCatMod, &nbsp;<a href=\"$PHP_SELF?forumadmin=yes\">$langBack</a>";
	}

	#############################################################
	###################ForumGoSave ###############################
	#############################################################

	elseif(isset($forumgosave)) {
		$result = @db_query("select user_id from users where username='$forum_moderator'", $currentCourseID);
		list($forum_moderator) = mysql_fetch_row($result);

		@db_query("update users set user_level='2' where user_id='$forum_moderator'", $currrentCourseID);
		@db_query("update forums set forum_name='$forum_name', forum_desc='$forum_desc',
            forum_access='2', forum_moderator='1', cat_id='$cat_id',
            forum_type='$forum_type' where forum_id='$forum_id'", $currentCourseID);
		
		$tool_content .= "
			<table width=\"99%\">
			<tbody>
			<tr>
			<td class=\"success\"><p><b>$langForumDataChanged</b></p>
			<p><a href=\"$PHP_SELF?forumgo=yes&cat_id=$cat_id&ctg=$ctg\">$langBack</a></p>
			</td>
			</tr>
			</tbody>
			</table>";
		/*$tool_content .= "
	 <a href=\"$PHP_SELF?forumgo=yes&cat_id=$cat_id&ctg=$ctg\">$langBack</a>";*/

	}

	#############################################################
	##################### ForumCatAdd ###########################
	#############################################################

	// function ForumCatAdd($catagories)

	elseif(isset($forumcatadd)) {
		db_query("insert into catagories values (NULL, '$catagories', NULL)", $currentCourseID);
		
		$tool_content .= "
			<table width=\"99%\">
			<tbody>
			<tr>
			<td class=\"success\"><p><b>$langCatAdded</b></p>
			<p><a href=\"$PHP_SELF?forumadmin=yes\">$langBack</a></p>
			</td>
			</tr>
			</tbody>
			</table>";
//		$tool_content .= "$langCatAdded,&nbsp;<a href=\"$PHP_SELF?forumadmin=yes\">$langBack</a>";
		//Header("Location: forum_admin.php?afficher=yes");
		//$tool_content .= "<a href=\$PHP_SELF?forumgo=yes&cat_id=$cat_id&ctg=$ctg\">Retour</a>";
	}

	#############################################################
	################### ForumGoAdd ################################
	#############################################################

	elseif(isset($forumgoadd)) {
		$result = @db_query("select user_id from users where username='$forum_moderator'", $currentCourseID);
		list($forum_moderator) = mysql_fetch_row($result);

		db_query("update users set user_level='2' where user_id='$forum_moderator'", $currentCourseID);
		@db_query("insert into forums (forum_id, forum_name, forum_desc, forum_access, forum_moderator, cat_id, forum_type)
        VALUES (NULL, '$forum_name', '$forum_desc', '2',
        '1', '$cat_id', '$forum_type')", $currentCourseID);
		$idforum=db_query("select forum_id from forums where forum_name='$forum_name'", $currentCourseID);
		while ($my_forum_id = mysql_fetch_array($idforum)) {
			$forid=$my_forum_id[0];
		}
		//db_query("insert into forum_mods (forum_id, user_id) values ('$forid', '1')", $currentCourseID);

		$tool_content .= "
			<table width=\"99%\">
			<tbody>
			<tr>
			<td class=\"success\"><p><b>$langForumCategoryAdded</b></p>
			<p><a href=\"$PHP_SELF?forumgo=yes&cat_id=$cat_id&ctg=$ctg\">$langBack</a></p>
			</td>
			</tr>
			</tbody>
			</table>";

	}
	#############################################################
	#################ForumCatDel###################################
	#############################################################

	elseif(isset($forumcatdel)) {
		$result = db_query("select forum_id from forums where cat_id='$cat_id'", $currentCourseID);
		while(list($forum_id) = mysql_fetch_row($result)) {
			db_query("delete from topics where forum_id=$forum_id", $currentCourseID);
		}
		db_query("delete from forums where cat_id=$cat_id", $currentCourseID);
		db_query("delete from catagories where cat_id=$cat_id", $currentCourseID);
		$tool_content .= "<a href=\"$PHP_SELF?forumadmin=yes\">$langBack</a>";

	}

	#############################################################
	####################ForumGoDel ################################
	#############################################################


	elseif(isset($forumgodel)){

		db_query("delete from topics where forum_id=$forum_id", $currentCourseID);
		db_query("delete from forums where forum_id=$forum_id", $currentCourseID);
		$tool_content .= "
			<table width=\"99%\">
			<tbody>
			<tr>
			<td class=\"success\"><p><b>$langForumDelete</b></p>
			<p><a href=\"$PHP_SELF?forumgo=yes&ctg=$ctg&cat_id=$cat_id\">$langBack</a></p>
			</td>
			</tr>
			</tbody>
			</table>";

	}
	############################################
	############################################

	else {
		$tool_content .= "<p><b>$langForCategories</b></p><p><em>$langAddForums.</em></p>
    <form action=\"forum_admin.php?forumadmin=yes\" method=post></td><tr><td>";

		$tool_content .= "<table width=99%>
    <thead>
    <tr><th>ID</th><th>$langCategories</th>
    <th>$langNbFor</th><th>$langFunctions</th></tr></thead><tbody>";
		$result = db_query("select cat_id, cat_title from catagories order by cat_id", $currentCourseID);

		$i=0;
		while(list($cat_id, $cat_title) = mysql_fetch_row($result)) {
			$gets = db_query("select count(*) as total from forums where cat_id=$cat_id", $currentCourseID);
			$numbers= mysql_fetch_array($gets);

			if($i%2==0){
				$tool_content .= "<tr bgcolor=$color1>";
			}     	// IF
			elseif($i%2==1) {
				$tool_content .= "<tr bgcolor=$color2>";
			}
			$tool_content .= "<td>$cat_id</td>
		<td>$cat_title &nbsp;</td>
		<td>$numbers[total]</td>
		<td>
		<a href=\"forum_admin.php?forumgo=yes&cat_id=$cat_id&ctg=$cat_title\">$langForums</a> |
		<a href=forum_admin.php?forumcatedit=yes&cat_id=$cat_id>$langModify</a> |
		<a href=forum_admin.php?forumcatdel=yes&cat_id=$cat_id&ok=0>$langDelete</a></td></tr>";
			$i++;
		}

		$tool_content .= "</tbody></table></form>
  
    <br/><p><b>$langAddCategory</b></p>    
    <form action=\"forum_admin.php?forumcatadd=yes\" method=post>
    <table width=99%><thead><tr><th>$langCat</th>
    <td><input type=text name=catagories size=50></td></tr>
    </thead></table>
	<input type=hidden name=forumcatadd value=yes><br/>
    <input type=submit value=\"$langAdd\"></form>";
	}
} else {
	$tool_content .= "$langNotAllowed<br>";
}
draw($tool_content, 2);
?>
