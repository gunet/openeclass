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
	listusers.php
	@last update: 31-05-2006 by Karatzidis Stratos
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

 	The admin can view: 
 	- the total number of teachers,students and visitors
 	- the list of platform users
 
	The list can be produced by 3 different ways:
	- Normal Listing
	- Search users with filters (search_user.php)
	- Course List / users per course (listcours.php)
 
 
 	@Comments: The script is organised in four sections.

 	1) Execute the command called by the user
           Note (March 2004) some editing functions (renaming, commenting)
           are moved to a separate page, edit_document.php. This is also
           where xml and other stuff should be added.
 
	@todo: update the link for user statistics
==============================================================================
*/

/*****************************************************************************
LANGFILES, BASETHEME, OTHER INCLUDES AND NAMETOOLS
******************************************************************************/
$langFiles = array('admin','about');
include '../../include/baseTheme.php';
include 'admin.inc.php';			
@include "check_admin.inc";		// check if user is administrator
$nameTools = $langVersion;
$tool_content = "";					// Initialise $tool_content
$nameTools = "Λίστα Χρηστών / Ενέργειες";		// Define $nameTools


// initalize the incoming variables
$search = isset($_GET['search'])?$_GET['search']:'';
$c = isset($_GET['c'])?$_GET['c']:(isset($_POST['c'])?$_POST['c']:'');


switch($c)		// get the case for each different listing
{
	case '0': $view = 1;		break;		// normal listing
	case '':	$view = 1;		break;		// normal listing
	case 'searchlist': $view = 2;		break;		// search listing (search_user.php)
	default:	$view = 3;	break;		// list per course
}

