<?php
/***************************************************************************
  	                        page_tail.php  -  description
 	                        -------------------    
	begin                : Sat June 17 2000    
	copyright            : (C) 2001 The phpBB Group
	email                : support@phpbb.com
 
    $Id$
 
***************************************************************************/

/*************************************************************************** *                                         				                                 
 *   This program is free software; you can redistribute it and/or modify  	 
 *   it under the terms of the GNU General Public License as published by   
 *   the Free Software Foundation; either version 2 of the License, or	    	 
 *   (at your option) any later version. * 
 ***************************************************************************/
if($user_logged_in && $userdata[user_level] == 4) {
	;
}
$tool_content .= "
<FONT FACE=\"$FontFace\" SIZE=\"$FontSize3\" COLOR=\"$textcolor\">
<CENTER><BR>
Copyright &copy; 2000 - 2001 <a href=\"http://www.phpbb.com/about.php\" target=\"_blank\">The phpBB Group</a>
</CENTER>
</font><BR>
";

showfooter($db);

$tool_content .= "</FONT>";

?>
