<?

/*===========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2008  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/


/*===========================================================================
	listusers.php
	@last update: 27-06-2006 by Karatzidis Stratos
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Vagelis Pitsioygas <vagpits@uom.gr>
==============================================================================
  @Description: List Users (eclass version)

 	This script displays information about User Info / List.
	The list allows the admin to:
	- edit user data
	- display the course modules list for each user
	- Register/Unregister from a course
	- Statistics per user
	- delete the user
	- Register/Unregister the user from the platform

	@todo: update the link for user statistics
==============================================================================
*/

// BASETHEME, OTHER INCLUDES AND NAMETOOLS
$require_admin = TRUE;
include '../../include/baseTheme.php';
include 'admin.inc.php';
$nameTools = $langVersion;
$tool_content = "";
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
$nameTools = $langListUsersActions;

// initalize the incoming variables
$search = isset($_GET['search'])?$_GET['search']:'';
$c = isset($_GET['c'])?$_GET['c']:(isset($_POST['c'])?$_POST['c']:'');

switch($c)		// get the case for each different listing
{
	case '0': $view = 1;		break;		// normal listing
	case '':	$view = 1;		break;		// normal listing
	case '4': $view = 1; break; // normal listing. Display the inactive accounts
	case 'searchlist': $view = 2;		break;		// search listing (search_user.php)
	default:	$view = 3;	break;		// list per course
}

if($view==2)				// coming from search_user.php(search with criteria)
{
	if((!empty($search)) && ($search="yes"))
	{
		// get the incoming values
		$user_sirname = isset($_POST['user_sirname'])?$_POST['user_sirname']:'';
		$user_firstname = isset($_POST['user_firstname'])?$_POST['user_firstname']:'';
		$user_username = isset($_POST['user_username'])?$_POST['user_username']:'';
		$user_am = isset($_POST['user_am'])?$_POST['user_am']:'';
		$user_type = isset($_POST['user_type'])?$_POST['user_type']:'';
		$user_registered_at_flag = isset($_POST['user_registered_at_flag'])?$_POST['user_registered_at_flag']:'';

	  $date = split("-",  $_POST['date']);
		if (array_key_exists(1, $date)) {
    		$day=$date[0];
		    $month=$date[1];
    		$year=$date[2];
		    $mytime = mktime($hour, $minute, 0, $month, $day, $year);
		 } else {
		    $mytime = mktime($hour, $minute, 0, 0, 0, 0);
		}

		if(!empty($mytime)) {
			$user_registered_at = $mytime;
		} else {
			$user_registered_at = "";
		}
		// end format date/time

		$user_email = isset($_POST['user_email'])?$_POST['user_email']:'';

		// unregister their values from session variables
		session_unregister('user_sirname');
		session_unregister('user_firstname');
		session_unregister('user_username');
		session_unregister('user_am');
		session_unregister('user_type');
		session_unregister('user_registered_at_flag');
		session_unregister('user_registered_at');
		session_unregister('user_email');
	}
	else
	{
		// get their values from session
		$user_sirname = isset($_SESSION['user_sirname'])?$_SESSION['user_sirname']:'';
		$user_firstname = isset($_SESSION['user_firstname'])?$_SESSION['user_firstname']:'';
		$user_username = isset($_SESSION['user_username'])?$_SESSION['user_username']:'';
		$user_am = isset($_SESSION['user_am'])?$_SESSION['user_am']:'';
		$user_type = isset($_SESSION['user_type'])?$_SESSION['user_type']:'';
		$user_registered_at_flag = isset($_SESSION['user_registered_at_flag'])?$_SESSION['user_registered_at_flag']:'';
		$user_registered_at = isset($_SESSION['user_registered_at'])?$_SESSION['user_registered_at']:'';
		$user_email = isset($_SESSION['user_email'])?$_SESSION['user_email']:'';
	}

}
else		// means that we have 'normal' listing or 'users per course' listing
{
	$user_sirname = "";
	$user_firstname = "";
	$user_username = "";
	$user_am = "";
	$user_type = "";
	$user_registered_at_flag = "";
	$user_registered_at = "";
	$user_email = "";
}

