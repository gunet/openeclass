<?

if (!isset($language)) {
	$language = 'greek';
} else {
	$language = preg_replace('/[^a-z-]/', '', $language);
}

include("../lang/$language/help.inc");

// Default topic
if (empty($GLOBALS["lang$_GET[topic]Content"])) {
	$_GET['topic'] = 'Clar';
}

?>
<html>
	<head>
		<title><?= $GLOBALS["langH$_GET[topic]"] ?></title>
		<link href="../../template/classic/tool_content.css" rel="stylesheet" type="text/css" />
		<style type='text/css'>
			body, h1 { background-color: white; font-family: Arial, Helvetica, sans-serif; }
			ul, li {font-size:12px;}
		</style>
	</head>
	<body>
		<h3><?= $GLOBALS["langH$_GET[topic]"] ?></h3>
		<?= $GLOBALS["lang$_GET[topic]Content"] ?>	
		<center><p>
			<a href='javascript:window.close();'><?= $langClose ?></a>
		</p></center>
	</body>
</html>
