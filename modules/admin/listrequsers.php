<?
/*
      +----------------------------------------------------------------------+
      | GUnet eClass 1.7                                                    |
      | Asychronous Teleteaching Platform                                    |
      +----------------------------------------------------------------------+
      | Copyright (c) 2003-2007  GUnet                                       |
      +----------------------------------------------------------------------+
      |                                                                      |
      | GUnet eClass 1.7 is an open platform distributed in the hope that   |
      | it will be useful (without any warranty), under the terms of the     |
      | GNU License (General Public License) as published by the Free        |
      | Software Foundation. The full license can be read in "license.txt".  |
      |                                                                      |
      | Main Developers Group: Costas Tsibanis <k.tsibanis@noc.uoa.gr>       |
      |                        Yannis Exidaridis <jexi@noc.uoa.gr>           |
      |                        Alexandros Diamantidis <adia@noc.uoa.gr>      |
      |                        Tilemachos Raptis <traptis@noc.uoa.gr>        |
      |                                                                      |
      | For a full list of contributors, see "credits.txt".                  |
      |                                                                      |
      +----------------------------------------------------------------------+
      | Contact address: Asynchronous Teleteaching Group (eclass@gunet.gr),  |
      |                  Network Operations Center, University of Athens,    |
      |                  Panepistimiopolis Ilissia, 15784, Athens, Greece    |
      +----------------------------------------------------------------------+
*/
$langFiles = array('gunet','registration','admin');
$require_admin = TRUE;
include '../../include/baseTheme.php';
include('../../include/sendMail.inc.php');

$nameTools= "Ανοικτές Αιτήσεις Φοιτητών";
$navigation[]= array ("url"=>"index.php", "name"=> $langAdmin);
$local_style = " th {font-size:12px; font-family:Verdana, Arial, Helvetica;  } ";
$sendmail =0;

// Initialise $tool_content
$tool_content = "";

$local_head = '
<script type="text/javascript">
function confirmation ()
{
   if (confirm("'.$langDelConf.'")) {
                return true;
   } else {
          return false;
                }
}
</script>';

$tool_content .= "<table width=100% border='0' height=316 cellspacing='0' align=center cellpadding='0'>\n";
$tool_content . "<tr>\n";
$tool_content . "<td valign=top>\n";

$tool_content . "<table height=300 width='96%' align='center' class='admin'><tr>
<td valign=top><br>";

