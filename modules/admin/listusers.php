<?
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
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
$search = isset($_GET['search'])? $_GET['search']: '';
$c = isset($_GET['c'])? $_GET['c']: (isset($_POST['c'])? $_POST['c']: '');

switch($c)	// get the case for each different listing
{
	case '': $view = 1; break; // normal listing
	case 'inactive': $view = 1; break; // normal listing. Display the inactive accounts
	case 'searchlist': $view = 2;	break; // search listing (search_user.php)
	default: $c = intval($c); $view = 3; break; // list per course
}

if($view == 2)	// coming from search_user.php(search with criteria)
{
	if((!empty($search)) && ($search="yes"))
	{
		// get the incoming values
		$user_surname = isset($_POST['user_surname'])?$_POST['user_surname']:'';
		$user_firstname = isset($_POST['user_firstname'])?$_POST['user_firstname']:'';
		$user_username = isset($_POST['user_username'])?$_POST['user_username']:'';
		$user_am = isset($_POST['user_am'])?$_POST['user_am']:'';
		$user_type = isset($_POST['user_type'])?$_POST['user_type']:'';
		$user_email = isset($_POST['user_email'])?$_POST['user_email']:'';
		$user_registered_at_flag = isset($_POST['user_registered_at_flag'])?$_POST['user_registered_at_flag']:'';

	  	$date = explode("-",  $_POST['date']);
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
	}
}

// Display Actions Toolbar
  $tool_content .= "
      <div id='operations_container'>
        <ul id='opslist'>
	<li><a href='search_user.php'>$langSearchUser</a></li>
	<li><a href='listusers.php?c=inactive'>".$langInactiveUsers."</a></li>
        </ul>
      </div>";

/***************
Criteria/Filters
***************/

$criteria = 0;

// surname search
if(!empty($user_surname))
{
	$user_surname_qry = " nom LIKE '".$user_surname."%'";
	$criteria++;
}
else
{
	$user_surname_qry = "";
}

// first name search
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

// username search
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

//user am search
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

// user type search
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


// email search
if(!empty($user_email))
{
	if($criteria!=0)
	{
		$user_email_qry = " AND";
	}
	else
	{
		$user_email_qry = "";
	}
	$criteria++;
	$user_email_qry .= " email LIKE '".$user_email."%'";
}
else
{
	$user_email_qry = "";
}

// join the above with registered at search
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


if($c=='inactive')
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
$startList = isset($_GET['startList'])?$_GET['startList']:'';
$numbList = isset($_GET['numbList'])?$_GET['numbList']:'';

if(!empty($ord)) {
	switch ($ord) {
		case "s": $order = "statut,prenom,nom"; break;
		case "n": $order = "nom,prenom,statut"; break;
		case "p": $order = "prenom,nom,statut"; break;
		case "u": $order = "username,statut,prenom"; break;
		default: $order = "statut,prenom,nom"; break;
	}
} else {
	$order = "statut";
}

$caption ="";

if($view == 3) { // users per course
	$qry = "SELECT a.user_id, a.nom, a.prenom, a.username, a.email, b.statut
		FROM user AS a LEFT JOIN cours_user AS b ON a.user_id = b.user_id
		WHERE b.cours_id = $c";
} else {
	// Count users, with or without criteria/filters
	$qry = "SELECT user_id,nom,prenom,username,email,statut FROM user";
	if((!empty($user_surname_qry)) || (!empty($user_firstname_qry)) || (!empty($user_username_qry)) 
		|| (!empty($user_am_qry)) || (!empty($user_type_qry)) || (!empty($user_registered_at_qry)) 
		|| (!empty($user_email_qry)) || (!empty($users_active_qry)))
	{
		$qry .= " WHERE".$user_surname_qry.$user_firstname_qry.$user_username_qry.$user_am_qry.$user_type_qry.$user_email_qry.$user_registered_at_qry.$users_active_qry;
	}
}

