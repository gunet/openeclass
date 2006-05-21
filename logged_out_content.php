<?PHP
//logged_out_content.php



$tool_content .= <<<lCont
<div id="container_login">
<!--<div id="header"><h1>Header</h1></div>-->
<div id="wrapper">
<div id="content_login">
<p>Η πλατφόρμα <strong>GUnet e-Class</strong> αποτελεί ένα ολοκληρωμένο σύστημα ηλεκτρονικής οργάνωσης, αποθήκευσης και παρουσίασης ηλεκτρονικού εκπαιδευτικού υλικού. Είναι σχεδιασμένη με προσανατολισμό την ενίσχυση και υποστήριξη της εκπαιδευτικής διαδικασίας, προσφέροντας στους συμμετέχοντες ένα δυναμικό περιβάλλον αλληλεπίδρασης, ανεξάρτητο από τους περιοριστικούς παράγοντες του χώρου και του χρόνου της κλασσικής διδασκαλίας.</p>


</div>
</div>
<div id="navigation">

 <table width="99%">
      <thead>
      	<tr>
      		<th> Σύνδεση Χρήστη </th>
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
				<font size="1">$warning</font><br>
				<font size="2"><a href="modules/auth/lostpass.php">$lang_forgot_pass</a></font><br></p>
     			 </form>
     		</td>
     	</tr>
      </tbody>
      </table>


</div>
<div id="extra">
<p>Χώρος για εικόνες Χώρος για εικόνες Χώρος για εικόνες Χώρος για εικόνες Χώρος για εικόνες Χώρος για εικόνες Χώρος για εικόνες Χώρος για εικόνες Χώρος για εικόνες Χώρος για εικόνες Χώρος για εικόνες Χώρος για εικόνες Χώρος για εικόνες Χώρος για εικόνες Χώρος για εικόνες Χώρος για εικόνες Χώρος για εικόνες Χώρος για εικόνες Χώρος για εικόνες Χώρος για εικόνες Χώρος για εικόνες Χώρος για εικόνες Χώρος για εικόνες  </p>
</div>
<!--<div id="footer"><p>Here it goes the footer</p></div>-->
</div>

lCont;



?>
