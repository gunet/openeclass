<?php  

$require_current_course = TRUE;
$langFiles = 'import';
include('../../include/init.php');
$nameTools = $langLinks;

begin_page();

$f = fopen("../../courses/$currentCourseID/page/$link","r");
while (!feof($f)) {
      echo(fread($f,1024));
}
fclose($f);
?>
		</td>
	</tr>
</table>
</body>
</html>