$sql = db_query($qry);

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
			case 1:	$teachers++; break;
 			case 5:	$students++; break;
			case 10: $visitors++; break;
 			default: $other++; break;
		}
	}

	if($countUser>0)
	{
		$caption = "";
		$caption .= "<i>$langThereAre: <b>$teachers</b> $langTeachers, <b>$students</b> $langStudents 
			$langAnd <b>$visitors</b> $langVisitors</i><br />";
		$caption .= "<i>$langTotal: <b>$countUser</b> $langUsers</i><br />";

		if($countUser>0)
		{
			if($c=='inactive')
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
		$endList=15;
		if(isset ($numbering) && $numbering)
		{
			if($numbList=="more")
	    		{
	     			$startList=$startList+$endList;
	    		}
	    		elseif($numbList=="less") {
	    			$startList=abs($startList-$endList);
			}
	    		elseif($numbList=="all") {
	    			$startList=0;
	     			$endList=$countUser;
			} elseif($numbList=="begin") {
	     			$startList=0;
	    		}
	    		elseif($numbList=="final") {
				$startList=((int)($countUser / $endList)*$endList);
			}
		} // if numbering
		else // default status for the list: users 0 to $endList
		{
	   		$startList=0;
		}

		// Numerating the items in the list to show: starts at 1 and not 0
		$i=$startList+1;
		if ($countUser >= $endList) { // Do not show navigation buttons if less than 30 users	
			$tool_content .= "<table class=\"FormData\" width=\"99%\" align=\"left\">
  			<thead><tr>
    			<td class=\"left\" width=\"20%\">
      			<form method=post action=\"$_SERVER[PHP_SELF]?numbList=begin\">
      			".keep_var()."
        		<input type=submit value=\"$langBegin<<\" name=\"numbering\">
      			</form>
    			</td>
    			<td class=\"center\" width=\"20%\">";
        		if($startList!=0) // if beginning of list or complete listing, do not show "previous" button
	    		{
				if (isset($_REQUEST['ord'])) {
	       				$tool_content .= "<form method=post action=\"$_SERVER[PHP_SELF]?startList=$startList&numbList=less&ord=$_REQUEST[ord]\">
	       				".keep_var()."
					<input type=submit value=\"$langPreced50<\" name=\"numbering\">
					</form>";
				} else {
			  		$tool_content .= "
      					<form method=post action=\"$_SERVER[PHP_SELF]?startList=$startList&numbList=less\">
      					".keep_var()."
        				<input type=submit value=\"$langPreced50<\" name=\"numbering\">
      					</form>";
				}
			}
			if (isset($_REQUEST['ord']))  {
	    			$tool_content .= "</td><td class=\"center\" width=\"20%\">
      				<form method=post action=\"$_SERVER[PHP_SELF]?startList=$startList&numbList=all&ord=$_REQUEST[ord]\">
      				".keep_var()."
        			<input type=submit value=\"$langAll\" name=numbering>
      				</form>
    				</td><td class=\"center\" width=\"20%\">";
			} else 	{
				$tool_content .= "</td><td class=\"center\" width=\"20%\">
      				<form method=post action=\"$_SERVER[PHP_SELF]?startList=$startList&numbList=all\">
      				".keep_var()."
        			<input type=submit value=\"$langAll\" name=numbering>
      				</form></td>
    				<td class=\"center\" width=\"20%\">";
			}
			if(!((($countUser-$startList) <= $endList) OR ($endList == $countUser))) // if end of list or complete listing, do not show "next" button
			{
				if (isset($_REQUEST['ord'])) { 
					$tool_content .= "
      					<form method=post action=\"$_SERVER[PHP_SELF]?startList=$startList&numbList=more&ord=$_REQUEST[ord]\">
      					".keep_var()."
        				<input type=submit value=\"$langFollow50>\" name=numbering>
      					</form>";
				} else {
	      				$tool_content .= "
      					<form method=post action=\"$_SERVER[PHP_SELF]?startList=$startList&numbList=more\">
      					".keep_var()."
        				<input type=submit value=\"$langFollow50>\" name=numbering>
      					</form>";
				}
			}
			if (isset($_REQUEST['ord'])) {
	    			$tool_content .= "</td><td class=\"right\" width=\"20%\">
	  			<form method=post action=\"$_SERVER[PHP_SELF]?numbList=final&ord=$_REQUEST[ord]\">
	  			".keep_var()."
        			<input type=submit value=\"$langEnd>>\" name=numbering>
	  			</form>
				</td></tr></thead></table>";
			} else {
	    			$tool_content .= "</td><td class=\"right\" width=\"20%\">
      				<form method=post action=\"$_SERVER[PHP_SELF]?numbList=final\">
      				".keep_var()."
        			<input type=submit value=\"$langEnd>>\" name=numbering>
       				</form>
     				</td></tr></thead></table>";
			}
		}       // Show navigation buttons if ($countUser >= 30)

		if($view == 3) {
			$qry = "SELECT a.user_id,a.nom, a.prenom, a.username, a.email, b.statut
			FROM user AS a LEFT JOIN cours_user AS b ON a.user_id = b.user_id
			WHERE b.cours_id=$c";
		} else {
			$qry = "SELECT user_id,nom,prenom,username,email,statut FROM user";
			if((!empty($user_surname_qry)) || (!empty($user_firstname_qry))
				|| (!empty($user_username_qry)) || (!empty($user_am_qry))
				|| (!empty($user_type_qry)) || (!empty($user_registered_at_qry))
				|| (!empty($user_email_qry)) || (!empty($users_active_qry)) )
			{
				$qry .= " WHERE".$user_surname_qry.$user_firstname_qry.$user_username_qry.$user_am_qry.$user_type_qry.$user_email_qry.$user_registered_at_qry.$users_active_qry;
			}
		}

		$qry .= " ORDER BY $order LIMIT $startList, $endList";
		mysql_free_result($sql);
		$sql = db_query($qry);
	
		/****************************************
		Show users - Format the table for display
		*****************************************/

		@$str = "user_surname=$_REQUEST[user_surname]&user_firstname=$_REQUEST[user_firstname]&user_username=$_REQUEST[user_username]&user_am=$_REQUEST[user_am]&user_email=$_REQUEST[user_email]&user_type=$_REQUEST[user_type]&user_registered_at_flag=$_REQUEST[user_registered_at_flag]";
		
		$tool_content .= "<table class=\"FormData\" width=\"99%\" align=\"left\">
  			<tbody><tr><td class=\"odd\" colspan=\"9\"><div align=\"right\">".$caption."</div></td>
  			</tr>";
		if (isset($numbering) and isset($_REQUEST['startList']) and isset($_REQUEST['numbList'])) {
			$string = '';
			if (isset($_REQUEST['c'])) {
				$string = "&c=$c";
			}
			$tool_content .= "<tr><th scope=\"col\" colspan='2'>
			<a href='$_SERVER[PHP_SELF]?ord=n$string&$str&startList=$_REQUEST[startList]&numbList=$_REQUEST[numbList]'>$langSurname</a>
			</th><th>
			<a href=\"$_SERVER[PHP_SELF]?ord=p$string&$str&startList=$_REQUEST[startList]&numbList=$_REQUEST[numbList]\">$langName</a>
			</th><th>
			<a href=\"$_SERVER[PHP_SELF]?ord=u$string&$str&startList=$_REQUEST[startList]&numbList=$_REQUEST[numbList]\">$langUsername</a></th><th scope=\"col\">$langEmail</th>
    			<th scope=\"col\">
			<a href=\"$_SERVER[PHP_SELF]?ord=s$string&$str&startList=$_REQUEST[startList]&numbList=$_REQUEST[numbList]\">$langProperty</a></th><th scope=\"col\">$langActions</th>
    			<th scope=\"col\">$langDelete $langUser</th>
    			<th scope=\"col\">$langStats</th>
  			</tr></thead><tbody>";
		}
		else
		{
			$tool_content .= "<th scope='col' colspan='2'>
				<a href='$_SERVER[PHP_SELF]?ord=n&$str'>$langSurname</a></th>
    			<th><a href='$_SERVER[PHP_SELF]?ord=p&$str'>$langName</a></th>
    			<th><a href='$_SERVER[PHP_SELF]?ord=u&$str'>$langUsername</a></th>
    			<th scope='col'>$langEmail</th>
    			<th scope='col'>
			<a href='$_SERVER[PHP_SELF]?ord=s&$str'>$langProperty</a></th>
    			<th scope='col' colspan='3'>$langActions</th>
  			</tr>";
		}

        	$k =0;
		for ($j = 0; $j < mysql_num_rows($sql); $j++) {
			while($logs = mysql_fetch_array($sql,MYSQL_ASSOC)) {
				if ($k%2==0) {
		              		$tool_content .= "<tr>";
	            		} else {
		                	$tool_content .= "<tr class=\"odd\">";
	            		}
				$tool_content .= "<td width=\"1\">
				<img style='border:0px;' src='${urlServer}/template/classic/img/arrow_grey.gif' title='bullet'></td>
    				<td>".htmlspecialchars($logs['nom'])."</td>
    				<td>".htmlspecialchars($logs['prenom'])."</td>
    				<td>".htmlspecialchars($logs['username'])."</td>
    				<td>".htmlspecialchars($logs['email'])."</td>
    				<td align='center'>";
				switch ($logs['statut'])
				{
					case 1:	$tool_content .= "<img src='../../template/classic/img/teacher.gif' title='$langTeacher'></img>";break;
	   				case 5:	$tool_content .= "<img src='../../template/classic/img/student.gif' title='$langStudent'></img>";break;
					case 10: $tool_content .= "<img src='../../template/classic/img/guest.gif' title='$langVisitor'></img>";break;
	   				default: $tool_content .= "$langOther ($logs[6])";break;
				}
				$tool_content .= "</td><td><a href=\"edituser.php?u=".$logs['user_id']."\">
				<img src='../../template/classic/img/edit.gif' title='$langEdit' border='0'></a></td>
      				<td><a href=\"unreguser.php?u=".$logs['user_id']."\">
				<img src='../../images/delete.gif' title='$langDelete' border='0'></img></a></td>
      				<td align='center'>
				<a href=\"userstats.php?u=".$logs['user_id']."\">
				<img src='../../template/classic/img/platform_stats.gif' border='0' title='$langStat'></img></a></td>\n";
				$tool_content .= "</tr>";
			}
 			$k++;
		}
		// end format / display
		$tool_content .= "</tbody></table>";
	}
	else
	{
		$tool_content .= "<p class=\"caution_small\">$langNoSuchUsers</p>";
	}
}
else
{
	$tool_content .= "<br />$langNoUserList<br />";
}
$tool_content .= "<p align=\"right\"><a href=\"index.php\">$langBack</a></p>";