/***************
Criteria/Filters
***************/
$criteria = 0;
if(!empty($user_sirname))
{
	$user_sirname_qry = " nom LIKE '".$user_sirname."%'";
	$criteria++;
}
else
{
	$user_sirname_qry = "";
}

if(!empty($user_firstname))
{
	if($criteria!=0)
	{
		$user_firstname_qry = " AND";
	}
	else
	{
		$user_firstname_qry = "";
	}
	$criteria++;
	$user_firstname_qry .= " prenom LIKE '".$user_firstname."%'";
}
else
{
	$user_firstname_qry = "";
}

if(!empty($user_username))
{
	if($criteria!=0)
	{
		$user_username_qry = " AND";
	}
	else
	{
		$user_username_qry = "";
	}
	$criteria++;
	$user_username_qry .= " username LIKE '".$user_username."%'";
}
else
{
	$user_username_qry = "";
}

if(!empty($user_am))
{
	if($criteria!=0)
	{
		$user_am_qry = " AND";
	}
	else
	{
		$user_am_qry = "";
	}
	$criteria++;
	$user_am_qry .= " am='".$user_am."'";
}
else
{
	$user_am_qry = "";
}

if(!empty($user_type))
{
	if($criteria!=0)
	{
		$user_type_qry = " AND";
	}
	else
	{
		$user_type_qry = "";
	}
	$criteria++;
	$user_type_qry .= " statut=".$user_type;
}
else
{
	$user_type_qry = "";
}

if(!empty($user_registered_at_flag))
{
	if($criteria!=0)
	{
		$user_registered_at_qry = " AND";
		$criteria++;
	}
	else
	{
		$user_registered_at_qry = "";
	}
	$user_registered_at_qry .= " registered_at";
	switch($user_registered_at_flag)
	{
		case 1:	$user_registered_at_qry .= " >="; break;
		case 2: $user_registered_at_qry .= " <="; break;
		default: $user_registered_at_qry .= " <="; break;
	}
	if(!empty($user_registered_at))
	{
		$user_registered_at_qry .= $user_registered_at;
	}
	else
	{
		$user_registered_at_qry = "";
	}
}
else
{
	$user_registered_at_qry = "";
}

if(!empty($user_email))
{
	if($criteria!=0)
	{
		$user_email_qry = " AND";
		$criteria++;
	}
	else
	{
		$user_email_qry = "";
	}
	$user_email_qry .= " email LIKE '".$user_email."%'";
}
else
{
	$user_email_qry = "";
}

if($c==4)
{
	if($criteria!=0)
	{
		$users_active_qry = " AND";
		$criteria++;
	}
	else
	{
		$users_active_qry = "";
	}
	$users_active_qry .= " expires_at<".time()." AND user_id<>1";
}
else
{
	$users_active_qry = "";
}
// end filter/criteria

$ord = isset($_GET['ord'])?$_GET['ord']:'';
if(!empty($ord))
{
	switch ($ord)
  {
		case "s":		$order = "statut,prenom,nom"; break;
		case "n":		$order = "nom,prenom,statut"; break;
		case "p":		$order = "prenom,nom,statut"; break;
		case "u":		$order = "username,statut,prenom"; break;
		default:		$order = "statut,prenom,nom"; break;
    }
} else {
	$order = "statut";
}

$caption ="";

