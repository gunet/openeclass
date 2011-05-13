<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/


if (!isset($_GET['language'])) {
	$language = 'greek';
} else {
	$language = preg_replace('/[^a-z-]/', '', $_GET['language']);
}

if (file_exists("../lang/$language/help.inc.php")) {
	include("../lang/$language/help.inc.php");
} else {
	die('No such help topic');
}

// Default topic
if (!isset($_GET['topic']) or !isset($GLOBALS["lang$_GET[topic]Content"])) {
	$_GET['topic'] = 'Default';
}

header('Content-Type: text/html; charset=UTF-8');

$title = $GLOBALS['langH' . str_replace('_student', '', $_GET['topic'])];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title><?php echo $GLOBALS["langH$_GET[topic]"]; ?></title>
		<link href="../../template/classic/tool_content.css" rel="stylesheet" type="text/css" />

		<style type='text/css'>
			html {
                                background-color: #687A92;
                                padding: 5px 5px 5px 5px;
                           }
                      body {
                                background-color: #ffffff;
                                color: #666666;
                                font-size: 10px; font-family: Verdana, Arial;
                                line-height: 1.3;
                           }

                        h3 {
                                background : #dfdfdf;
                                font-size : 14pt;
                                border-bottom : 3px solid #687A92;
                                padding : 8px;
                                font-variant : normal;
                                font-weight : bold;
                           }

                        p {
                           padding: 10px;
                           text-align:justify;
                        }

			h4 {
                font-family: Verdana, Arial, Helvetica;
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
		<h3><?php echo $title; ?></h3>
		<?php echo $GLOBALS["lang$_GET[topic]Content"]; ?>
		<div align="right"><a href='javascript:window.close();'><?php echo $langWindowClose; ?></a>&nbsp;&nbsp;</div>
		<br />
	</body>
</html>
