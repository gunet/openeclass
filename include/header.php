<?

/*
 +----------------------------------------------------------------------+
 | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
 | Copyright (c) 2003 GUNet                                             |
 +----------------------------------------------------------------------+
 | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
 |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
 |          Christophe Gesche <gesche@ipm.ucl.ac.be>                    |
 |                                                                      |
 | e-class changes by: Costas Tsibanis <costas@noc.uoa.gr>              |
 |                     Yannis Exidaridis <jexi@noc.uoa.gr>              |
 |                     Alexandros Diamantidis <adia@noc.uoa.gr>         |
 +----------------------------------------------------------------------+
 | Standard header included by all e-class files                        |
 | Defines standard functions and validates variables                   |
 +----------------------------------------------------------------------+
*/

/*
 * You can set the following variables before including this file:
 *
 * $langFiles: a variable or array containing the names of 
 *             language files required (without the .inc.php extension)
 * $require_login: set to true to mark a file that requires a logged-in user
 * $require_current_course: set to true to mark a file requiring
 *             the user to have selected a course. In this case, the
 *             variables containing course settings are given values.
 $ $language_override: set this to force messages in another language
 *
 * To show the initial banner, call the function begin_page().
 * The name of the page is taken from the variable $nameTools,
 * and additional navigation breadcrumps are taken from the
 * array $navigation, which must contain pairs of (url, description)
 * like this:
 * $navigation[] = array("url" => "file.php", "name"=> "Description");
 * You can set $page_title to have a title different from $nameTools.
 * You can also call begin_page() as:
 * begin_page($nameTools, $navigation) (both arguments are optional).
 * If you want to add something to the stylesheet set for this page,
 * set the variable $local_style before calling begin_page(); e.g.,
 * $local_style = 'body { background: red; }';
 * If you want to add something else in the <HEAD> section of a page,
 * eg. a script, set $local_head appropriately.
 */



// ---------------------------------------------------------
// this function draws the logo and the navigation bar
// you must call this function in every script you want the logo to be displayed
// ---------------------------------------------------------