if($view==3) {
	$qry = "SELECT a.user_id,a.nom, a.prenom, a.username, a.email, b.statut
		FROM user AS a LEFT JOIN cours_user AS b ON a.user_id = b.user_id
		WHERE b.code_cours='".$c."'";
} else {
	// Count users, with or without criteria/filters
	$qry = "SELECT user_id,nom,prenom,username,email,statut FROM user";
	if((!empty($user_sirname_qry)) || (!empty($user_firstname_qry)) || (!empty($user_username_qry)) || (!empty($user_am_qry)) || (!empty($user_type_qry)) || (!empty($user_registered_at_qry)) || (!empty($user_email_qry)) || (!empty($users_active_qry)) )
	{
		$qry .= " WHERE".$user_sirname_qry.$user_firstname_qry.$user_username_qry.$user_am_qry.$user_type_qry.$user_registered_at_qry.$user_email_qry.$users_active_qry;
	}
}
$sql = mysql_query($qry);
if($sql)
{
	$countUser = mysql_num_rows($sql);
	$teachers = 0;
	$students = 0;
	$visitors = 0;
	$other = 0;
	while($numrows=mysql_fetch_array($sql,MYSQL_ASSOC))
	{
		switch ($numrows['statut'])
		{
			case 1:		$teachers++; break;
 			case 5:		$students++; break;
			case 10:	$visitors++; break;
 			default:	$other++; break;
		}
	}


	if($countUser>0)
	{
		$caption = "";
		$caption .= "<i>$langThereAre: <b>$teachers</b> $langTeachers, <b>$students</b> $langStudents και <b>$visitors</b> $langVisitors</i><br />";
		$caption .= "<i>$langTotal: <b>$countUser</b> $langUsers</i><br />";

		if($countUser>0)
		{
			if($c==4)
			{
				$caption .= "&nbsp;$langAsInactive<br />";
				$caption .= "<a href=\"updatetheinactive.php?activate=1\">".$langAddSixMonths."</a><br />";
			}
			else
			{
				$caption .= " ";
			}
		}
		else
		{
			$caption .= "";
		}


		// DEFINE SETTINGS FOR THE 5 NAVIGATION BUTTONS INTO THE USERS LIST: begin, less, all, more and end
		$endList=50;
		if(isset ($numbering) && $numbering)
		{
			if($numbList=="more")
	    {
	     	$startList=$startList+50;
	    }
	    elseif($numbList=="less")
	    {
	    	$startList=abs($startList-50);
			}
	    elseif($numbList=="all")
	    {
	    	$startList=0;
	     	$endList=$countUser;
			}
	    elseif($numbList=="begin")
	    {
	     	$startList=0;
	    }
	    elseif($numbList=="final")
	    {
				$startList=((int)($countUser / 50)*50);
			}
		} // if numbering
		else // default status for the list: users 0 to 50
		{
	   	$startList=0;
		}

		// Numerating the items in the list to show: starts at 1 and not 0
		$i=$startList+1;

		if ($countUser >= 50)	// Do not show navigation buttons if less than 50 users
		{
			$tool_content .= "
  <table class=\"FormData\" width=\"99%\" align=\"left\">
  <thead>
  <tr>
    <td class=\"left\" width=\"20%\">
      <form method=post action=\"$_SERVER[PHP_SELF]?numbList=begin\">
        <input type=submit value=\"$langBegin<<\" name=\"numbering\">
      </form>
    </td>
    <td class=\"center\" width=\"20%\">";
        if($startList!=0)		// if beginning of list or complete listing, do not show "previous" button
	    {
				if (isset($_REQUEST['ord']))
				{
	       	$tool_content .= "
      <form method=post action=\"$_SERVER[PHP_SELF]?startList=$startList&numbList=less&ord=$_REQUEST[ord]\">
        <input type=submit value=\"$langPreced50<\" name=\"numbering\">
      </form>";
				}
				else
				{
			  	$tool_content .= "
      <form method=post action=\"$_SERVER[PHP_SELF]?startList=$startList&numbList=less\">
        <input type=submit value=\"$langPreced50<\" name=\"numbering\">
      </form>";
				}
			}

			if (isset($_REQUEST['ord']))  {
	    	$tool_content .= "
    </td>
    <td class=\"center\" width=\"20%\">
      <form method=post action=\"$_SERVER[PHP_SELF]?startList=$startList&numbList=all&ord=$_REQUEST[ord]\">
        <input type=submit value=\"$langAll\" name=numbering>
      </form>
    </td>
    <td class=\"center\" width=\"20%\">";
			} else 	{
				$tool_content .= "
    </td>
    <td class=\"center\" width=\"20%\">
      <form method=post action=\"$PHP_SELF?startList=$startList&numbList=all\">
        <input type=submit value=\"$langAll\" name=numbering>
      </form>
    </td>
    <td class=\"center\" width=\"20%\">";
			}
			if(!((($countUser-$startList) <= 50) OR ($endList == $countUser)))	// if end of list or complete listing, do not show "next" button
			{
				if (isset($_REQUEST['ord']))
				{
	      	$tool_content .= "
      <form method=post action=\"$_SERVER[PHP_SELF]?startList=$startList&numbList=more&ord=$_REQUEST[ord]\">
        <input type=submit value=\"$langFollow50>\" name=numbering>
      </form>";
				}
				else
				{
	      	$tool_content .= "
      <form method=post action=\"$_SERVER[PHP_SELF]?startList=$startList&numbList=more\">
        <input type=submit value=\"$langFollow50>\" name=numbering>
      </form>";
				}
			}
			if (isset($_REQUEST['ord']))
			{
	    	$tool_content .= "
    </td>
    <td class=\"right\" width=\"20%\">
	  <form method=post action=\"$_SERVER[PHP_SELF]?numbList=final&ord=$_REQUEST[ord]\">
        <input type=submit value=\"$langEnd>>\" name=numbering>
	  </form>
	</td>
  </tr>
  </thead>
  </table>";
			}
			else
			{
	    	$tool_content .= "
    </td>
    <td class=\"right\" width=\"20%\">
      <form method=post action=\"$_SERVER[PHP_SELF]?numbList=final\">
        <input type=submit value=\"$langEnd>>\" name=numbering>
       </form>
     </td>
  </tr>
  </thead>
  </table>
  ";
			}
		}       // Show navigation buttons if ($countUser >= 50)

		if($view==3) {
			$qry = "SELECT a.user_id,a.nom, a.prenom, a.username, a.email, b.statut
			FROM user AS a LEFT JOIN cours_user AS b ON a.user_id = b.user_id
			WHERE b.code_cours='".$c."'";
		} else {
			$qry = "SELECT user_id,nom,prenom,username,email,statut FROM user";
			if((!empty($user_sirname_qry)) || (!empty($user_firstname_qry))
				|| (!empty($user_username_qry)) || (!empty($user_am_qry))
				|| (!empty($user_type_qry)) || (!empty($user_registered_at_qry))
				|| (!empty($user_email_qry)) || (!empty($users_active_qry)) )
			{
				$qry .= " WHERE".$user_sirname_qry.$user_firstname_qry.$user_username_qry.$user_am_qry.$user_type_qry.$user_registered_at_qry.$user_email_qry.$users_active_qry;
			}
		}

		$qry .= "	ORDER BY $order LIMIT $startList, $endList";
		mysql_free_result($sql);
		$sql = mysql_query($qry);

		/****************************************
		Show users - Format the table for display
		*****************************************/
		if (isset($numbering) and isset($_REQUEST['startList']) and isset($_REQUEST['numbList']))
		{
			$tool_content .= "
  <table class=\"FormData\" width=\"99%\" align=\"left\">
  <tbody>
  <tr>
    <td class=\"odd\" colspan=\"9\"><div align=\"right\">".$caption."</div></td>
  </tr>
  <tr>
    <th scope=\"col\" colspan='2'><a href=\"listusers.php?ord=n&startList=$_REQUEST[startList]&numbList=$_REQUEST[numbList]\">$langSurname</a></th>
    <th><a href=\"listusers.php?ord=p&startList=$_REQUEST[startList]&numbList=$_REQUEST[numbList]\">$langName</a></th>
    <th><a href=\"listusers.php?ord=u&startList=$_REQUEST[startList]&numbList=$_REQUEST[numbList]\">$langUsername</a></th>
    <th scope=\"col\">$langEmail</th>
    <th scope=\"col\"><a href=\"listusers.php?ord=s&startList=$_REQUEST[startList]&numbList=$_REQUEST[numbList]\">$langProperty</a></th>
    <th scope=\"col\">$langActions</th>
    <th scope=\"col\">$langDelete $langUser</th>
    <th scope=\"col\">$langStats</th>
  </tr>
  </thead>
  <tbody>";
		}
		else
		{
			$tool_content .= "
  <table class=\"FormData\" width=\"99%\" align=\"left\">
  <tbody>
  <tr>
    <td class=\"odd\" colspan='9'><div align=\"right\"><small>".$caption."</small></div></td>
  </tr>
  <tr>
    <th scope=\"col\" colspan='2'><a href=\"listusers.php?ord=n\">$langSurname</a></th>
    <th><a href=\"listusers.php?ord=p\">$langName</a></th>
    <th><a href=\"listusers.php?ord=u\">$langUsername</a></th>
    <th scope=\"col\">$langEmail</th>
    <th scope=\"col\"><a href=\"listusers.php?ord=s\">$langProperty</a></th>
    <th scope=\"col\" colspan='3'>$langActions</th>
  </tr>";
		}

        $k =0;
		for ($j = 0; $j < mysql_num_rows($sql); $j++) {
			while($logs = mysql_fetch_array($sql,MYSQL_ASSOC)) {
				if ($k%2==0) {
		              $tool_content .= "
  <tr>";
	            } else {
		                $tool_content .= "
  <tr class=\"odd\">";
	            }
				$tool_content .= "
    <td width=\"1\"><img style='border:0px;' src='${urlServer}/template/classic/img/bullet_bw.gif' title='bullet'></td>
    <td>".htmlspecialchars($logs['nom'])."</td>
    <td>".htmlspecialchars($logs['prenom'])."</td>
    <td>".htmlspecialchars($logs['username'])."</td>
    <td>".htmlspecialchars($logs['email'])."</td>
    <td align='center'>";
				switch ($logs['statut'])
				{
					case 1:		$tool_content .= "<img src='../../template/classic/img/teacher.gif' title='$langTeacher'></img>";break;
	   			case 5:		$tool_content .= "<img src='../../template/classic/img/student.gif' title='$langStudent'></img>";break;
					case 10:	$tool_content .= "<img src='../../template/classic/img/guest.gif' title='$langVisitor'></img>";break;
	   			default:	$tool_content .= "$langOther ($logs[6])";break;
				}
				$tool_content .= "</td>
      <td><a href=\"edituser.php?u=".$logs['user_id']."\"><img src='../../template/classic/img/edit.gif' title='$langEdit' border='0'></a></td>
      <td><a href=\"unreguser.php?u=".$logs['user_id']."\"><img src='../../images/delete.gif' title='$langDelete' border='0'></img></a></td>
      <td align='center'><a href=\"userstats.php?u=".$logs['user_id']."\"><img src='../../template/classic/img/platform_stats.gif' border='0' title='$langStat'></img></a></td>\n";
				$tool_content .= "
  </tr>";

			}
 $k++;
		}
		// end format / display
		$tool_content .= "
  </tbody>
  </table>";
	}
	else
	{
		$tool_content .= $langNoSuchUsers;
	}
}
else
{
	$tool_content .= "<br />$langNoUserList<br />";
}
$tool_content .= "<p align=\"right\"><a href=\"index.php\">$langBack</a></p>";

// 3: display administrator menu
draw($tool_content,3,'admin');
?>
