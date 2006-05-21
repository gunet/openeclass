<?PHP
//logged_in_content.php

$tool_content .= "<table width=\"99%\"><thead>";
	$result2 = mysql_query("SELECT cours.code k, cours.fake_code c, cours.intitule i, cours.titulaires t, cours_user.statut s
		FROM cours, cours_user WHERE cours.code=cours_user.code_cours AND cours_user.user_id='".$uid."'
		AND (cours_user.statut='5' OR cours_user.statut='10')");
        if (mysql_num_rows($result2) > 0) {
		$tool_content .=  '<tr><th>'.$langMyCoursesUser.'</th></tr>';
		
		$tool_content .= "</thead><tbody>";
		$i=0; 
		// SHOW COURSES
		while ($mycours = mysql_fetch_array($result2)) {
			$dbname = $mycours["k"];
			$status[$dbname] = $mycours["s"];
			if ($i%2==0) $tool_content .=  '<tr>';
			elseif($i%2==1) $tool_content .= '<tr class="odd">';
			$tool_content .= '<td>
			<a href="courses/'.$mycours['k'].'/">'.$mycours['i'].'</a>
			<br>'.$mycours['t'].'<br>'.$mycours['c'].'
			</td>
			</tr>';
			$i++; 
		}	// while 
	} // end of if
	
	$tool_content .= "</tbody></table><br>";
	
	$tool_content .= "<table width=\"99%\"><thead>";
// second case check in which courses are registered as a professeror
	$result2 = mysql_query("SELECT cours.code k, cours.fake_code c, cours.intitule i, cours.titulaires t, cours_user.statut s
        	FROM cours, cours_user WHERE cours.code=cours_user.code_cours 
		AND cours_user.user_id='".$uid."' AND cours_user.statut='1'");
	if (mysql_num_rows($result2) > 0) {
	        $tool_content .= '<tr><th>'.$langMyCoursesProf.'</th></tr>';
	        $tool_content .= "</thead><tbody>";
        	$i=0;
        	while ($mycours = mysql_fetch_array($result2)) {
                	$dbname = $mycours["k"];
                	$status[$dbname] = $mycours["s"];
                	if ($i%2==0) $tool_content .= '<tr>';
                	elseif($i%2==1) $tool_content .= '<tr class=\"odd\">';
                        $tool_content .= '<td>
                        <a href="'.$urlServer."courses/".$mycours['k'].'/">'.$mycours['i'].'</a>
                        <br>'.$mycours['t'].'<br>'.$mycours['c'].'
                        </td>
                        </tr>';
                	$i++;
        	}       // while
	} // if
	$tool_content .= '</tbody></table>'; 
	session_register('status');

// // check for new announces
//                if (check_new_announce())
//                    $tool_content .= "<font size=\"1\" face=\"arial, helvetica\" color=\"blue\"><img src='./images/nea.gif' border=0 align=center alt = '(".$langNewAnnounce.")'></font>";
//$tool_content .= <<<tCont7
//		</a></td></tr>
//		<tr bgcolor="#E6E6E6"><td><font size="2" face="arial, helvetica">
//		<a href="modules/profile/profile.php">
//		$langModifyProfile</a>
//		</font></td></tr>
//tCont7;
//	}
?>