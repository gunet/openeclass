<?
include '../../include/baseTheme.php';
include 'auth.inc.php';
$nameTools = $langReqRegProf;

// Initialise $tool_content
$tool_content = "";

$auth = get_auth_id();
$tool_content .= "	
<table width=\"99%\" class='FormData' align='left'>
<thead>
<tr>
<td>
<form action=\"newprof_second.php\" method=\"post\">

  <table width=\"100%\" align='left'>
  <tbody>
  <tr>
    <th class='left' width='20%'>$langSurname</th>
    <td width='10%'><input size='35' type=\"text\" name=\"nom_form\" value=\"".@$ps."\" class='FormData_InputText'></td>
	<td>(*)</td>
  </tr>
  <tr>
    <th class='left'>$langName</th>
    <td><input size='35' type=\"text\" name=\"prenom_form\" value=\"".@$pn."\" class='FormData_InputText'></td>
	<td>(*)</td>
  </tr>
  <tr>
    <th class='left'>$langUsername</th>
    <td><input size='35' type=\"text\" name=\"uname\" value=\"".@$pu."\" class='FormData_InputText'></td>
	<td>(*) (**)</td>
  </tr>
  <tr>
    <th class='left'>$langPass</th>
    <td><input size='35' type=\"text\" name=\"password\" value=\"".create_pass(5)."\" class='FormData_InputText'></td>
	<td>(**)</td>
  </tr>
  <tr>
    <th class='left'>$langEmail</th>
    <td><input size='35' type=\"text\" name=\"email_form\" value=\"".@$pe."\" class='FormData_InputText'></td>
	<td>(*)</td>
  </tr>
  <tr>
    <th class='left'>$langComments</td>
    <td><textarea name=\"usercomment\" COLS=\"32\" ROWS=\"4\" WRAP=\"SOFT\" class='FormData_InputText'>".@$usercomment."</textarea></td>
	<td>(*) $profreason</td>
  </tr>
  <tr>
    <th class='left'>".$langDepartment."</th>
    <td colspan='2'><select name=\"department\">";
        $deps=mysql_query("SELECT name FROM faculte order by id");
        while ($dep = mysql_fetch_array($deps)) 
        {
        	$tool_content .= "<option value=\"$dep[0]\">$dep[0]</option>\n";
        }
        $tool_content .= "</select>
    </td>
  </tr>	
  <tr>
    <th>&nbsp;</th>
    <td>
    <input type=\"submit\" name=\"submit\" value=\"".$langSubmitNew."\" >
    <input type=\"hidden\" name=\"auth\" value=\"1\" ></td>
	<td>
    <p align='right'>$langRequiredFields<br>$langStar2 . $langCharactersNotAllowed</p>
    </td>
  </tr>
  </tbody>
  </table>
  
  </form>
  </td>
  </tr>
  </table>
		
	<br/>
";
draw($tool_content,0);
?>
