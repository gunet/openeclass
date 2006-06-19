<?php 

/*
Header, Copyright, etc ...
 */

$require_current_course = TRUE;
$langFiles='learnPath';

require_once("../../../config/config.php");
require_once ('../../../include/init.php');
?>
<html>
<head>
 <script>
  <!-- //
   parent.tocFrame.location.href="../viewer_toc.php";
  //-->
 </script>
</head>
<body>
 <center>
  <br /><br /><br />
  <p>
<?php
if($_GET['op'] == 'cancel')
{
    echo $langExerciseCancelled;
}
elseif($_GET['op'] == 'finish') // exercise done
{
    echo $langExerciseDone;
}
?>
   </p>
  </center>
 </body>
</html>