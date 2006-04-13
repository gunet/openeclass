<?
$require_login = TRUE;
$require_current_course = TRUE;
$langFiles = 'chat';
include '../../include/init.php';
?>
<html>
<head><title><?= $siteName ?></title></head>
	<frameset rows="40%,*,15%" marginwidth="0" frameborder="NO" border="0">  
		<frame src="chat_header.php" name="topBanner" scrolling="no" noresize>
		<frame src="messageList.php" name="messageList" scrolling="auto">
		<frame src="messageEditor.php" name="messageEditor" scrolling="no" noresize>
	</frameset>
</html>