if (isset($close) && $close == 1) {
	$sql = db_query("UPDATE prof_request set status='2', date_closed=NOW() WHERE rid='$id'");

	$tool_content . "<br><br><center>Η αίτηση του φοιτητή έκλεισε !</center>";
} elseif (isset($close) && $close == 2) {
	if (!empty($comment)) {
		if (db_query("UPDATE prof_request set status = '2',
					    date_closed = NOW(),
					    comment = '".mysql_escape_string($comment)."'
					    WHERE rid = '$id'")) {
			if ($sendmail == 1) {
        $emailsubject = "Απόρριψη αίτησης εγγραφής στην Πλατφόρμα Ασύγχρονης Τηλεκπαίδευσης";
				$emailbody = "
Η αίτησή σας για εγγραφή στην πλατφόρμα eClass απορρίφθηκε.
Σχόλια:

> $comment

$langManager $siteName
$administratorName $administratorSurname
Τηλ. $telephone
$langEmail : $emailAdministrator

";
				send_mail($siteName, $emailAdministrator, "$prof_name $prof_surname",
					$prof_email, $emailsubject, $emailbody, $charset);
			}
                        $tool_content .= "<div class=alert1>Η αίτηση απορρίφθηκε!</div><br>";
                        if ($sendmail == 1) $tool_content .= "<div class=kk align=center>Στάλθηκε ενημερωτικό μήνυμα στη διεύθυνση <b>$prof_email</b></div>.";
                        $tool_content .= "<br><table width=80% align=center style=\"border: 1px solid $table_border;\"><tr><td class=color1><h4>Σχόλια:</h4><pre>$comment</pre></td></tr></table>\n";
		}
	} else {
		$r = db_query("SELECT comment, profname, profsurname, profemail, proftmima, date_open, profcomm
					     FROM prof_request WHERE rid = '$id'");
		$d = mysql_fetch_assoc($r);

$tool_content .= "<table width=85% align=center><tr><td>
      <form action='listrequsers.php' method='post'>
        <table border='0' width='100%' cellspacing='0'>
        <tr>
       <td>&nbsp;</td>
        </tr>
        <tr>
       <td class=td_label2 style='border : 1px solid $table_border'>Πρόκειται να απορρίψετε την αίτηση Φοιτητή <b>'$d[profname] $d[profsurname] &lt;$d[profemail]&gt;'</b> με στοιχεία:</td>
        </tr>
        <tr>
           <td>&nbsp;</td>
        </tr>
        <tr>
        <td>";
        
        $tool_content .= "<table align=center width=50% border=\"0\" cellspacing=1 cellpading=1 style=\"border: 1px solid $table_border;\">
           <tr>
             <td class=color1 style=\"border: 1px solid $table_border;\"><b>Επώνυμο</b>:</td>
             <td class=stat2 style=\"border: 1px solid $table_border;\">$d[profsurname]</td>
           </tr>
           <tr>
             <td class=color1 style=\"border: 1px solid $table_border;\"><b>Όνομα</b>:</td>
             <td class=stat2 style=\"border: 1px solid $table_border;\">$d[profname]</td>
           </tr>
           <tr>
             <td class=color1 style=\"border: 1px solid $table_border;\"><b>E-mail</b>:</td>
             <td class=stat2 style=\"border: 1px solid $table_border;\">$d[profemail]</td>
           </tr>
           <tr>
             <td class=color1 style=\"border: 1px solid $table_border;\"><b>Τμήμα</b>:</td>
             <td class=stat2 style=\"border: 1px solid $table_border;\">$d[proftmima]</td>
           </tr>
           <tr>
             <td class=color1 style=\"border: 1px solid $table_border;\"><b>Ημ/νία Αίτησης</b>:</td>
             <td class=stat2 style=\"border: 1px solid $table_border;\">$d[date_open]</td>
           </tr>
           <tr>
             <td class=color1 style=\"border: 1px solid $table_border;\"><b>Τηλεφ.</b>:</td>
             <td class=stat2 style=\"border: 1px solid $table_border;\">$d[profcomm]</td>
           </tr>
           </table>
        ";
        
         $tool_content .= "</td></tr>
        <tr><td>&nbsp;</td>
        </tr>
        <tr>
         <td class=color1 style='border : 1px solid $table_border'>Σχόλια:</td>
        </tr>
        <tr>
           <td><input type='hidden' name='id' value='$id'>
               <input type='hidden' name='close' value='2'>
               <input type='hidden' name='prof_name' value='$d[profname]'>
               <input type='hidden' name='prof_surname' value='$d[profsurname]'>
               <textarea name='comment' rows='5' cols='80' class=auth_input_admin>$d[comment]</textarea>
           </td>
        </tr>
        <tr>
           <td>&nbsp;</td>
        </tr>
        <tr>
           <td class=color1 style='border : 1px solid $table_border'>
                <table width=100%><tr><td class=kk>
                <input type='checkbox' name='sendmail' value='1' checked='yes'>
                &nbsp;Αποστολή μηνύματος στο χρήστη, στη διεύθυνση:</td><td align=left>
                <input type='text' name='prof_email' class=auth_input_admin value='$d[profemail] '></td>
								<td align=right>
                <input type='submit' name='submit' value='Απόρριψη'></td></tr></table>
            </td>
        </tr>
        <tr>
           <td class=kk align=right><small>(στο μήνυμα θα αναφέρεται και το παραπάνω σχόλιο)</small></td>
        </tr>
        <tr>
           <td>&nbsp;</td>
        </tr>
        </table>
      </FIELDSET>
      </form>
      </td></tr></table>";
	}
}

else {

      $tool_content .= "<table align=center width=98% border='0' bgcolor=white cellspacing=0 cellpadding=0 style='border: 1px solid gold;'><tr><td>
      <table align=center width=100% border='0' cellspacing=2 cellpadding=1>
      <tr>
         <td class=td_label2 style='border: 1px solid $table_border' width=15%><small>Όνομα<br>Επώνυμο</small></td>
         <td class=td_label2 style='border: 1px solid $table_border' width=10%><small>Username</small></td>
         <td class=td_label2 style='border: 1px solid $table_border' width=20%><small>E-mail</small></td>
         <td class=td_label2 style='border: 1px solid $table_border' width=20%><small>Ημ/νία Αιτ.</small></td>
         <td class=td_label2 style='border: 1px solid $table_border' width=20%><small>Σχόλια</small></td>
         <td class=td_label2 style='border: 1px solid $table_border' width=15% align=center><small>Ενέργειες</small></td>
      </tr>";

	$sql = db_query("SELECT rid,profname,profsurname,proftmima,profcomm,profuname,profemail,date_open,comment 
								FROM prof_request WHERE status='1' and statut='5'");

	for ($j = 0; $j < mysql_num_rows($sql); $j++) {
		$req = mysql_fetch_array($sql);
$tool_content .= "<tr onMouseOver=\"this.style.backgroundColor='#F1F1F1'\" onMouseOut=\"this.style.backgroundColor='transparent'\">";
     $tool_content .= "<td class=kk title=".htmlspecialchars($req[3])."><small>".htmlspecialchars($req[1])."<br>";
     $tool_content .= htmlspecialchars($req[2])."</small></td>";
		for ($i = 5; $i < mysql_num_fields($sql); $i++) {
			if ($i == 6 and $req[$i] != "") {
				$tool_content .= "<td class=kk><small><a href=\"mailto:".htmlspecialchars($req[$i])."\" class=small_tools>".htmlspecialchars($req[$i])."</a></small></td>";
			} else {
				$tool_content .= "<td class=kk><small>".htmlspecialchars($req[$i])."</small></td>";
			}
		}

			$tool_content .= "<td align=center class=kk><small><a href=\"listrequsers.php?id=$req[rid]&close=1\" class=small_tools onclick=\"return confirmation();\">Κλείσιμο</a><br><a href=\"$_SERVER[PHP_SELF]?id=$req[rid]&close=2\" class=small_tools>Απόρριψη</a>";
			
			$tool_content .= "<br><a href=\"../auth/newuserreq.php?".
			"id=".urlencode($req['rid']).
			"&pn=".urlencode($req['profname']).
			"&ps=".urlencode($req['profsurname']).
			"&pu=".urlencode($req['profuname']).
			"&pe=".urlencode($req['profemail']).
			"&pt=".urlencode($req['proftmima']).
			"\" class=small_tools>Εγγραφή</a>";	
		// check for ldap server
     /*if (check_ldap_entries())
		   $tool_content .= "<br><a href='../auth/ldapnewuser.php?id=$req[rid]&m=$req[profemail]&tmima=".urlencode($req['proftmima'])."' class=small_tools>Εγγραφή LDAP</a>"; */
			$tool_content .= "</small></td></tr>";
	}
  
        if (mysql_num_rows($sql) == 0 ) {
             $tool_content .= "<tr><td colspan=9 class=kk align=center><br>Δεν Υπάρχουν Ανοικτές Αιτήσεις Φοιτητών !<br><br></td></tr>";
        }
        $tool_content .= "</table>";
        $tool_content .= "</td></tr></table>";
}

$tool_content .= "<tr><td>&nbsp;</td></tr>";

$tool_content .= "</td></tr>
   <tr><td align=right>
    <a href=\"../admin/index.php\" class=mainpage>$langBackAdmin&nbsp;</a>
    </td></tr></table>
	</td></tr>
	<tr><td>&nbsp;</td></tr>
</table>";

draw($tool_content,3,'admin');
?>
