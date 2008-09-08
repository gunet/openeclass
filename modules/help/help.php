<?
/*===========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2008  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/


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

$title = $GLOBALS["langH" . str_replace('_student', '', $_GET['topic'])];

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
		<h3><?= $title ?></h3>
		<?= $GLOBALS["lang$_GET[topic]Content"] ?>	
		<center><p>
			<a href='javascript:window.close();'><?= $langWindowClose ?></a>
		</p></center>
	</body>
</html>
