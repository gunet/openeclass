<?  session_start(); ?>
<?php
/***************************************************************************
                            bb_memberlist.php  -  description
                             -------------------
    begin                : Sat June 17 2000
    copyright            : (C) 2001 The phpBB Group
    email                : support@phpbb.com

    $Id$

 ***************************************************************************/

/***************************************************************************
 *                                         				                                
 *   This program is free software; you can redistribute it and/or modify  	
 *   it under the terms of the GNU General Public License as published by  
 *   the Free Software Foundation; either version 2 of the License, or	    	
 *   (at your option) any later version.
 *
 ***************************************************************************/

/*
*  This Page Created By:  Yokhannan
*  Email:  support@4cm.com
*  Created On: Saturday, July 22, 2000
*
*  Edited: Thursday, October 19, 2000
*    Added a better ICQ method.
*    Changed all the Font Settings to use Variables
*    Made some minor PHP-HTML changes
*  
* Oct 27, 2000 
*    Added pagination
*       - James
*/
include('extention.inc');
include('functions.'.$phpEx);
include('config.'.$phpEx);
require('auth.'.$phpEx);
$pagetitle = "Members List";
$pagetype = "other";
include('page_header_list.' . $phpEx);

if (!isset($sortby))
{
	$sortby = '';	
}

switch ($sortby) {
	case '':
		$sortby = "user_id ASC";
		$sortlink = "";
	break;
	case 'user':
		$sortby = "username ASC";
		$sortlink = "user";
	break;
	case 'from':
		$sortby = "user_from ASC";
		$sortlink = "from";
	break;
	case 'posts':
		$sortby = "user_posts DESC";
		$sortlink = "posts";
	break;
}


if(!$start) $start = 0;

$sql = "SELECT * FROM users WHERE user_id != -1 AND user_level != -1 ORDER BY $sortby LIMIT $start, $topics_per_page";
if(!$result = mysql_query($sql, $db))
	error_die("Couldn't get userlist from database");

?>
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="<?php echo $tablewidth?>">
    <tr> 
      <td align=right>
<?php
  $sql = "SELECT count(*) AS total FROM users WHERE user_level != -1";
if(!$r = mysql_query($sql, $db))
  die("Error could not contact the database!</TABLE></TABLE>");
list($all_topics) = mysql_fetch_array($r);

// subtract one from user count because of the anonymous entry..
--$all_topics;

$count = 1;
$next = $start + $topics_per_page;
if($all_topics > $topics_per_page) 
{
   if ($next < $all_topics)
   {
   	echo "<font size=-1>\n<a href=\"bb_memberlist.$phpEx?start=$next&sortby=$sortlink\">$l_nextpage</a> | ";
   }
   for($x = 0; $x < $all_topics; $x++) 
   {
      if(0 == ($x % $topics_per_page)) 
      {
	 		if($x == $start)
	   		echo "$count\n";
	 		else
	   		echo "<a href=\"bb_memberlist.$phpEx?&start=$x&sortby=$sortlink\">$count</a>\n";
	 		
	 		$count++;
	 		if(!($count % 10)) 
	 			echo "<BR>";
      }
   }
}
$next = 0;
$ranking = $start;

?>
</td>
</tr>
</table>
  <table width="<?php echo $tablewidth?>" border="0" cellspacing="2" cellpadding="0" bordercolor="<?php echo $table_bgcolor?>" bgcolor="<?php echo $table_bgcolor?>" align="center">
          <tr nowrap> 
            <td>

<?PHP

$row = mysql_fetch_array($result);

