<?PHP
//logged_out_content.php
// @author Evelthon Prodromou <eprodromou@upnet.gr>
$sql_el ="SELECT `date`, `gr_title` , `gr_body` , `gr_comment`
		FROM `admin_announcements`
		WHERE `visible` = \"V\"
		";
$sql_en ="SELECT `date`, `en_title` , `en_body` , `en_comment`
		FROM `admin_announcements`
		WHERE `visible` = \"V\"
		";

if(session_is_registered('langswitch')) {
	$language = $_SESSION['langswitch'];
}

if ($language == "greek") $sql = $sql_el;
else $sql = $sql_en;

	$tool_content .= <<<lCont
<div id="container_login">

<div id="wrapper">
<div id="content_login">
<p>$langInfo</p>
lCont;
$result = db_query($sql, $mysqlMainDb);
	if (mysql_num_rows($result) > 0) {
		$announceArr = array();
		while ($eclassAnnounce = mysql_fetch_array($result)) {
			array_push($announceArr, $eclassAnnounce);
		}

		$tool_content .= "
<br/>

<table width=\"99%\">
	<thead>
		<tr>
			<th> $langPlatformAnnounce </th>
		</tr>
	</thead>
	<tbody>";


		$numOfAnnouncements = count($announceArr);

		for($i=0; $i < $numOfAnnouncements; $i++) {

			if ($i%2 == 0) $rowClass = "class=\"odd\"";
			else $rowClass = "";

			$tool_content .= "
		<tr $rowClass>
			<td>
				<p><b>".$announceArr[$i][0].":</b> <u>".$announceArr[$i][1]."</u></p>
				<p>".$announceArr[$i][2]."</p>
				<p><i>".$announceArr[$i][3]."</i></p>
			</td>
		</tr>
		";

		}

		$tool_content .= "
			</tbody>
		</table>";
	}
	$tool_content .= <<<lCont2
</div>
</div>
<div id="navigation">

 <table width="99%">
      <thead>
      	<tr>
      		<th> $langUserLogin </th>
      	</tr>
      </thead>
      <tbody>
      	<tr class="odd">
      		<td>
      			<form action="index.php" method="post">
      		  $langUserName <br>
        			<input  name="uname" size="20"><br>
       			 $langPass <br>
        			<input name="pass" type="password" size="20"><br><br>
       			 <input value="$langEnter" name="submit" type="submit"><br>
				$warning<br>
				<a href="modules/auth/lostpass.php">$lang_forgot_pass</a>
     			 </form>
     		</td>
     	</tr>
      </tbody>
      </table>


</div>
<div id="extra">
<p>{ECLASS_HOME_EXTRAS_RIGHT}</p>
</div>

</div>

lCont2;



?>