//-------------------------------------
// function for keeping post variables
//-------------------------------------
function keep_var() {

	$retstring = '';
	if (isset($_REQUEST['c']) and $_REQUEST['c'] != 'searchlist' and $_REQUEST['c'] != 'inactive') {
			$c = $_REQUEST['c'];
			$retstring .= "<input type = 'hidden' name='c' value='$c'>";
	} else  {
		if (isset($_REQUEST['user_surname'])) {
			$user_surname = $_REQUEST['user_surname'];
			$retstring .= "<input type = 'hidden' name='user_surname' value='$user_surname'>";
		} 
		if (isset($_REQUEST['user_firstname'])) {
			$user_firstname = $_REQUEST['user_firstname'];
			$retstring .= "<input type='hidden' name='user_firstname' value='$user_firstname'>";
		}
		if (isset($_REQUEST['user_username'])) {
			$user_username = $_REQUEST['user_username'];
			$retstring .= "<input type='hidden' name='user_username' value = '$user_username'>";
		}
		if (isset($_REQUEST['user_am'])) {
			$user_am = $_REQUEST['user_am']; 
			$retstring .= "<input type='hidden' name='user_am' value = '$user_am'>";
		}
		if (isset($_REQUEST['user_type'])) {
			$user_type = $_REQUEST['user_type'];
			$retstring .= "<input type='hidden' name='user_type' value='$user_type'>";
		}
		if (isset($_REQUEST['user_email'])) {
			$user_email = $_REQUEST['user_email'];
			$retstring .= "<input type='hidden' name='user_email' value='$user_email'>";
		}
		if (isset($_REQUEST['user_registered_at_flag'])) {
			$user_registered_at_flag = $_REQUEST['user_registered_at_flag'];
			$retstring .= "<input type='hidden' name='user_registered_at_flag' value='$user_registered_at_flag'>";
		}
	}
	return $retstring;
}


// 3: display administrator menu
draw($tool_content, 3, 'admin');
?>