function begin_page ($page_name = FALSE, $page_navi = FALSE) {
	global $is_adminOfCourse, $urlServer, $bannerPath, $nameTools, $intitule, $siteName,
		$charset, $colorLight, $colorMedium, $colorDark,
		$langUser, $prenom, $nom, $titulaires,
		$code_cours, $currentCourseID, $uid, $navigation,
		$mainInterfaceWidth,$langLogout, $mysqlMainDb, $official_prefix,
		$require_help, $require_login, $require_current_course, 
		$language, $langHelp, $helpTopic, $page_title, $local_style, $local_head;

	if (!$page_navi)
			$page_navi = $navigation;

	if (!$page_name)
			$page_name = $nameTools;

	@ header('Content-Type: text/html; charset='. $charset);
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?= $charset?>">
	<link rel="stylesheet" href="<?= $urlServer ?>/template/default.css" type="text/css">
	<? if (!empty($local_style)) {
		echo "<style type='text/css'>$local_style</style>\n";
	}
	if (!empty($local_head)) {
		echo $local_head;
	} ?>
	<title> 
	<?
	if (isset($intitule)) 
		echo q("$page_name - $intitule - $siteName"); 
	else 
		echo q("$page_name - $siteName"); 
	?>
	</title>
</head>
<body bgcolor="white">
<table border="0" align="center" cellpadding="0" cellspacing="0" width="<?= $mainInterfaceWidth?>">
	<tr><td><font face="arial, helvetica" size="2">
		<table cellpadding="3" border=0 cellspacing="2">
			<tr><td colspan="5" align="center" style="padding:0px;" bgcolor="<?= $colorMedium ?>">
				<font face="Arial, Helvetica, sans-serif" color="#FFFFFF" size="2">
				<img src="<?= $urlServer.$bannerPath ?>"></td></tr>
			<tr><td colspan="5" bgcolor="<?= $colorMedium ?>">
				<font face="Arial, Helvetica, sans-serif" color="#FFFFFF" size="2">
<?
	if (isset($uid)) { 
		if (isset($require_login) or ($require_current_course)) 
			echo "<span style='float: left'>$langUser: ". q("$prenom $nom"). "</span>";
		echo "<a href='".$urlServer."index.php?logout=yes' target=_top
			style='float: right; color: white;'>$langLogout</a></td></tr>";
		} else {
			echo "&nbsp;</td></tr>";
		}
	echo "</font>\n";

echo"</tr>";
if (isset($currentCourseID)) {
$f = mysql_fetch_array(db_query("SELECT fake_code FROM cours WHERE code='$code_cours'",$mysqlMainDb));
echo "<tr bgcolor=\"$colorLight\"><td colspan=\"5\" style=\"padding:5px;\">
        <b><font face=\" Arial, Helvetica, sans-serif\" size=\"3\" color=\"$colorDark\">
        <i><font color=\"#CC3300\">$f[0]</font></i> - $intitule</font>";
echo "<br>";
echo "<font face=\" Arial, Helvetica, sans-serif\" size=\"2\">Διδάσκων: $titulaires </font></b>";

//---------------------------------
// additional info for di - eclass
// dont mess about it... 
// it has been used in one special adaptation of the platform
//--------------------------------

// check if lesson is official or seminar

$s=mysql_fetch_array(mysql_query("SELECT faculte FROM cours WHERE code='$currentCourseID'"));
if (isset($official_prefix) and $s[0] == $official_prefix) {
        $semester=mysql_fetch_array(db_query("SELECT semester,theory,lab,pract FROM lessons WHERE code='$f[0]'",$mysqlMainDb));
        echo " <br><font face=\" Arial, Helvetica, sans-serif\" size=\"2\">
                <b>Εξάμηνο: $semester[0]o</b>
                </font>
                <font face=\" Arial, Helvetica, sans-serif\" color=\"#000066\" size=\"2\">";
                echo "<br><font face=\" Arial, Helvetica, sans-serif\" size=\"2\">";
                if (empty($semester[1])) $semester[1]='-';
                        echo "<b>Ώρες Θεωρίας:</b> $semester[1]";
                if (empty($semester[2])) $semester[2]='-';
                        echo "&nbsp;&nbsp;&nbsp;<b>Ώρες Φροντιστηρίου:</b> $semester[2]";
                if (empty($semester[3])) $semester[3]='-';
                echo "&nbsp;&nbsp;&nbsp;<b>Ώρες Εργαστηρίου:</b> $semester[3]<br>";
        echo "</font>";
        echo "</td></tr>";
        }
if ($s[0] == 'S') {
        echo "<br><font face=\" Arial, Helvetica, sans-serif\" size=\"2\">";
        $sl=mysql_fetch_array(mysql_query("SELECT suplesson FROM cours WHERE code='$code_cours'"));
        if (!empty($sl[0]))
                echo "<b>Υποστηριζόμενο Μάθημα: $sl[0]</b></font>";
        }

mysql_select_db($currentCourseID);

}

//------------------------------
// end of additional info
//-----------------------------

?>
	<tr><td colspan="4"><font face="Arial, Helvetica, sans-serif" size="1">
		<a href="<?= $urlServer.'index.php' ?>" target="_top"><?= $siteName ?></a>
<?
	if (isset($currentCourseID))
		echo "&nbsp;&gt;&nbsp;<a href=\"".$urlServer.
			"courses/$currentCourseID/index.php\" target=\"_top\">". q($intitule) . "</a>\n";
	if (isset($page_navi) && is_array($page_navi))
		foreach ($page_navi as $step)
			echo "&nbsp;&gt;&nbsp;<a target=\"_top\" href=\""
				.$step["url"]."\" >".q($step["name"])."</a>";
	if (isset($page_name)) {
		echo "&nbsp;&gt;&nbsp;<b>" . q($page_name) . "</b>";
	}
?>					<br>
				</font>
			</td></tr>
		</table>
	</td></tr>
<?
	if (!empty($page_title)) {
		$page_name = $page_title;
	}
	if (!empty($page_name)) {
		echo "<tr><td>&nbsp;<br><font face='arial, helvetica' size='2'>
			<b>&nbsp;" . q($page_name) . "</b><br>&nbsp;<br></td></tr>\n";
	}


// if variable $require_help is true then display help link and the appropriate help topic 

if ($is_adminOfCourse and isset($require_help) and $require_help) {
	echo "<tr><td colspan=\"4\" align=\"right\">
        <a href='../help/help.php?topic=$helpTopic&language=$language' 
	onClick=\"window.open('../help/help.php?topic=$helpTopic&language=$language','Help','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=450,height=550,left='+((screen.width-400)/2)+',top='+((screen.height-450)/2));
	return false; \">
        <font size=\"2\" face=\"arial, helvetica\">$langHelp</font>
         </a>
        </td>
        </tr>";
	}

echo "<tr><td>\n";

} 

// ---------------------------------------------
// end of function begin_page
// ---------------------------------------------

function start_toolbar()
{
        echo "   <table align='center' width=96% border='0' cellspacing='0' cellpadding='0'  style=\"border: 1px so
lid #DCDCDC;\">\n";
        echo "   <tr>\n";
        echo "      <td align=right class='tool_bar' valign=middle height=25>";

}

function end_toolbar()
{
                                echo "   </td>\n";
        echo "   </tr>\n";
        echo "   </table>\n\n";
        echo "   </br>\n\n";
}

function start_AdminToolbar()
{
        echo "   <table align='center' width=96% border='0' cellspacing='0' cellpadding='0'  style=\"border: 1px so
lid #DCDCDC;\">\n";
        echo "   <tr>\n";
        echo "      <td align=right class='tool_bar_Admin' valign=middle height=25>";

}

function end_AdminToolbar()
{
                                echo "   </td>\n";
        echo "   </tr>\n";
        echo "   </table>\n\n";
        echo "   </br>\n\n";
}

?>
