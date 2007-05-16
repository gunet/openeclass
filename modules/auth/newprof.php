<?
$langFiles = array('registration', 'admin', 'gunet');
include '../../include/baseTheme.php';
include 'auth.inc.php';
$nameTools = $reqregprof;

// Initialise $tool_content
$tool_content = "";

$auth = get_auth_id();

$tool_content .= "	<form action=\"newprof_second.php\" method=\"post\">

	<table width=\"99%\" cellspacing='1' cellpadding='1'>
	";
	//<caption>".$profpers."</caption>

$tool_content .= "		
	<tbody>
	<thead>
	<tr>
	<th>$langSurname</th>
	<td><input type=\"text\" name=\"nom_form\" value=\"".@$ps."\" > (*)</td>
	</tr>
	<tr>
	<th>$langName</th>
	<td>
	<input type=\"text\" name=\"prenom_form\" value=\"".@$pn."\"> (*)</td>
	</tr>
	<tr>
	<th>$langUsername</th>
	<td><input type=\"text\" name=\"uname\" value=\"".@$pu."\"> (*) (**)</td>
	</tr>
	<tr>
	<th>$langPass</th>
	<td><input type=\"text\" name=\"password\" value=\"".create_pass(5)."\"> (**)</td>
	</tr>
	<tr>
	<th>$langEmail</th>
	<td><input type=\"text\" name=\"email_form\" value=\"".@$pe."\"> (*)</td>
	</tr>
	<tr>
        <th>$profcomment</td>
	<td>
        <textarea name=\"usercomment\" COLS=\"35\" ROWS=\"4\" WRAP=\"SOFT\">".@$usercomment."</textarea>
	 (*) $profreason
        </td>
        </tr>
	<tr>
        <th>".$langDepartment."</th>
        <td><select name=\"department\">";
        $deps=mysql_query("SELECT name FROM faculte order by id");
        while ($dep = mysql_fetch_array($deps)) 
        {
        	$tool_content .= "<option value=\"$dep[0]\">$dep[0]</option>\n";
        }
        $tool_content .= "</select>
        </td>
        </tr>	
        </thead>
		<tr><td>&nbsp;</td>
		    <td>
			<input type=\"submit\" name=\"submit\" value=\"".$langSubmitNew."\" >
	        <input type=\"hidden\" name=\"auth\" value=\"1\" >
			<br/><br/>
			<p>$langRequiredFields<br>
	           $star2 . $langCharactersNotAllowed</p>
			</td>
		</tr>
        </table>
        
        		
	</form>	
	<br/>
	
	
";

draw($tool_content,0,'auth');

?>
