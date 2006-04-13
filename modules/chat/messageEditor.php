<?
$require_login = TRUE;
$require_current_course = TRUE;
$langFiles = 'chat';
include '../../include/init.php';
?>

<html>
<head>
<script>
function prepare_message()
{
	document.chatForm.chatLine.value=document.chatForm.msg.value;
	document.chatForm.msg.value = "";
	document.chatForm.msg.focus();
	return true;
}
</script>

</head>
<body>
<table border="0" align="center" cellpadding="0" cellspacing="0" width="<?php echo $mainInterfaceWidth?>">
<form name = "chatForm" action = "messageList.php#bottom" method = "get" target = "messageList"
	  onSubmit = "return prepare_message();">

<tr><td>
<input type="text" name="msg" size="80">
<input type="hidden" name="chatLine"></td>
<td><input type="submit" value=" >> "></td>
</tr>
</form>
<?
if ($is_adminOfCourse) { 
?>
	<tr><td>&nbsp;</td></tr>
	<tr><td><a href="messageList.php?reset=true" target="messageList"><?= $langWash ?></a> | 
	<a href="messageList.php?store=true" target="messageList"><?= $langSave ?></a></td></tr>
<?
 }
?>
</table>

