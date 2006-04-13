<?
$langFiles = array('admin','gunet');
include '../../include/init.php';
@include "check_admin.inc";

$nameTools = "Λίστα Χρηστών / Ενέργειες";
$navigation[]= array ("url"=>"index.php", "name"=> $langAdmin);
begin_page();

echo "<tr><td>";

$conn = mysql_connect("$mysqlServer", "$mysqlUser", "$mysqlPassword");
if (!mysql_select_db("$mysqlMainDb", $conn)) {
	die("Cannot select database $mysqlMainDb.\n");
}


if (isset($ord)) {
	switch ($ord) {
		case "s":
			$order = "statut"; break;
		case "n":
			$order = "nom"; break;
		case "p":
			$order = "prenom"; break;
		case "u":
			$order = "username"; break;
		default:
			$order = "statut"; break;
	}
} else {
	$order = "statut";
}


// Αν έχει ζητηθεί κάποιο μάθημα με τον κωδικό c, και βρέθηκαν χρήστες,
// εμφανίζεται η σελίδα με τους χρήστες:

$a=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM user"));
$b=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM user where statut='1'"));
$c=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM user where statut='5'"));
$d=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM user where statut='10'"));
echo "<p><i>Υπάρχουν <b>$b[0]</b> Καθηγητές, <b>$c[0]</b> φοιτητές και <b>$d[0]</b> επισκέπτες</i></p>";
echo "<p><i>Σύνολο <b>$a[0]</b> χρήστες</i></p>";

$countUser = $a[0];

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
}       // if numbering

// default status for the list: users 0 to 50
else
{
        $startList=0;
}

// Numerating the items in the list to show: starts at 1 and not 0
$i=$startList+1;

// Do not show navigation buttons if less than 50 users
if ($countUser >= 50)
{

	echo "
                <table width=100% cellpadding=1 cellspacing=1 border=0>
                        <tr>
                                <td valign=bottom align=left width=20%>
                                        <form method=post action=\"$PHP_SELF?numbList=begin\">
                                                <input type=submit value=\"$langBegin<<\" name=\"numbering\">
                                        </form>
                                </td>
                                <td valign=bottom align=middle width=20%>";

        // if beginning of list or complete listing, do not show "previous" button
        if($startList!=0)
        {
		if (isset($_REQUEST['ord'])) {
        	        echo "<form method=post action=\"$PHP_SELF?startList=$startList&numbList=less&ord=$_REQUEST[ord]\">
                	       <input type=submit value=\"$langPreced50<\" name=\"numbering\">
                       		</form>";
		} else {
		       echo "<form method=post action=\"$PHP_SELF?startList=$startList&numbList=less\">
                       <input type=submit value=\"$langPreced50<\" name=\"numbering\">
                       </form>";
		}
        }

	if (isset($_REQUEST['ord'])) {
        	     echo "
                     </td>
                     <td valign=bottom align=middle width=20%>
                     <form method=post action=\"$PHP_SELF?startList=$startList&numbList=all&ord=$_REQUEST[ord]\">
                         <input type=submit value=\"$langAll\" name=numbering>
                     </form>
                     </td>
                     <td valign=bottom align=middle width=20%>";
		} else {
			echo "
                      </td>
                      <td valign=bottom align=middle width=20%>
                      <form method=post action=\"$PHP_SELF?startList=$startList&numbList=all\">
                           <input type=submit value=\"$langAll\" name=numbering>
                      </form>
                      </td>
                      <td valign=bottom align=middle width=20%>";
		}		

// if end of list or complete listing, do not show "next" button
        if(!((($countUser-$startList) <= 50) OR ($endList == $countUser)))
        {
		if (isset($_REQUEST['ord'])) {
                	echo " <form method=post action=\"$PHP_SELF?startList=$startList&numbList=more&ord=$_REQUEST[ord]\">
                                   <input type=submit value=\"$langFollow50>\" name=numbering>
                              </form>";
		} else {
                	echo " <form method=post action=\"$PHP_SELF?startList=$startList&numbList=more\">
                                   <input type=submit value=\"$langFollow50>\" name=numbering>
                              </form>";
		}
        }
	if (isset($_REQUEST['ord'])) {
        	     echo "
                     </td>
                     <td valign=bottom align=right width=20%>
                      <form method=post action=\"$PHP_SELF?numbList=final&ord=$_REQUEST[ord]\">
                           <input type=submit value=\"$langEnd>>\" name=numbering>
                      </form>
                      </td>
                      </tr>
                	</table>"; 
		} else {
        		echo "
                              </td>
                              <td valign=bottom align=right width=20%>
                                        <form method=post action=\"$PHP_SELF?numbList=final\">
                                                <input type=submit value=\"$langEnd>>\" name=numbering>
                                        </form>
                              </td>
                              </tr>
                	</table>"; 
		}	

}       // Show navigation buttons

// Show users

if (isset($numbering) and isset($_REQUEST['startList']) and isset($_REQUEST['numbList']))   {
	echo "<table border=\"1\">\n<tr><th>".
	    "<a href=\"listusers.php?ord=n&startList=$_REQUEST[startList]&numbList=$_REQUEST[numbList]\">Επώνυμο</a></th><th>".
	    "<a href=\"listusers.php?ord=p&startList=$_REQUEST[startList]&numbList=$_REQUEST[numbList]\">Όνομα</a></th><th>".
	    "<a href=\"listusers.php?ord=u&startList=$_REQUEST[startList]&numbList=$_REQUEST[numbList]\">Username</a></th><th>".
	    "Password</th><th>".
	     "<a href=\"listusers.php?ord=s&startList=$_REQUEST[startList]&numbList=$_REQUEST[numbList]\">Email</a></th><th>".
	     "Ιδιότητα</th>".
	     "<th>Ενέργειες</th></tr>";
} else {
	echo "<table border=\"1\">\n<tr><th>".
	     "<a href=\"listusers.php?ord=n\">Επώνυμο</a></th><th>".
			 "<a href=\"listusers.php?ord=p\">Όνομα</a></th><th>".
			 "<a href=\"listusers.php?ord=u\">Username</a></th><th>".
			 "Password</th><th>".
			 "<a href=\"listusers.php?ord=s\">Email</a></th><th>".
			 "Ιδιότητα</th>".
			 "<th>Ενέργειες</th></tr>";
}

$sql = mysql_query("SELECT user_id,nom,prenom,username,password,email,statut FROM user 
			ORDER BY $order LIMIT $startList, $endList");

        for ($j = 0; $j < mysql_num_rows($sql); $j++) {
                $logs = mysql_fetch_array($sql);
                echo("<tr>");
                for ($i = 1; $i < 6; $i++) {
                        echo("<td width='500'>".htmlspecialchars($logs[$i])."</td>");
		}
		 switch ($logs[6]) {
                        case 1:
                                echo "<td>Καθηγητής</td>"; break;
                        case 5:
                                echo "<td>Φοιτητής</td>"; break;
			case 10:
				echo "<td>Επισκέπτης</td>"; break;
                        default:
                               echo "<td>¶λλο ($logs[6])</td>"; break;

			}

		for ($i = 7; $i < mysql_num_fields($sql); $i++) {
			 echo("<td width='500'>".htmlspecialchars($logs[$i])."</td>");

                }
                echo "<td><a href=\"edituser.php?u=$logs[0]\">Επεξεργασία</a></td>\n";
}
            echo("</td>");
        echo("</tr>");

	echo "</table>";
	echo "<p><center><a href=\"index.php\">Επιστροφή</a></p></center>";
?>
</body></html>
