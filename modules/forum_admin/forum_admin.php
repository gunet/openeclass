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
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
 */

$require_current_course = TRUE;
$langFiles = 'forum_admin';
$require_help = TRUE;
$helpTopic = 'For';
include '../../include/init.php';

$nameTools = $langOrganisation;
$navigation[]= array ("url"=>"../phpbb/index.php", "name"=> $langForums);

begin_page();

	
echo "<tr><td>";


########### AFFICHER ####################################
#########################################################
if($is_adminOfCourse) 
{
##################FORUM GO ################################
############################################################
    if(isset($forumgo))
	{
    echo "<font size=2 face=\"arial, helvetica\"><b>$langForCat $ctg</b> &nbsp;&nbsp;[<a href=\"$PHP_SELF?forumadmin=yes\">$langBackCat</a>]</font size></font>
    <form action=\"forum_admin.php?forumgoadd=yes&ctg=$ctg&cat_id=$cat_id\" method=post>
    <table border=0 width=600 cellpadding=4 cellspacing=2><tr bgcolor=silver>
	<td width=25%><font size=2 face=\"arial, helvetica\"><b>$langForName</b></td>
	<td width=50%><font size=2 face=\"arial, helvetica\"><b>$langDescription</b></td>
	<td width=25%><font size=2 face=\"arial, helvetica\"><b>$langFunctions</b></td></tr>";
    $result = mysql_query("select forum_id, forum_name, forum_desc, forum_access,
    forum_moderator, forum_type from forums where cat_id='$cat_id'");
    $i=0;
    while(list($forum_id, $forum_name, $forum_desc, $forum_access,
    $forum_moderator, $forum_type) = mysql_fetch_row($result)) {
	    if($i%2==0){
		echo "<tr bgcolor=$color1>";
	}     	// IF
	elseif($i%2==1) {
		echo "<tr bgcolor=$color2>";
	}
	echo "<td valign=top><font size=2 face=\"arial, helvetica\">$forum_name</td>
	<td valign=top><font size=2 face=\"arial, helvetica\">$forum_desc&nbsp;</td>";

	echo "
	<td valign=top><font size=2 face=\"arial, helvetica\"><a href=forum_admin.php?forumgoedit=yes&forum_id=$forum_id&ctg=$ctg&cat_id=$cat_id>$langModify</a>
         | <a href=forum_admin.php?forumgodel=yes&forum_id=$forum_id&cat_id=$cat_id&ctg=$ctg&ok=0>$langDelete</a>
		</td></tr>";
	$i++;
    }
    echo "</form></td></tr></table>
    <br><font size=2 face=\"arial, helvetica\"><b>$langAddForCat $ctg</b><br>
    <font size=2 face=\"arial, helvetica\">
    <form action=\"forum_admin.php?forumgoadd=yes&ctg=$ctg&cat_id=$cat_id\" method=post>
    <table border=0 width=600>
    <tr><td><font size=2 face=\"arial, helvetica\">$langForName</td><td><input type=text name=forum_name size=40></td></tr>
    <tr><td><font size=2 face=\"arial, helvetica\">$langDescription</td><td><textarea name=forum_desc cols=40 rows=3></textarea></td></tr>";

    echo "</table>
    <input type=hidden name=cat_id value=\"$cat_id\">
    <input type=hidden name=forumgoadd value=yes>
    <input type=submit value=$langAdd>
    </form>";
}

############################################
##########ForumGoEdit#######################
#############################################

elseif(isset($forumgoedit)) {

    $result = mysql_query("select forum_id, forum_name, forum_desc, forum_access, forum_moderator,
    cat_id, forum_type from forums where forum_id='$forum_id'");

    list($forum_id, $forum_name, $forum_desc, $forum_access, $forum_moderator, $cat_id_1,
    $forum_type) = mysql_fetch_row($result);
    echo "<font size=2 face=\"arial, helvetica\"><b>$langModify $forum_name</b></font>
    <form action=\"forum_admin.php?forumgosave=yes&ctg=$ctg&cat_id=".@$cat_id."\" method=post>
    <input type=hidden name=forum_id value=$forum_id>
    <table border=0 width=600><tr><td>
    <tr><td><font size=2 face=\"arial, helvetica\">$langForName</td>
    <td><input type=text name=forum_name size=50 value=\"$forum_name\"></td></tr>
    <tr><td><font size=2 face=\"arial, helvetica\">$langDescription</td><td>
    <textarea name=forum_desc cols=50 rows=3>$forum_desc</textarea></td></tr>
    <tr><td><font size=2 face=\"arial, helvetica\">$langChangeCat</td>
    <td><SELECT NAME=cat_id>";
    $result = mysql_query("select cat_id, cat_title from catagories");
    while(list($cat_id, $cat_title) = mysql_fetch_row($result)) {
        if ($cat_id == $cat_id_1) {
    echo "<OPTION VALUE=\"$cat_id\" selected>$cat_title</OPTION>";          }
    else {
    echo "<OPTION VALUE=\"$cat_id\">$cat_title</OPTION>";
             }
                                                                                              }
    echo "</SELECT></td></tr></table>
    <input type=hidden name=forumgosave value=yes>
    <input type=submit value=\"$langSave\">
    </form>";
}

#############################################################
####################ForumCatEdit##############################
#############################################################

    elseif(isset($forumcatedit)) {
    $result = mysql_query("select cat_id, cat_title from catagories where cat_id='$cat_id'");
    list($cat_id, $cat_title) = mysql_fetch_row($result);
    echo "<font size=2 face=\"arial, helvetica\"><b>$langModCatName</b></font>
    <form action=\"forum_admin.php?forumcatsave=yes\" method=post>
    <input type=hidden name=cat_id value=$cat_id>
    <table border=0 width=600><tr><td><font size=2 face=\"arial, helvetica\">$langCat</td><td><input type=text name=cat_title size=55 value=\"$cat_title\"></td></tr><tr><td>
    </td></tr></table>";
    //   <input type=hidden name=forumcatsave value=yes>
    echo "<input type=submit value=\"$langSave\">
		</form>";
}

#############################################################
###############ForumCatSave###################################
#############################################################

elseif (isset($forumcatsave)) {
    mysql_query("update catagories set cat_title='$cat_title' where cat_id='$cat_id'");
    // echo "<META http-equiv=\"REFRESH\" CONTENT=\"0; URL=\"$PHP_SELF?forumadmin=yes\"> ";
    echo "<font size=2 face=\"arial, helvetica\">$langNameCatMod, &nbsp;<a href=\"$PHP_SELF?forumadmin=yes\">$langBack</a>";
}

#############################################################
###################ForumGoSave ###############################
#############################################################

elseif(isset($forumgosave)) {
    $result = @mysql_query("select user_id from users where username='$forum_moderator'");
    list($forum_moderator) = mysql_fetch_row($result);

	@mysql_query("update users set user_level='2' where user_id='$forum_moderator'");
	    @mysql_query("update forums set forum_name='$forum_name', forum_desc='$forum_desc',
            forum_access='2', forum_moderator='1', cat_id='$cat_id',
            forum_type='$forum_type' where forum_id='$forum_id'");
         echo "<font size=2 face=\"arial, helvetica\">
	 <a href=\"$PHP_SELF?forumgo=yes&cat_id=$cat_id&ctg=$ctg\">$langBack</a>";

}

#############################################################
##################### ForumCatAdd ###########################
#############################################################

// function ForumCatAdd($catagories)

elseif(isset($forumcatadd)) {
    mysql_query("insert into catagories values (NULL, '$catagories', NULL)");
    echo "<font size=2 face=\"arial, helvetica\">$langCatAdded,&nbsp;<a href=\"$PHP_SELF?forumadmin=yes\">$langBack</a>";
    //Header("Location: forum_admin.php?afficher=yes");
     //echo "<a href=\$PHP_SELF?forumgo=yes&cat_id=$cat_id&ctg=$ctg\">Retour</a>";
}

#############################################################
################### ForumGoAdd ################################
#############################################################

elseif(isset($forumgoadd)) {
    $result = @mysql_query("select user_id from users where username='$forum_moderator'");
    list($forum_moderator) = mysql_fetch_row($result);

	mysql_query("update users set user_level='2' where user_id='$forum_moderator'");
	@mysql_query("insert into forums (forum_id, forum_name, forum_desc, forum_access, forum_moderator, cat_id, forum_type)
        VALUES (NULL, '$forum_name', '$forum_desc', '2',
        '1', '$cat_id', '$forum_type')");
        $idforum=mysql_query("select forum_id from forums where forum_name='$forum_name'");
        while ($my_forum_id = mysql_fetch_array($idforum)) {
        $forid=$my_forum_id[0];
        }
        mysql_query("insert into forum_mods (forum_id, user_id) values ('$forid', '1')");
         echo "<font size=2 face=\"arial, helvetica\"><a href=\"$PHP_SELF?forumgo=yes&cat_id=$cat_id&ctg=$ctg\">$langBack</a>";

}
#############################################################
#################ForumCatDel###################################
#############################################################

elseif(isset($forumcatdel)) {
   	$result = mysql_query("select forum_id from forums where cat_id='$cat_id'");
	while(list($forum_id) = mysql_fetch_row($result)) {
	mysql_query("delete from forumtopics where forum_id=$forum_id");
	}
	mysql_query("delete from forums where cat_id=$cat_id");
	mysql_query("delete from catagories where cat_id=$cat_id");
 echo "<font size=2 face=\"arial, helvetica\"><a href=\"$PHP_SELF?forumadmin=yes\">$langBack</a>";
// 	echo "</TD></TR></TABLE></TD></TR></TABLE>";
	//include("footer.php");

}

#############################################################
####################ForumGoDel ################################
#############################################################


elseif(isset($forumgodel)){

	mysql_query("delete from forumtopics where forum_id=$forum_id");
	mysql_query("delete from forums where forum_id=$forum_id");
         echo "<font size=2 face=\"arial, helvetica\">
	 <a href=\"$PHP_SELF?forumgo=yes&ctg=$ctg&cat_id=$cat_id\">$langBack</a>";

}
############################################
############################################

else {
    echo "<font size=2 face=\"arial, helvetica\"><b>$langForCategories</b><br>$langAddForums.</font>
    <form action=\"forum_admin.php?forumadmin=yes\" method=post></td><tr><td>";

    echo "<table BORDER=0 CELLSPACING=2 CELLPADDING=4 width=600>
    <tr bgcolor=silver><td><font size=2 face=\"arial, helvetica\"><b>ID</b></td><td><b><font size=2 face=\"arial, helvetica\">$langCategories</b></td>
    <td><font size=2 face=\"arial, helvetica\"><b>$langNbFor</b></td><td><font size=2 face=\"arial, helvetica\"><b>$langFunctions</b></td></tr>";
    $result = mysql_query("select cat_id, cat_title from catagories order by cat_id");

    $i=0;
    while(list($cat_id, $cat_title) = mysql_fetch_row($result)) {
    $gets = mysql_query("select count(*) as total from forums where cat_id=$cat_id");
	$numbers= mysql_fetch_array($gets);

	    if($i%2==0){
		echo "<tr bgcolor=$color1>";
	}     	// IF
	elseif($i%2==1) {
		echo "<tr bgcolor=$color2>";
	}
    	echo "<td><font size=2 face=\"arial, helvetica\">$cat_id</td>
		<td><font size=2 face=\"arial, helvetica\">$cat_title &nbsp;</td>
		<td><font size=2 face=\"arial, helvetica\">$numbers[total]</td>
		<td><font size=2 face=\"arial, helvetica\">
		<a href=\"forum_admin.php?forumgo=yes&cat_id=$cat_id&ctg=$cat_title\">$langForums</a> |
		<a href=forum_admin.php?forumcatedit=yes&cat_id=$cat_id>$langModify</a> |
		<a href=forum_admin.php?forumcatdel=yes&cat_id=$cat_id&ok=0>$langDelete</a></font size></td></tr>";
		$i++;
    }

    echo "</table></form>
  <font size=2 face=\"arial, helvetica\"><b>$langAddCategory</b><br><br>
    <font size=2>
    <form action=\"forum_admin.php?forumcatadd=yes\" method=post>
    <table border=0 width=600><tr><td><font size=2 face=\"arial, helvetica\">$langCat</td>
    <td><input type=text name=catagories size=50></td></tr>
	<tr><td><input type=hidden name=forumcatadd value=yes>
    <input type=submit value=\"$langAdd\"></form></td><td>&nbsp;</td></tr></table>";
   }
##########################################################################
##########################################################################
}

else {

echo "<font face=\"arial, helvetica\" size=2>$langNotAllowed</font><br>";
}
########################################"
#######################################
?>
</td>
	</tr>
	<tr>
		<td>
			<hr noshade size=1>
<?
end_page();
?>