if($view==2)				// coming from search_user.php(search with criteria)
{
	if((!empty($search)) && ($search="yes"))
	{
		// get the incoming values
		$user_sirname = isset($_POST['user_sirname'])?$_POST['user_sirname']:'';
		$user_am = isset($_POST['user_am'])?$_POST['user_am']:'';
		$user_type = isset($_POST['user_type'])?$_POST['user_type']:'';
		$user_registered_at_flag = isset($_POST['user_registered_at_flag'])?$_POST['user_registered_at_flag']:'';
		// format the date/time filter
		$datetime = new DATETIME();
		$datetime->set_datetime_byglobal("HTTP_POST_VARS");
		$mytime = $datetime->get_timestamp_entered();
		if(!empty($mytime))
		{
			$user_registered_at = $mytime;
		}
		else
		{
			$user_registered_at = "";
		}
		// end format date/time
		$user_email = isset($_POST['user_email'])?$_POST['user_email']:'';
	
		// unregister their values from session variables
		session_unregister('user_sirname');
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
		case 1:	$user_registered_at_qry .= " <="; break;
		case 2: $user_registered_at_qry .= " >="; break;
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
// end filter/criteria


$ord = isset($_GET['ord'])?$_GET['ord']:'';
if(!empty($ord)) 
{
	switch ($ord) 
  {
		case "s":		$order = "statut"; break;
		case "n":		$order = "nom"; break;
		case "p":		$order = "prenom"; break;
		case "u":		$order = "username"; break;
		default:		$order = "statut"; break;
    }
} 
else 
{
	$order = "statut";
}

$caption ="";
	
	
if($view==3)
{
	$qry = "SELECT a.user_id,a.nom, a.prenom, a.username, a.email, b.statut 
		FROM user AS a LEFT JOIN cours_user AS b ON a.user_id = b.user_id
		WHERE b.code_cours='".$c."'";
}
else
{
	// Count users, with or without criteria/filters
	$qry = "SELECT user_id,nom,prenom,username,email,statut FROM user";
	if((!empty($user_sirname_qry)) || (!empty($user_am_qry)) || (!empty($user_type_qry)) || (!empty($user_registered_at_qry)) || (!empty($user_email_qry)) )
	{
		$qry .= " WHERE".$user_sirname_qry.$user_am_qry.$user_type_qry.$user_registered_at_qry.$user_email_qry;
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
	$caption = "";
	$caption .= "<p><i>Υπάρχουν <b>$teachers</b> Καθηγητές, <b>$students</b> φοιτητές και <b>$visitors</b> επισκέπτες</i></p>";
	$caption .= "<p><i>Σύνολο <b>$countUser</b> χρήστες</i></p>";
		
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
                <table width=99%>
                        <tr>
                      	  <td valign=bottom align=left width=20%>
                              <form method=post action=\"$PHP_SELF?numbList=begin\"><input type=submit value=\"$langBegin<<\" name=\"numbering\">
                              </form>
                          </td>
                          <td valign=bottom align=middle width=20%>";
    if($startList!=0)		// if beginning of list or complete listing, do not show "previous" button
    {
			if (isset($_REQUEST['ord'])) 
			{
       	$tool_content .= "<form method=post action=\"$PHP_SELF?startList=$startList&numbList=less&ord=$_REQUEST[ord]\">
                	       <input type=submit value=\"$langPreced50<\" name=\"numbering\">
                       		</form>";
			} 
			else 
			{
		  	$tool_content .= "<form method=post action=\"$PHP_SELF?startList=$startList&numbList=less\">
                       <input type=submit value=\"$langPreced50<\" name=\"numbering\">
                       </form>";
			}
		}

		if (isset($_REQUEST['ord'])) 
		{
    	$tool_content .= "
                     </td>
                     <td valign=bottom align=middle width=20%>
                     <form method=post action=\"$PHP_SELF?startList=$startList&numbList=all&ord=$_REQUEST[ord]\">
                         <input type=submit value=\"$langAll\" name=numbering>
                     </form>
                     </td>
                     <td valign=bottom align=middle width=20%>";
		} 
		else 
		{
			$tool_content .= "
                      </td>
                      <td valign=bottom align=middle width=20%>
                      <form method=post action=\"$PHP_SELF?startList=$startList&numbList=all\">
                           <input type=submit value=\"$langAll\" name=numbering>
                      </form>
                      </td>
                      <td valign=bottom align=middle width=20%>";
		}		

		if(!((($countUser-$startList) <= 50) OR ($endList == $countUser)))		// if end of list or complete listing, do not show "next" button
		{
			if (isset($_REQUEST['ord'])) 
			{
      	$tool_content .= " <form method=post action=\"$PHP_SELF?startList=$startList&numbList=more&ord=$_REQUEST[ord]\">
                                   <input type=submit value=\"$langFollow50>\" name=numbering>
                              </form>";
			} 
			else 
			{
      	$tool_content .= " <form method=post action=\"$PHP_SELF?startList=$startList&numbList=more\">
                                   <input type=submit value=\"$langFollow50>\" name=numbering>
                              </form>";
			}
		}
		if (isset($_REQUEST['ord'])) 
		{
    	$tool_content .= "
                     </td>
                     <td valign=bottom align=right width=20%>
                      <form method=post action=\"$PHP_SELF?numbList=final&ord=$_REQUEST[ord]\">
                           <input type=submit value=\"$langEnd>>\" name=numbering>
                      </form>
                      </td>
                      </tr>
                	</table>"; 
		} 
		else 
		{
    	$tool_content .= "</td>
                          <td valign=bottom align=right width=20%>
                               <form method=post action=\"$PHP_SELF?numbList=final\">
                                <input type=submit value=\"$langEnd>>\" name=numbering>
                              </form>
                              </td>
                              </tr>
                	</table>"; 
		}	
	}       // Show navigation buttons if ($countUser >= 50)

	
	if($view==3)
	{
		$qry = "SELECT a.user_id,a.nom, a.prenom, a.username, a.email, b.statut 
		FROM user AS a LEFT JOIN cours_user AS b ON a.user_id = b.user_id
		WHERE b.code_cours='".$c."'";
	}
	else
	{
		$qry = "SELECT user_id,nom,prenom,username,email,statut FROM user";
		if((!empty($user_sirname_qry)) || (!empty($user_am_qry)) || (!empty($user_type_qry)) || (!empty($user_registered_at_qry)) || (!empty($user_email_qry)) )
		{
			$qry .= " WHERE".$user_sirname_qry.$user_am_qry.$user_type_qry.$user_registered_at_qry.$user_email_qry;
		}		
	}
	
	$qry .= "	ORDER BY $order LIMIT $startList, $endList";
	mysql_free_result($sql);
	$sql = mysql_query($qry);
	
	
	/****************************************
	Show users - Format the table for dispaly
	*****************************************/	
	if (isset($numbering) and isset($_REQUEST['startList']) and isset($_REQUEST['numbList']))   
	{
		$tool_content .= "<table width=\"99%\"><caption>".$caption."</caption><thead><tr>".
					"<th scope=\"col\"><a href=\"listusers.php?ord=n&startList=$_REQUEST[startList]&numbList=$_REQUEST[numbList]\">Επώνυμο</a></th>".
					"<th><a href=\"listusers.php?ord=p&startList=$_REQUEST[startList]&numbList=$_REQUEST[numbList]\">Όνομα</a></th>".
					"<th><a href=\"listusers.php?ord=u&startList=$_REQUEST[startList]&numbList=$_REQUEST[numbList]\">Username</a></th>".
				 "<th scope=\"col\"><a href=\"listusers.php?ord=s&startList=$_REQUEST[startList]&numbList=$_REQUEST[numbList]\">Email</a></th>".
				 "<th scope=\"col\">Ιδιότητα</th>".
				 "<th scope=\"col\">Ενέργειες</th>".
				 "<th scope=\"col\">Διαγραφή Χρήστη</th>".
				 "<th scope=\"col\">Στατιστικά Χρήστη</th>".
				 "</tr></thead><tbody>";
	} 
	else 
	{
		$tool_content .= "<table width=\"99%\"><caption>".$caption."</caption><thead><tr>".
					"<th scope=\"col\"><a href=\"listusers.php?ord=n\">Επώνυμο</a></th><th>".
				 "<a href=\"listusers.php?ord=p\">Όνομα</a></th><th>".
				 "<a href=\"listusers.php?ord=u\">Username</a></th>".
				 "<th scope=\"col\"><a href=\"listusers.php?ord=s\">Email</a></th>".
				 "<th scope=\"col\">Ιδιότητα</th>".
				 "<th scope=\"col\">Ενέργειες</th>".
				 "<th scope=\"col\">Διαγραφή Χρήστη</th>".
				 "<th scope=\"col\">Στατιστικά Χρήστη</th>".
				 "</tr></thead><tbody>";
	}
		
	for ($j = 0; $j < mysql_num_rows($sql); $j++) 
	{
		while($logs = mysql_fetch_array($sql,MYSQL_ASSOC))
  	{
			$tool_content .= ("<tr>");
			$tool_content .= "<td>".htmlspecialchars($logs['nom'])."</td>".
				"<td>".htmlspecialchars($logs['prenom'])."</td>".
				"<td>".htmlspecialchars($logs['username'])."</td>".
				"<td>".htmlspecialchars($logs['email'])."</td>";
			switch ($logs['statut']) 
			{
				case 1:		$tool_content .= "<td>Καθηγητής</td>";break;
   			case 5:		$tool_content .= "<td>Φοιτητής</td>";break;
				case 10:	$tool_content .= "<td>Επισκέπτης</td>";break;
   			default:	$tool_content .= "<td>Άλλο ($logs[6])</td>";break;
			}
			$tool_content .= "<td><a href=\"edituser.php?u=".$logs['user_id']."\">Επεξεργασία</a></td>
				<td><a href=\"unreguser.php?u=".$logs['user_id']."\">Διαγραφή</a></td>
				<td><a href=\"edituser.php?u=".$logs['user_id']."\">Στατιστικά</a></td>\n";
			$tool_content .= "</tr>";
		}
	}
	// end format / dispaly
	$tool_content .= "</tbody></table>";
		
}
else
{
	$tool_content .= "<br />NO LISTING RESULTS<br />";
}

$tool_content .= "<p><center><a href=\"index.php\">Επιστροφή</a></p></center>";

// 3: display administrator menu
draw($tool_content,3);

?>