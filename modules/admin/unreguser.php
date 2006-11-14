<?
/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        Α full copyright notice can be read in "/info/copyright.txt".
        
       	Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
        	    Yannis Exidaridis <jexi@noc.uoa.gr> 
      		    Alexandros Diamantidis <adia@noc.uoa.gr> 

        For a full list of contributors, see "credits.txt".  
     
        This program is a free software under the terms of the GNU 
        (General Public License) as published by the Free Software 
        Foundation. See the GNU License for more details. 
        The full license can be read in "license.txt".
     
       	Contact address: GUnet Asynchronous Teleteaching Group, 
        Network Operations Center, University of Athens, 
        Panepistimiopolis Ilissia, 15784, Athens, Greece
        eMail: eclassadmin@gunet.gr
==============================================================================*/

/**===========================================================================
	unreguser.php
	@last update: 27-06-2006 by Karatzidis Stratos
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Vagelis Pitsioygas <vagpits@uom.gr>
==============================================================================        
        @Description: Delete user from platform and from courses (eclass version)

 	This script allows the admin to :
 	- permanently delete a user account
 	- delete a user from participating into a course
 	
==============================================================================
*/

// LANGFILES, BASETHEME, OTHER INCLUDES AND NAMETOOLS
$langFiles = array('gunet','admin','registration');
$require_admin = TRUE;
include '../../include/baseTheme.php';
$nameTools = $langUnregUser;
$navigation[]= array ("url"=>"index.php", "name"=> $langAdmin);

$tool_content = "";

// get the incoming values and initialize them
$u = isset($_GET['u'])?$_GET['u']:'';		// user ID
$doit = isset($_GET['doit'])?$_GET['doit']:'';
$c = isset($_GET['c'])?$_GET['c']:'';		// course ID

$u_account = (!empty($u))?uid_to_username($u):'';
$u_realname = (!empty($u))?uid_to_name($u):'';
$u_statut = get_uid_statut($u);
$t = 0;

