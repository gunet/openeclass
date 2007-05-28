<?
$langFiles = array('registration', 'admin');

$require_admin = TRUE;
include '../../include/baseTheme.php';
include('../../include/sendMail.inc.php');

$nameTools = $langNewUser;
$navigation[]= array ("url"=>"../admin/", "name"=> $langAdmin);

// Initialise $tool_content
$tool_content = "";

$tool_content .= "<table width=99% border='0' height=316 cellspacing='0' align=center cellpadding='0'>\n";
$tool_content .= "<tr>\n";
$tool_content .= "<td valign=top>\n";

$tool_content .= "
   <table border=0 width=60% align=center>
   <tr>
    <td>
    <form action='newuserreq_second.php' method='post'>
    <table  border=0 cellpadding='1' cellspacing='2' border='0' width='100%' align=center>
		<thead>
    <tr valign='top'>
    <th class=color1 style='border : 1px solid $table_border;' width=50%>$langSurname</th>
	  <td><input type='text' class=auth_input_admin name='nom_form' value='".@$ps."' >
		<small>&nbsp;(*)</small></td>
	  </tr>
	  <tr>
	  <th class=color1 style='border : 1px solid $table_border;' width=50%>$langName</th>
	  <td><input type='text' class=auth_input_admin name='prenom_form' value='".@$pn."' >
		<small>&nbsp;(*)</small></td>
	  </tr>
	  <tr>
	  <th class=color1 style='border : 1px solid $table_border;' width=50%>$langUsername</th>
	  <td><input type='text' class=auth_input_admin name='uname' value='".@$pu."'>
		<small>&nbsp;(*)</small></td>
	  </tr>
	  <tr>
	  <th class=color1 style='border : 1px solid $table_border;' width=50%>$langPass&nbsp;:</th>
	  <td><input type='text' class=auth_input_admin name='password' value=".create_pass(5)."></td>
	  </tr>
	  <tr>
    <th class=color1 style='border : 1px solid $table_border;' width=50%>$langEmail</th>
	  <td><input type='text' class=auth_input_admin name='email_form' value='".@$pe."'>
		<small>&nbsp;(*)</small></td>
	  </tr>
	  <tr>
	  <th class=color1 style='border : 1px solid $table_border;' width=50%>$langDepartment &nbsp;
		</span></th><td>";

		$dep = array();
        $deps=db_query("SELECT name FROM faculte order by id");
			while ($n = mysql_fetch_array($deps))
					$dep[$n[0]] = $n['name'];  

		if (isset($pt))
			$tool_content .= selection ($dep, 'department', $pt);
		else 
			$tool_content .= selection ($dep, 'department');
 
	   	$tool_content .= "</td></tr>
	  		<tr><td>&nbsp;</td>
			 	<td><input type='submit' name='submit' value='$langOk' ></td>
		    </tr></thead></table>
				<input type='hidden' name='rid' value='$id'>
    		</form>
				</td></tr>
    <tr><td align='right'><span class='explanationtext'>$langRequiredFields</span></td></tr>
    </table></td></tr>
    <tr><td align=right>";
		        
   	$tool_content .= "<a href=\"../admin/index.php\" class=mainpage>$langBackAdmin&nbsp;</a>";
		$tool_content .= "<tr><td>&nbsp;</td></tr>";
		$tool_content .= "</table>\n";

		draw($tool_content,3, 'auth');
?>
