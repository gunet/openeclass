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

$nameTools= $langUserOpenRequests;
$navigation[]= array ("url"=>"index.php", "name"=> $langAdmin);
$local_style = " th {font-size:12px; font-family:Verdana, Arial, Helvetica;  } ";
$sendmail =0;

$local_head = '
<script type="text/javascript">
function confirmation() {
   if (confirm("'.$langCloseConf.'")) {
                return true;
   } else {
          return false;
  }
}
</script>';

// Initialise $tool_content
$tool_content = "";

$tool_content .= "<table width=100% border='0' cellspacing='0' align=center cellpadding='0'>\n";
$tool_content .= "<tr>\n";
$tool_content .= "<td valign=top>\n";

if (isset($close) && $close == 1) {
	$sql = db_query("UPDATE prof_request set status='2', date_closed=NOW() WHERE rid='$id'");
	$tool_content .= "<br><br><center>Η αίτηση του φοιτητή έκλεισε !</center>";
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

$tool_content .= "<form action='$_SERVER[PHP_SELF]' method='post'>
        <table border='0' width='100%' cellspacing='0'>
        <tr>
       <td class=td_label2 style='border : 1px solid $table_border'>$langWarnReject <b>'$d[profname] $d[profsurname] &lt;$d[profemail]&gt;'</b> $langWithDetails:</td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <tr>
        <td>";
        
        $tool_content .= "<table align=center width=50% border=\"0\" cellspacing=1 cellpading=1 style=\"border: 1px solid $table_border;\">
           <tr>
             <td class=color1 style=\"border: 1px solid $table_border;\"><b>$langSurname</b>:</td>
             <td class=stat2 style=\"border: 1px solid $table_border;\">$d[profsurname]</td>
           </tr>
           <tr>
             <td class=color1 style=\"border: 1px solid $table_border;\"><b>$langName</b>:</td>
             <td class=stat2 style=\"border: 1px solid $table_border;\">$d[profname]</td>
           </tr>
           <tr>
             <td class=color1 style=\"border: 1px solid $table_border;\"><b>$langEmail</b>:</td>
             <td class=stat2 style=\"border: 1px solid $table_border;\">$d[profemail]</td>
           </tr>
           <tr>
             <td class=color1 style=\"border: 1px solid $table_border;\"><b>$langFaculte</b>:</td>
             <td class=stat2 style=\"border: 1px solid $table_border;\">$d[proftmima]</td>
           </tr>
           <tr>
             <td class=color1 style=\"border: 1px solid $table_border;\"><b>$langDateRequest</b>:</td>
             <td class=stat2 style=\"border: 1px solid $table_border;\">$d[date_open]</td>
           </tr>
           <tr>
             <td class=color1 style=\"border: 1px solid $table_border;\"><b>$langphone</b>:</td>
             <td class=stat2 style=\"border: 1px solid $table_border;\">$d[profcomm]</td>
           </tr>
           </table>
        ";
        
         $tool_content .= "</td></tr>
        <tr>
         <td class=color1 style='border : 1px solid $table_border'>$langComments:</td>
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
                <input type='checkbox' name='sendmail' value='1' checked='yes'>
                &nbsp;$langRequestSendMessage &nbsp;&nbsp;
								<input type='text' name='prof_email' class=auth_input_admin value='$d[profemail] '></td></tr>
								<tr>
								<td align='center'>
                <input type='submit' name='submit' value='$langRejectRequest'>
            </td>
        </tr>
        <tr>
           <td class=kk align=right><small>($langRequestDisplayMessage)</small></td>
        </tr>
        </table>
      </form>";
	}

} else {

      $tool_content .= "<table align=center width=98% border='0' bgcolor=white cellspacing=0 cellpadding=0 style='border: 1px solid gold;'>
      <tr>
         <td class=td_label2 style='border: 1px solid $table_border' width=15%><small>$langName<br>$langSurname</small></td>
         <td class=td_label2 style='border: 1px solid $table_border' width=10%><small>$langUsername</small></td>
         <td class=td_label2 style='border: 1px solid $table_border' width=20%><small>$langEmail</small></td>
         <td class=td_label2 style='border: 1px solid $table_border' width=20%><small>Ημ/νία Αιτ.</small></td>
         <td class=td_label2 style='border: 1px solid $table_border' width=20%><small>$langComments</small></td>
         <td class=td_label2 style='border: 1px solid $table_border' width=15% align=center><small>$langActions</small></td>
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

			$tool_content .= "<td align=center class=kk><small><a href='$_SERVER[PHP_SELF]?id=$req[rid]&close=1' class=small_tools onclick='return confirmation();'>Κλείσιμο</a><br><a href='$_SERVER[PHP_SELF]?id=$req[rid]&close=2' class=small_tools>$langRejectRequest</a>";
			
			$tool_content .= "<br><a href=\"../auth/newuserreq.php?".
			"id=".urlencode($req['rid']).
			"&pn=".urlencode($req['profname']).
			"&ps=".urlencode($req['profsurname']).
			"&pu=".urlencode($req['profuname']).
			"&pe=".urlencode($req['profemail']).
			"&pt=".urlencode($req['proftmima']).
			"\" class=small_tools>$langAcceptRequest</a>";	
		// check for ldap server
     /*if (check_ldap_entries())
		   $tool_content .= "<br><a href='../auth/ldapnewuser.php?id=$req[rid]&m=$req[profemail]&tmima=".urlencode($req['proftmima'])."' class=small_tools>Εγγραφή LDAP</a>"; */
			$tool_content .= "</small></td></tr>";
	}
  
        if (mysql_num_rows($sql) == 0 ) {
             $tool_content .= "<tr><td colspan=9 class=kk align=center><br>$langUserNoRequests<br><br></td></tr>";
        }
        $tool_content .= "</table>";
}

$tool_content .= "<tr><td align=right>
   <a href=\"../admin/index.php\" class=mainpage>$langBackAdmin&nbsp;</a>
	 </td></tr></table>";

draw($tool_content,3,'admin');
?>