if(empty($doit))
{
	//$tool_content .= "<h4>$langConfirmDelete</h4><p>$langConfirmDeleteQuestion1 <em>$un</em>";
	$tool_content .= "<h4>$langConfirmDelete</h4><p>$langConfirmDeleteQuestion1 <em>$u_realname ($u_account)</em>";
	if(!empty($c)) 
	{
		$tool_content .= " $langConfirmDeleteQuestion2 <em>".$c."</em>";
	}
	$tool_content .= "$langQueryMark</p>
		<ul>
		<li>Ναι: <a href=\"unreguser.php?u=$u&c=$c&doit=yes\">$langDelete!</a><br>&nbsp;</li>
		<li>Όχι: <a href=\"edituser.php?u=$u\">Επεξεργασία Χρήστη $u_account</a>&nbsp;&nbsp;&nbsp;<a href=\"index.php\">$back</a></li>
		</ul>";	
} 
else 
{
	if($doit == "yes")
	{
		$conn = mysql_connect($mysqlServer, $mysqlUser, $mysqlPassword);
		if (!mysql_select_db($mysqlMainDb, $conn))
	                die("Cannot select database");
		if(empty($c)) 
		{
			if ($u == 1) 
			{
				$tool_content .= "$langError. $langCannotDeleteAdmin";
			}
			else
			{
				// now check if the user has registered courses...
				$q1 = mysql_query("SELECT * FROM cours_user WHERE user_id=".$u);
				$total = mysql_num_rows($q1);
				if((!empty($total)) && ($total>0))		// means the the user has courses, so it is not allowed to delete
				{
					$tool_content .= "You are not allowed to delete the user <em>$u_realname ($u_account)</em><br>";
					$v = 0;	$s = 0;
					for ($p = 0; $p < mysql_num_rows($q1); $p++) 
				  {
						$l1 = mysql_fetch_array($q1);
						$tutor = $l1[5];
						if($tutor=='1')
						{
							$v++;
						}
						else
						{
							$s++;
						}
					}
					
					if($v>0)
					{
						if($s>0)
						{
							//display list
							$tool_content .= "Please unregister the user from the following list of courses:<br><br>";
							$sql = mysql_query("SELECT a.code, a.intitule, b.statut, a.cours_id, b.tutor 
							FROM cours AS a LEFT JOIN cours_user AS b ON a.code = b.code_cours
							WHERE b.user_id = '$u' AND b.tutor=0 ORDER BY b.statut, a.faculte");
							// αν ο χρήστης συμμετέχει σε μαθήματα τότε παρουσίασε τη λίστα 
							if (mysql_num_rows($sql) > 0) 
							{
								$tool_content .= "<h4>$langStudentParticipation</h4>\n".
								"<table border=\"1\">\n<tr><th>$langLessonCode</th><th>$langLessonName</th>".
								"<th>$langProperty</th><th>$langActions</th></tr>";
					  		for ($j = 0; $j < mysql_num_rows($sql); $j++) 
				  			{
									$logs = mysql_fetch_array($sql);
									$tool_content .= "<tr><td>".htmlspecialchars($logs[0])."</td><td>".
									htmlspecialchars($logs[1])."</td><td align=\"center\">";
									switch ($logs[4]) 
									{
										case '1':
											$tool_content .= $langTeacher;
											$tool_content .= "</td><td align=\"center\">---</td></tr>\n";
											break;
										case '0':
											$tool_content .= $langStudent;
											$tool_content .= "</td><td align=\"center\"><a href=\"unreguser.php?u=$u&c=$logs[0]\">$langDelete</a></td></tr>\n";
											break;
										default:
											$tool_content .= $langVisitor;
											$tool_content .= "</td><td align=\"center\"><a href=\"unreguser.php?u=$u&c=$logs[0]\">$langDelete</a></td></tr>\n";
											break;
									}
								}
				  			$tool_content .= "</table>\n";
							}
						}
						else
						{
							$tool_content .= "You are not allowed to delete this user. This user is a teacher in this list of courses:<br>";
							$sql = mysql_query("SELECT a.code, a.intitule, b.statut, a.cours_id 
							FROM cours AS a LEFT JOIN cours_user AS b ON a.code = b.code_cours
							WHERE b.user_id = '$u' AND b.statut=1 ORDER BY b.statut, a.faculte");
							// αν ο χρήστης συμμετέχει σε μαθήματα τότε παρουσίασε τη λίστα 
							if (mysql_num_rows($sql) > 0) 
							{
								$tool_content .= "<h4>$langStudentParticipation</h4>\n".
								"<table border=\"1\">\n<tr><th>$langLessonCode</th><th>$langLessonName</th>".
								"<th>$langProperty</th><th>$langActions</th></tr>";
					  		for ($j = 0; $j < mysql_num_rows($sql); $j++) 
				  			{
									$logs = mysql_fetch_array($sql);
									$tool_content .= "<tr><td>".htmlspecialchars($logs[0])."</td><td>".
									htmlspecialchars($logs[1])."</td><td align=\"center\">";
									$tool_content .= $langTeacher;
									$tool_content .= "</td><td align=\"center\">---</td></tr>\n";
								}
							}
					  	$tool_content .= "</table>\n";
							
							
							
							
						}
					}
					else
					{
						// display list
						$tool_content .= "Please unregister the user from the following list of courses:<br><br>";
						$sql = mysql_query("SELECT a.code, a.intitule, b.statut, a.cours_id 
						FROM cours AS a LEFT JOIN cours_user AS b ON a.code = b.code_cours
						WHERE b.user_id = '$u' AND b.statut=5 ORDER BY b.statut, a.faculte");
						// αν ο χρήστης συμμετέχει σε μαθήματα τότε παρουσίασε τη λίστα 
						if (mysql_num_rows($sql) > 0) 
						{
						$tool_content .= "<h4>$langStudentParticipation</h4>\n".
						"<table border=\"1\">\n<tr><th>$langLessonCode</th><th>$langLessonName</th>".
						"<th>$langProperty</th><th>$langActions</th></tr>";
					  for ($j = 0; $j < mysql_num_rows($sql); $j++) 
				  	{
							$logs = mysql_fetch_array($sql);
							$tool_content .= "<tr><td>".htmlspecialchars($logs[0])."</td><td>".
							htmlspecialchars($logs[1])."</td><td align=\"center\">";
							switch ($logs[2]) 
							{
								case 1:
									$tool_content .= $langTeacher;
									$tool_content .= "</td><td align=\"center\">---</td></tr>\n";
									break;
								case 5:
									$tool_content .= $langStudent;
									$tool_content .= "</td><td align=\"center\"><a href=\"unreguser.php?u=$u&c=$logs[0]\">$langDelete</a></td></tr>\n";
									break;
								default:
									$tool_content .= $langVisitor;
									$tool_content .= "</td><td align=\"center\"><a href=\"unreguser.php?u=$u&c=$logs[0]\">$langDelete</a></td></tr>\n";
									break;
							}
						}
				  	$tool_content .= "</table>\n";
						}
						
					}
					
					
					
					
					
					$t = 1;
				}
				else
				{
					$sql = mysql_query("DELETE from user WHERE user_id = '$u'");
					if (mysql_affected_rows($conn) > 0)
					{
						$t = 2;
					}
					else
					{
						$t = 3;
					}
				}
			
				
				switch($t)
				{
					case '1':	$tool_content .= "";	$m = 1; break;
					case '2': $tool_content .= "<p>$langUserWithId $u $langWasDeleted.</p>\n";	$m = 0;	break;
					case '3': $tool_content .= "$langErrorDelete";	$m = 1;	break;
					default: $m = 0;	break;
				}
			
			
				if($u!=1)
				{
					mysql_query("DELETE from admin WHERE idUser = '$u'");
				}
				if (mysql_affected_rows($conn) > 0) 
				{
					$tool_content .= "<p>$langUserWithId $u $langWasAdmin.</p>\n";
				}
				
				// delete guest user from cours_user 
				if($u_statut == '10')
				{
					$sql = mysql_query("DELETE from cours_user WHERE user_id = '$u'");
				}
			}
			
		} 
		elseif((!empty($c)) && (!empty($u)))
		{
			$sql = mysql_query("DELETE from cours_user WHERE user_id = '$u' and code_cours='$c'");
			if (mysql_affected_rows($conn) > 0)  
			{
				$tool_content .= "<p>$langUserWithId $u $langWasCourseDeleted $c.</p>\n";
				$m = 1;
			}
		}
		else
		{
				$tool_content .= "$langErrorDelete";
		}
		$tool_content .= "<br>&nbsp;";
		if((isset($m)) && (!empty($m)))
		{
			$tool_content .= "<br><a href=\"edituser.php?u=$u\">Επεξεργασία Χρήστη $u_account</a>&nbsp;&nbsp;&nbsp;";
		}
		$tool_content .= "<a href=\"./index.php\">$langBackAdmin</a>.<br />\n";
	}
}	

function get_uid_statut($u)
{
	global $mysqlMainDb;

	if ($r = mysql_fetch_row(db_query("SELECT statut FROM user WHERE user_id = '$u'",	$mysqlMainDb))) 
	{
		return $r[0];
	} 
	else 
	{
		return FALSE;
	}
}

draw($tool_content,3,'admin');
?>
