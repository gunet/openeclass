<?
$langFiles = array('registration', 'admin', 'gunet');
include '../../include/baseTheme.php';
include 'auth.inc.php';
//@include "check_admin.inc";
$nameTools = $reqregprof;

// Initialise $tool_content
$tool_content = "";

$auth = get_auth_id();

$tool_content .= "	<form action=\"newprof_second.php\" method=\"post\">
	<table width=\"99%\"><caption>".$profpers."</caption><tbody>
	<tr valign=\"top\" bgcolor=\"".$color2."\">
	<td width=\"3%\" nowrap><b>".$langSurname."</b></td>
	<td><input type=\"text\" name=\"nom_form\" value=\"".@$ps."\" >&nbsp;(*)</td>
	</tr>
	<tr bgcolor=\"".$color2."\">
	<td width=\"3%\" nowrap><b>".$langName."</b></td>
	<td>
	<input type=\"text\" name=\"prenom_form\" value=\"".@$pn."\">&nbsp;(*)</td>
	</tr>
	<tr bgcolor=\"".$color2."\">
	<td width=\"3%\" nowrap><b>".$langUsername."</b></td>
	<td><input type=\"text\" name=\"uname\" value=\"".@$pu."\">&nbsp;(*)</td>
	</tr>
	<tr bgcolor=\"".$color2."\">
	<td width=\"3%\" nowrap><b>".$langPass."&nbsp;:</b></td>
	<td><input type=\"text\" name=\"password\" value=\"".create_pass(5)."\"></td>
	</tr>
	<tr bgcolor=\"".$color2."\">
	<td width=\"3%\" nowrap><b>".$langEmail."</b></td>
	<td><input type=\"text\" name=\"email_form\" value=\"".@$pe."\">&nbsp;(*)</b></td>
	</tr>
	<tr bgcolor=\"".$color2."\">
        <td>".$profcomment."<br><font size=\"1\">".$profreason."
       	</td>
	<td>
        <textarea name=\"usercomment\" COLS=\"35\" ROWS=\"4\" WRAP=\"SOFT\">".@$usercomment."</textarea>
	<font size=\"1\">&nbsp;(*)</font>
        </td>
        </tr>
	<tr bgcolor=\"".$color2."\">
        <td>".$langDepartment.":</td>
        <td><select name=\"department\">";
        $deps=mysql_query("SELECT name FROM faculte order by id");
        while ($dep = mysql_fetch_array($deps)) 
        {
        	$tool_content .= "<option value=\"$dep[0]\">$dep[0]</option>\n";
        }
        $tool_content .= "</select>
        </td>
        </tr>				
	<tr><td colspan=\"2\">".$langRequiredFields."</td></tr>
	<tr><td>&nbsp;</td>
	<td><input type=\"submit\" name=\"submit\" value=\"".$langOk."\" >
	<input type=\"hidden\" name=\"auth\" value=\"1\" >
	</td>
	</tr>
</tbody></table></form>
";

$tool_content .= "<center><p><a href=\"../admin/index.php\">Επιστροφή</p></center>";

draw($tool_content,0);

// creating passwords automatically
function create_pass($length) {
	$res = "";
	$PASSCHARS="abcdefghijklmnopqrstuvwxyz023456789";
	$PASSL = 35;
	srand ((double) microtime() * 1000000);
	for ($i = 1; $i<=$length ; $i++ ) {
		$res .= $PASSCHARS[rand(0,$PASSL-1)];
	}
	return $res;
}
?>
