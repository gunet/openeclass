<?PHP
//logged_out_content.php



$tool_content .= <<<lCont
<div id="container_login">
<!--<div id="header"><h1>Header</h1></div>-->
<div id="wrapper">
<div id="content_login">
<p>$langInfo</p>


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
<!--<div id="footer"><p>Here it goes the footer</p></div>-->
</div>

lCont;



?>