if (!$row) {
	// No administrator??
	error_die("No members? Not even an administrator?");
} else {
?>
	<table width="100%" border="0" cellspacing="1" cellpadding="0">
	<TR>
		<td bgcolor="<?php echo $color2?>">&nbsp;</td>
		<td bgcolor="<?php echo $color2?>" width="25%" height="25" nowrap><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">&nbsp;<B><a href="<?php echo $PHP_SELF?>?sortby=user&start=<?php echo $start?>"><?php echo $l_username?></a></B></font></TD>
		<td bgcolor="<?php echo $color2?>" width="30%" height="25"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">&nbsp;<B><a href="<?php echo $PHP_SELF?>?sortby=from&start=<?php echo $start?>"><?php echo $l_location?></a></B></font></TD>
		<td bgcolor="<?php echo $color2?>" width="8%" height="25" nowrap align="center"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><B><?php echo $l_joined?></B></font></TD>
		<td bgcolor="<?php echo $color2?>" width="8%" height="25" nowrap align="center"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><a href="<?php echo $PHP_SELF?>?sortby=posts&start=<?php echo $start?>"><?php echo $l_posts?></a></b></font></td>
		<td bgcolor="<?php echo $color2?>" width="8%" height="25" nowrap align="center"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><B><?php echo $l_email?></B></font></TD>
		<td bgcolor="<?php echo $color2?>" width="6%" height="25" nowrap align="center"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><B><?php echo $l_url?></B></font></TD>
		<td bgcolor="<?php echo $color2?>" width="6%" height="25" nowrap align="center"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><B><?php echo $l_icq?></B></font></TD>
		<td bgcolor="<?php echo $color2?>" width="6%" height="25" nowrap align="center"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><B><?php echo $l_aim?></B></font></TD>
		<td bgcolor="<?php echo $color2?>" width="5%" height="25" nowrap align="center"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><B><?php echo $l_yim?></B></font></TD>
		<td bgcolor="<?php echo $color2?>" width="6%" height="25" nowrap align="center"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><B><?php echo $l_msn?></B></font></TD>
	</TR>
<?php

	do {
		if ($row[user_viewemail]) {
			$email = "<a href=\"mailto:$row[user_email]\"><img src=\"$email_image\" width=\"33\" height=\"17\" border=\"0\" alt=\"Email $row[username]\"></a>";
		} else {
			$email = "&nbsp;";
		}
		if ($row[user_website]) {
			$www = "<a href=\"$row[user_website]\"><img src=\"$www_image\" width=\"34\" height=\"17\" border=\"0\" alt=\"Visit $row[username]'s Web Site\"></a>";
		} else {
			$www = "&nbsp;";
		}
		if ($row[user_icq]) {
			$icq = "<a href=\"http://wwp.icq.com/scripts/search.dll?to=$row[user_icq]\"><img src=\"$icq_add_image\" width=\"32\" height=\"17\" border=\"0\" alt=\"Add $row[username]\"></a>";
		} else {
			$icq = "&nbsp;";
		}
		if ($row[user_aim]) {
			$aim = "<a href=\"aim:goim?screenname=$row[user_aim]&message=Hi+$row[user_aim].+Are+you+there?\"><img src=\"$images_aim\" width=\"30\" height=\"17\" border=\"0\" alt=\"AIM $row[user_aim]\"></a></TD>";
		} else {
			$aim = "&nbsp;";
		}
		if ($row[user_yim]) {
			$yim = "<a href=\"http://edit.yahoo.com/config/send_webmesg?.target=$row[user_yim]&.src=pg\"><img src=\"$images_yim\" width=\"16\" height=\"16\" border=\"0\" alt=\"YIM $row[user_yim]\"></a>";
		} else {
			$yim = "&nbsp;";
		}
		if ($row[user_msnm]) {
			$msnm = "<a href=\"$url_phpbb/bb_profile.$phpEx?mode=view&user=$row[user_id]\"><img src=\"$images_msnm\" width=\"16\" height=\"16\" border=\"0\" alt=\"MSNM $row[user_msnm]\"></a>";
		} else {
			$msnm = "&nbsp;";
		}
		if ($row[user_regdate]) 
			$regdate = $row[user_regdate];
		else
			$regdate = "&nbsp;";
?>
	<TR>
		<td bgcolor="<?php echo $color2?>" nowrap align="center"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">&nbsp;<?php echo ++$ranking?>&nbsp;</font></TD>
		<td bgcolor="<?php echo $color2?>" width="25%" height="30" nowrap><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">&nbsp;<a href="<?php echo $url_phpbb?>/bb_profile.<?php echo $phpEx?>?mode=view&user=<?php echo $row[user_id]?>"><?php echo $row[username]?></a></font></TD>
		<td bgcolor="<?php echo $color1?>" width="30%" height="30"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">&nbsp;<?php echo stripslashes($row[user_from])?></font></TD>
		<td bgcolor="<?php echo $color2?>" width="8%" height="30" nowrap align="center"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"> <?php echo $regdate?> </font></td>
		<td bgcolor="<?php echo $color1?>" width="8%" height="30" nowrap align="center"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"> <?php echo $row[user_posts]?> </font></td>
		<td bgcolor="<?php echo $color2?>" width="8%" height="30" nowrap align="center"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"> <?php echo $email?> </font></TD>
		<td bgcolor="<?php echo $color1?>" width="6%" height="30" nowrap align="center"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"> <?php echo $www?> </font></TD>
		<td bgcolor="<?php echo $color2?>" width="6%" height="30" nowrap align="center"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"> <?php echo $icq?> </font></TD>
		<td bgcolor="<?php echo $color1?>" width="6%" height="30" nowrap align="center"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"> <?php echo $aim?> </font></TD>
		<td bgcolor="<?php echo $color2?>" width="5%" height="30" nowrap align="center"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"> <?php echo $yim?> </font></TD>
		<td bgcolor="<?php echo $color1?>" width="6%" height="30" nowrap align="center"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"> <?php echo $msnm?> </font></TD>
	</TR>
<?php
	} while ($row = mysql_fetch_array($result));
	echo "</table></table> \n";
}
?>
  <TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="<?php echo $tablewidth?>">                     
      <tr>                                                                                                                            
        <td align="left"> 
<?php  

$count = 1;
$next = $start + $topics_per_page;
if($all_topics > $topics_per_page) 
{
   if ($next < $all_topics)
   {
   	echo "<font size=-1>\n<a href=\"bb_memberlist.$phpEx?start=$next&sortby=$sortlink\">$l_nextpage</a> | ";
   }
   for($x = 0; $x < $all_topics; $x++) 
   {
      if(0 == ($x % $topics_per_page)) 
      {
	 		if($x == $start)
	   		echo "$count\n";
	 		else
	   		echo "<a href=\"bb_memberlist.$phpEx?&start=$x&sortby=$sortlink\">$count</a>\n";
	 		
	 		$count++;
	 		if(!($count % 10)) 
	 			echo "<BR>";
      }
   }
}


echo "<BR>\n";
?>
           </td>
            </tr>
          </table>
        </td>                                   
    </tr>
  </table>
  
  
<?php
include('page_tail.'.$phpEx);
?>
