<?
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2008  Greek Universities Network - GUnet
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

/* @version $Id$
@last update: 2006-12-19 by Evelthon Prodromou
*/

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'For';
$require_prof = true;
include '../../include/baseTheme.php';

$nameTools = $langOrganisation;
$navigation[]= array ("url"=>"../phpbb/index.php", "name"=> $langForums);

$tool_content = $head_content = "";

if($is_adminOfCourse) {

$head_content .= '
<script>
function confirmation ()
{
    if (confirm("'.$langConfirmDelete.'"))
        {return true;}
    else
        {return false;}
}
</script>
';
	// forum go
	if(isset($forumgo)) {
	$nameTools = $langAddForum;
	$navigation[]= array ("url"=>"../forum_admin/forum_admin.php", "name"=> $langOrganisation);
		$tool_content .= "
	
	<table class=\"Deps\" width=\"99%\">
    <tbody>
    <tr>
     <th>$langForCat: <br /><b>$ctg</b></th>
     <td><a href=\"$PHP_SELF?forumadmin=yes\">$langBackCat</a></td>
    </tr>
    </tbody>
    </table>
    <br />";
		$result = db_query("select forum_id, forum_name, forum_desc, forum_access, forum_moderator, forum_type from forums where cat_id='$cat_id'", $currentCourseID);
	if ($result and mysql_num_rows($result) > 0) {
		$tool_content .= "
		<form action=\"forum_admin.php?forumgoadd=yes&ctg=$ctg&cat_id=$cat_id\" method=post>
    		<table width=99% class=\"ForumAdmSum\">
    		<thead>
    		<tr>
      		<th width='10'>$langID</th>
      		<th>&nbsp;$langForName</th>
      		<th>$langDescription</th>
      		<th width='75'><div align=\"center\">$langActions</div></th>
    		</tr>
    		</thead>
    		<tbody>";
		$i=1;
		while(list($forum_id, $forum_name, $forum_desc, $forum_access,
			$forum_moderator, $forum_type) = mysql_fetch_row($result)) {
			$tool_content .= "
      			<tr><td align='right'>$i.</td>
      			<td align='left'>$forum_name</td>
      			<td align='left'>$forum_desc&nbsp;</td>";
			$tool_content .= "<td align='center'>
        		<a href=forum_admin.php?forumgoedit=yes&forum_id=$forum_id&ctg=$ctg&cat_id=$cat_id><img src='../../template/classic/img/edit.gif' title='$langModify' border='0'></img></a>
        		&nbsp;
        		<a href=forum_admin.php?forumgodel=yes&forum_id=$forum_id&cat_id=$cat_id&ctg=$ctg&ok=0><img src='../../template/classic/img/delete.gif' title='$langDelete' border='0'></img></a>
      			</td></tr>";
		$i++;
	}
	$tool_content .= "
    </tbody>
    </table>
    </form>
	
    <br/>";
	} else {
		    $tool_content .= "<p class=\"alert1\">$l_noforum</p>
    			</tr>";
	}

	$tool_content .= "
    <form action=\"forum_admin.php?forumgoadd=yes&ctg=$ctg&cat_id=$cat_id\" method=post>
    <table width=99% class=\"FormData\" align=\"left\">
    <tbody>
    <tr>
      <th width=\"220\">&nbsp;</th>
      <td><b>$langAddForCat</b></td>
    </tr>
    <tr>
      <th class=\"left\">$langCat:</th>
      <td>$ctg</td>
    </tr>
    <tr>
      <th class=\"left\">$langForName:</th>
      <td><input type=text name=forum_name size=40 class=\"FormData_InputText\"></td>
    </tr>
    <tr>
      <th class=\"left\">$langDescription:</th>
      <td><textarea name=forum_desc cols=37 rows=3 class=\"FormData_InputText\"></textarea></td>
    </tr>
    <tr>
      <th>&nbsp;</th>
      <td>
        <input type=hidden name=cat_id value=\"$cat_id\">
        <input type=hidden name=forumgoadd value=yes>
        <input type=submit value=$langAdd>
      </td>
    </tr>
    </tbody>
    </table>
    </form>";
	}
	// forum go edit	
	elseif(isset($forumgoedit)) {
	$nameTools = $langEditForum;
	$navigation[]= array ("url"=>"../forum_admin/forum_admin.php", "name"=> $langOrganisation);
	
		$result = db_query("select forum_id, forum_name, forum_desc, forum_access, forum_moderator,
    		cat_id, forum_type from forums where forum_id='$forum_id'", $currentCourseID);
		list($forum_id, $forum_name, $forum_desc, $forum_access, $forum_moderator, $cat_id_1,
		$forum_type) = mysql_fetch_row($result);
		$tool_content .= "
    <form action=\"forum_admin.php?forumgosave=yes&ctg=$ctg&cat_id=".@$cat_id."\" method=post>
    <input type=hidden name=forum_id value=$forum_id>
    <table width=99% class=\"FormData\">
    <tbody>
    <tr>
      <th width=\"220\">&nbsp;</th>
      <td><b>$langChangeForum</b></td>
    </tr>
    <tr>
      <th class=\"left\">$langForName</th>
      <td><input type=text name=forum_name size=50 value=\"$forum_name\" class=\"FormData_InputText\"></td>
    </tr>
    <tr>
      <th class=\"left\">$langDescription</th>
      <td><textarea name=forum_desc cols=47 rows=3 class=\"FormData_InputText\">$forum_desc</textarea></td>
    </tr>
    <tr>
      <th class=\"left\">$langChangeCat</th>
      <td>
          <SELECT NAME=cat_id  class=\"auth_input\">";
	  $result = db_query("select cat_id, cat_title from catagories", $currentCourseID);
		while(list($cat_id, $cat_title) = mysql_fetch_row($result)) {
			if ($cat_id == $cat_id_1) {
				$tool_content .= "<OPTION VALUE=\"$cat_id\" selected>$cat_title</OPTION>";          }
				else {
					$tool_content .= "<OPTION VALUE=\"$cat_id\">$cat_title</OPTION>";
				}
		}
	$tool_content .= "
          </SELECT> 
	  </td>
    </tr>
    <tr>
      <th>&nbsp;</th>
      <td><input type=hidden name=forumgosave value=yes>
          <input type=submit value=\"$langSave\">
      </td>
    </tr>
    </thead>
    </table>

    </form>";
	}

	// edit forum category	
	elseif(isset($forumcatedit)) {
		$result = db_query("select cat_id, cat_title from catagories where cat_id='$cat_id'", $currentCourseID);
		list($cat_id, $cat_title) = mysql_fetch_row($result);
		$tool_content .= "
    <form action=\"forum_admin.php?forumcatsave=yes\" method=post>
    <input type=hidden name=cat_id value=$cat_id>		
    <table width=99% class=\"FormData\">
    <tbody>
    <tr>
      <th width=\"220\">&nbsp;</th>
      <td><b>$langModCatName</b></td>
    </tr>
    <tr>
      <th class=\"left\">$langCat</th>
      <td><input type=text name=cat_title size=55 value=\"$cat_title\" class=\"FormData_InputText\"></td>
    </tr>
    <tr>
      <th>&nbsp;</th>
      <td><input type=submit value=\"$langSave\"></td>
    </tr>
    </thead>
    </table>	
    </form>";
	}

	// save forum category
	elseif (isset($forumcatsave)) {
		db_query("update catagories set cat_title='$cat_title' where cat_id='$cat_id'", $currentCourseID);

	$tool_content .= "
    <table width=\"99%\">
    <tbody>
    <tr>
      <td class=\"success\">$langNameCatMod</td>
    </tr>
    </tbody>
    </table>
    <br />
    <p align=\"right\"><a href=\"$PHP_SELF?forumadmin=yes\">$langBack</a></p>";

	}

	// forum go save
	elseif(isset($forumgosave)) {
	$nameTools = $langDelete;
	$navigation[]= array ("url"=>"../forum_admin/forum_admin.php", "name"=> $langOrganisation);
	
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
        <td class=\"success\">$langForumDataChanged</td>
      </tr>
      </tbody>
      </table>
      <br />
      <p align=\"right\"><a href=\"$PHP_SELF?forumgo=yes&cat_id=$cat_id&ctg=$ctg\">$langBack</a></p>";
	}

	// forum add category
	elseif(isset($forumcatadd)) {
		db_query("insert into catagories values (NULL, '$catagories', NULL)", $currentCourseID);
		$tool_content .= "
    <table width=\"99%\">
    <tbody>
    <tr>
      <td class=\"success\">$langCatAdded</td>
    </tr>
    </tbody>
    </table>
    <br />
    <p align=\"right\"><a href=\"$PHP_SELF?forumadmin=yes\">$langBack</a></p>";
		}

	// forum go add
	elseif(isset($forumgoadd)) {
	$nameTools = $langAddForum;
	$navigation[]= array ("url"=>"../forum_admin/forum_admin.php", "name"=> $langOrganisation);
	
		$result = @db_query("select user_id from users where username='$forum_moderator'", $currentCourseID);
		list($forum_moderator) = mysql_fetch_row($result);
		db_query("update users set user_level='2' where user_id='$forum_moderator'", $currentCourseID);
		@db_query("insert into forums (forum_id, forum_name, forum_desc, forum_access, forum_moderator, cat_id, forum_type)
        	VALUES (NULL, '$forum_name', '$forum_desc', '2', '1', '$cat_id', '$forum_type')", $currentCourseID);
		$idforum=db_query("select forum_id from forums where forum_name='$forum_name'", $currentCourseID);
		while ($my_forum_id = mysql_fetch_array($idforum)) {
			$forid=$my_forum_id[0];
		}
		$tool_content .= "
    <table width=\"99%\">
    <tbody>
    <tr>
      <td class=\"success\">$langForumCategoryAdded</td>
    </tr>
    </tbody>
    </table>
    <br />
    <p align=\"right\"><a href=\"$PHP_SELF?forumgo=yes&cat_id=$cat_id&ctg=$ctg\">$langBack</a></p>";	
		}
	
	// forum delete category
	elseif(isset($forumcatdel)) {
		$result = db_query("select forum_id from forums where cat_id='$cat_id'", $currentCourseID);
		while(list($forum_id) = mysql_fetch_row($result)) {
			db_query("delete from topics where forum_id=$forum_id", $currentCourseID);
		}
		db_query("delete from forums where cat_id=$cat_id", $currentCourseID);
		db_query("delete from catagories where cat_id=$cat_id", $currentCourseID);
		$tool_content .= "
    <table width=\"99%\">
    <tbody>
    <tr>
      <td class=\"success\">$langCatForumDelete</td>
    </tr>
    </tbody>
    </table>
    <br />
    <p align=\"right\"><a href=\"$PHP_SELF?forumadmin=yes\">$langBack</a></p>";
	}

	// forum delete
	elseif(isset($forumgodel)){
	$nameTools = $langDelete;
	$navigation[]= array ("url"=>"../forum_admin/forum_admin.php", "name"=> $langOrganisation);
	
		db_query("delete from topics where forum_id=$forum_id", $currentCourseID);
		db_query("delete from forums where forum_id=$forum_id", $currentCourseID);
		$tool_content .= "
    <table width=\"99%\">
    <tbody>
    <tr>
      <td class=\"success\">$langForumDelete</td>
    </tr>
    </tbody>
    </table>
    <br />
    <p align=\"right\"><a href=\"$PHP_SELF?forumgo=yes&ctg=$ctg&cat_id=$cat_id\">$langBack</a></p>";
	} else {
		$tool_content .= "
    <form action=\"forum_admin.php?forumadmin=yes\" method=post></td><tr><td>";
		$tool_content .= "
    <table width=99% class=\"ForumCategory\">
    <thead>
    <tr>
      <th width='10'>$langID</th>
      <th>$langForCategories</th>
      <th width='70'>$langNbFor</th>
      <th width='75'>$langActions</th>
    </tr>
";
	$result = db_query("select cat_id, cat_title from catagories order by cat_id", $currentCourseID);
	$i=1;

	while(list($cat_id, $cat_title) = mysql_fetch_row($result)) {
		$gets = db_query("select count(*) as total from forums where cat_id=$cat_id", $currentCourseID);
		$numbers= mysql_fetch_array($gets);
		$tool_content .= "<tr><td><div align='right'>$i.</div></td>
      		<td><div align='left'>$cat_title &nbsp;</div></td>
      		<td>$numbers[total]</td><td>
        	<a href='forum_admin.php?forumgo=yes&cat_id=$cat_id&ctg=$cat_title'><img src='../../template/classic/img/forum_on.gif' border='0' title='$langForums'></img></a>&nbsp;
        	<a href='forum_admin.php?forumcatedit=yes&cat_id=$cat_id'><img src='../../template/classic/img/edit.gif' border='0' title='$langModify'></img></a>&nbsp;
        	<a href='forum_admin.php?forumcatdel=yes&cat_id=$cat_id&ok=0' onClick='return confirmation();'><img src='../../template/classic/img/delete.gif' border='0' title='$langDelete'></img></a>
      		</td>
    		</tr>";
			$i++;
		}
	$tool_content .= "
    </thead>
    </table>
    </form>
	
    <br/>
    <form action=\"forum_admin.php?forumcatadd=yes\" method=post>

    <table width=99% class=\"FormData\" align=\"left\">
    <tbody>
    <tr>
      <th width=\"220\">&nbsp;</th>
      <td><b>$langAddCategory</b></td>
    </tr>
    <tr>
      <th class=\"left\">$langCat</th>
      <td><input type=text name=catagories size=50 class=\"FormData_InputText\"></td>
    </tr>
    <tr>
      <th>&nbsp;</th>
      <td><input type=hidden name=forumcatadd value=yes><input type=submit value=\"$langAdd\"></td>
    </tr>
    </thead>
    </table>

    </form>
	<br/>
	<p><b><u>$langNote</u>:</b> ($langForCategories)<br/>
       <em>$langAddForums</em>
    </p>";
	}
} else {
	$tool_content .= "$langNotAllowed<br>";
}
if($is_adminOfCourse && isset($head_content)) {
	draw($tool_content, 2, 'forum_admin', $head_content);	
} else {
	draw($tool_content, 2, 'forum_admin');
}
?>
