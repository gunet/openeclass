<?

if (!isset($language)) {
	$language = 'greek';
} else {
	$language = preg_replace('/[^a-z-]/', '', $language);
}

if (file_exists("../lang/$language/help.inc.php")) {
	include("../lang/$language/help.inc.php");
} else {
	die('No such help topic');
}

// Default topic
if (!isset($_GET['topic']) ||  empty($GLOBALS["lang$_GET[topic]Content"])) {
	$_GET['topic'] = 'Default';
}

header('Content-Type: text/html; charset=UTF-8');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title><?= $GLOBALS["langH$_GET[topic]"] ?></title>
		<link href="../../template/classic/tool_content.css" rel="stylesheet" type="text/css" />

		<style type='text/css'>
					html {
                                background-color: #575656;
                                padding: 5px 5px 5px 5px;
                           }	
                      body {
                                background-color: #ffffff;
                                color: #575656;
                                font-size: 11px; font-family: Verdana, Arial;
                                line-height: 1.3;
                           }

                        h3 {
                                color: #004571;
                                background : #F1F1F1;
                                font-size : 14pt;
                                border-bottom : 1px solid Silver;
                                border-right : 1px solid Silver;
                                border-top : 1px solid Silver;
                                border-left : 1px solid Silver;
                                padding : 8px;
                                font-variant : normal;
                                font-weight : bold;
                           }
                        
                        p {
                           padding: 10px;
                            text-align:justify;
                        }

			h4 {
				background-color: white; 
                                font-family: Verdana, Arial, Helvetica;
                                color: #004571;
				font-size: 12px;
                                padding: 10px;
                                border-bottom : 1px dotted silver;
			   }
			.helptopic {
				font-size: 11px; font-family: Verdana, Arial;
				text-align:justify;
				line-height: 1.3;
				margin-right: 10px;
				margin-left: 10px;
				}
			.lihelptopic {
				font-size: 11px; font-family: Verdana, Arial;
				text-align:justify;
				line-height: 1.3;
				margin-right: 10px;
				margin-left: 10px;
				list-style-type: square;
                                }

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
