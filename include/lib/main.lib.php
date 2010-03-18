<?php
/*
=============================================================================
GUnet eClass 2.0
E-learning and Course Management Program
================================================================================
Copyright(c) 2003-2010  Greek Universities Network - GUnet
A full copyright notice can be read in "/info/copyright.txt".

Authors:     Costas Tsibanis <k.tsibanis@noc.uoa.gr>
Yannis Exidaridis <jexi@noc.uoa.gr>
Alexandros Diamantidis <adia@noc.uoa.gr>

For a full list of contributors, see "credits.txt".

This program is a free software under the terms of the GNU
(General Public License) as published by the Free Software
Foundation. See the GNU License for more details.
The full license can be read in "license.txt".

Contact address: GUnet Asynchronous Teleteaching Group,
Network Operations Center, University of Athens,
Panepistimiopolis Ilissia, 15784, Athens, Greece
eMail: eclassadmin@gunet.gr
/*

/*
----------------------------------------------------------------------
General useful functions for eClass
Standard header included by all eClass files
Defines standard functions and validates variables
---------------------------------------------------------------------
*/

define('ECLASS_VERSION', '2.3');

// Show query string and then do MySQL query
function db_query2($sql, $db = FALSE)
{
	echo "<hr><pre>$sql</pre><hr>";
	return db_query($sql, $db);
}

/*
 Debug MySQL queries
-------------------------------------------------------------------------
it is better to use the function below instead of the usual mysql_query()
first argument: the query
second argument (optional) : the name of the data base
If error happens just display the error and the code
-----------------------------------------------------------------------
*/

function db_query($sql, $db = FALSE) {

	if ($db) {
		mysql_select_db($db);
	}
	$r = mysql_query($sql);

	if (defined('DEBUG_MYSQL') or mysql_errno()) {
		echo '<hr>' . mysql_errno() . ': ' . mysql_error()
		. "<br><pre>$sql</pre><hr>";
	}
	return $r;
}


// Check if a string looks like a valid email address
function email_seems_valid($email)
{
        return (preg_match('#^[0-9a-z_\.\+-]+@([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,}$#i', $email)
                and !preg_match('#@.*--#', $email));
}

// Eclass SQL query wrapper returning only a single result value.
// Useful in some cases because, it avoid nested arrays of results.
function db_query_get_single_value($sqlQuery, $db = FALSE) {
	$result = db_query($sqlQuery, $db);

	if ($result) {
		list($value) = mysql_fetch_row($result);
		mysql_free_result($result);
		return $value;
	}
	else {
		return false;
	}
}

// Claroline SQL query wrapper returning only the first row of the result
// Useful in some cases because, it avoid nested arrays of results.
function db_query_get_single_row($sqlQuery, $db = FALSE) {
	$result = db_query($sqlQuery, $db);

	if($result) {
		$row = mysql_fetch_array($result, MYSQL_ASSOC);
		mysql_free_result($result);
		return $row;
	}
	else {
		return false;
	}
}

// Eclass SQL fetch array returning all the result rows
// in an associative array. Compared to the PHP mysql_fetch_array(),
// it proceeds in a single pass.
function db_fetch_all($sqlResultHandler, $resultType = MYSQL_ASSOC) {
	$rowList = array();

	while( $row = mysql_fetch_array($sqlResultHandler, $resultType) )
	{
		$rowList [] = $row;
	}

	mysql_free_result($sqlResultHandler);

	return $rowList;
}

// Eclass SQL query and fetch array wrapper. It returns all the result rows
// in an associative array.
function db_query_fetch_all($sqlQuery, $db = FALSE) {
	$result = db_query($sqlQuery, $db);

	if ($result) return db_fetch_all($result);
	else         return false;
}


// ----------------------------------------------------------------------
// for safety reasons use the functions below
// ---------------------------------------------------------------------


// Quote string for SQL query
function quote($s) {
	return "'".addslashes($s)."'";
}


// Quote string for SQL query if needed (if magic quotes are on)
function autoquote($s) {
        if (get_magic_quotes_gpc()) {
        	return "'$s'";
        } else {
        	return "'".addslashes($s)."'";
        }
}

// Unquote string if needed (if magic quotes are on)
function autounquote($s) {
        if (get_magic_quotes_gpc()) {
        	return stripslashes($s);
        } else {
        	return $s;
        }
}

// Shortcut for htmlspecialchars()
function q($s)
{
	return htmlspecialchars($s, ENT_QUOTES);
}

/*
* Escapes a string according to the current DBMS's standards
* @param string $str  the string to be escaped
* @return string  the escaped string
* Function Purpose: prepends backslashes to the following characters:
* \x00, \n, \r, \, ', " and \x1a
*/
function escapeSimple($str)
{
	global $db;
	if (get_magic_quotes_gpc())
	{
		return $str;
	}
	else
	{
		if (function_exists('mysql_real_escape_string'))
		{
			return @mysql_real_escape_string($str, $db);
		}
		else
		{
			return @mysql_escape_string($str);
		}
	}
}

function escapeSimpleSelect($str)
{
	if (get_magic_quotes_gpc())
	{
		return addslashes($str);
	}
	else
	{
		return $str;
	}
}


function unescapeSimple($str) {

if (get_magic_quotes_gpc()) {
		return stripslashes($str);
	} else {
	return $str;
	}

}

// ------------------------------------------------------
// Other useful functions. We use it in various scripts.
// -----------------------------------------------------

// Translate uid to username
function uid_to_username($uid)
{
	global $mysqlMainDb;

	if ($r = mysql_fetch_row(db_query(
	"SELECT username FROM user WHERE user_id = '".mysql_real_escape_string($uid)."'",
	$mysqlMainDb))) {
		return $r[0];
	} else {
		return FALSE;
	}
}

// Translate uid to real name / surname
function uid_to_name($uid)
{
	global $mysqlMainDb;

	if ($r = mysql_fetch_row(db_query("SELECT CONCAT(nom, ' ', prenom)
		FROM user WHERE user_id = '".mysql_real_escape_string($uid)."'", $mysqlMainDb))) {
		return $r[0];
	} else {
		return FALSE;
	}
}
// Translate uid to real firstname
function uid_to_firstname($uid)
{
        global $mysqlMainDb;

        if ($r = mysql_fetch_row(db_query("SELECT prenom
		FROM user WHERE user_id = '".mysql_real_escape_string($uid)."'", $mysqlMainDb))) {
                return $r[0];
        } else {
                return FALSE;
        }
}


// Translate uid to real surname
function uid_to_surname($uid)
{
        global $mysqlMainDb;

        if ($r = mysql_fetch_row(db_query("SELECT nom
		FROM user WHERE user_id = '".mysql_real_escape_string($uid)."'", $mysqlMainDb))) {
                return $r[0];
        } else {
                return FALSE;
        }
}

// Translate uid to user email
function uid_to_email($uid)
{
        global $mysqlMainDb;

        if ($r = mysql_fetch_row(db_query("SELECT email
		FROM user WHERE user_id = '".mysql_real_escape_string($uid)."'", $mysqlMainDb))) {
                return $r[0];
        } else {
                return FALSE;
        }
}


// Translate uid to AM (student number)
function uid_to_am($uid)
{
	global $mysqlMainDb;

	if ($r = mysql_fetch_array(db_query("SELECT am from user
		WHERE user_id = '$uid'", $mysqlMainDb))) {
	return $r[0];
		} else {
			return FALSE;
		}
}


// Find a user's group
// If $required == TRUE, show error if user doesn't belong to group
// else returns FALSE;
function user_group($uid, $required = TRUE)
{
	global $currentCourseID;

	$res = db_query("SELECT team FROM user_group WHERE user = '$uid'",
	$currentCourseID);
	if ($res) {
		$secret = mysql_fetch_row($res);
		return $secret[0];
	} else {
		if ($required) {
			die("Error: user tried to submit group work but doesn't belong in a group!");
		} else {
			return FALSE;
		}
	}
}

// find a group name
function gid_to_name($gid)
{
	global $currentCourseID;
	if ($r = mysql_fetch_row(db_query("SELECT name FROM student_group
		WHERE id = '".mysql_real_escape_string($gid)."'", $currentCourseID))) {
                return $r[0];
	} else {
                return FALSE;
	}
}


// Find secret subdir of group gid
function group_secret($gid)
{
	global $currentCourseID;

	$res = db_query("SELECT secretDirectory FROM student_group WHERE id = '$gid'",
	$currentCourseID);
	if ($res) {
		$secret = mysql_fetch_row($res);
		return $secret[0];
	} else {
		die("Error: group $gid doesn't exist");
	}
}


// ------------------------------------------------------------------
// Often useful function (with so many selection boxes in eClass !!)
// ------------------------------------------------------------------


// Show a selection box.
// $entries: an array of (value => label)
// $name: the name of the selection element
// $default: if it matches one of the values, specifies the default entry
// Changed by vagpits
function selection($entries, $name, $default = '', $extra = '')
{
	$retString = "";
	$retString .= "\n<select name='$name' $extra class='auth_input'>\n";
	foreach ($entries as $value => $label) {
		if ($value == $default) {
			$retString .= "<option selected value='" . htmlspecialchars($value) . "'>" .
			htmlspecialchars($label) . "</option>\n";
		} else {
			$retString .= "<option value='" . htmlspecialchars($value) . "'>" .
			htmlspecialchars($label) . "</option>\n";
		}
	}
	$retString .= "</select>\n";
	return $retString;
}

/********************************************************************
Show a selection box. Taken from main.lib.php
Difference: the return value and not just echo the select box

$entries: an array of (value => label)
$name: the name of the selection element
$default: if it matches one of the values, specifies the default entry
 ***********************************************************************/
function selection3($entries, $name, $default = '') {
	$select_box = "<select name='$name'>\n";
	foreach ($entries as $value => $label)  {
	    if ($value == $default) {
		$select_box .= "<option selected value='" . htmlspecialchars($value) . "'>" .
				htmlspecialchars($label) . "</option>\n";
		}  else {
		$select_box .= "<option value='" . htmlspecialchars($value) . "'>" .
				htmlspecialchars($label) . "</option>\n";
		}
	}
	$select_box .= "</select>\n";

	return $select_box;
}


// --------------------------------------------------------------------------
// The check_admin() function is used in the very first place in all scripts in the admin
// directory. Just checks that we are really admin users (and not fake!) to proceed...
// ----------------------------------------------------------------------------
function check_admin() {

	global $uid;
	// just make sure that the $uid variable isn't faked
	if (isset($_SESSION['uid'])) $uid = $_SESSION['uid'];
	else unset($uid);

	if (isset($uid)) {
		$res = db_query("SELECT * FROM admin WHERE idUser='$uid'");
	}
	if (!isset($uid) or !$res or mysql_num_rows($res) == 0) {
		return false;
	} else return true;
}


// ------------------------------------------
// function to check if user is a guest user
// ------------------------------------------

function check_guest() {
	global $mysqlMainDb, $uid;
	if (isset($uid)) {
		$res = db_query("SELECT statut FROM user WHERE user_id = '$uid'", $mysqlMainDb);
		$g = mysql_fetch_row($res);

		if ($g[0] == 10) {
			return true;
		} else {
			return false;
		}
	}
}

// ---------------------------------------------------------------------
// function to check that we are really a professor (and not fake!).
// It is used in various scripts
// --------------------------------------------------------------------

// check if a user is professor

function check_prof()
{
	global $mysqlMainDb, $uid, $require_current_course, $is_adminOfCourse;
	if (isset($uid)) {
                if (isset($require_current_course) and $is_adminOfCourse) {
                        return true;
                }
		$res = db_query("SELECT statut FROM user WHERE user_id='$uid'", $mysqlMainDb);
		$s = mysql_fetch_array($res);
		if ($s['statut'] == 1)
		return true;
		else
		return false;
	}

}


// ---------------------------------------------------
// just make sure that the $uid variable isn't faked
// --------------------------------------------------

function check_uid() {

	global $urlServer, $require_valid_uid, $uid;

	if (isset($_SESSION['uid']))
	$uid = $_SESSION['uid'];
	else
	unset($uid);

	if ($require_valid_uid and !isset($uid)) {
		header("Location: $urlServer");
		exit;
	}

}
// -------------------------------------------------------
// Check if a user with username $login already exists
// ------------------------------------------------------

function user_exists($login) {
  global $mysqlMainDb;

  $username_check = mysql_query("SELECT username FROM `$mysqlMainDb`.user
	WHERE username='".mysql_real_escape_string($login)."'");
  if (mysql_num_rows($username_check) > 0)
    return TRUE;
  else
    return FALSE;
}

// Convert HTML to plain text

function html2text ($string)
{
	$trans_tbl = get_html_translation_table (HTML_ENTITIES);
	$trans_tbl = array_flip ($trans_tbl);

	$text = preg_replace('/</',' <',$string);
	$text = preg_replace('/>/','> ',$string);
	$desc = html_entity_decode(strip_tags($text));
	$desc = preg_replace('/[\n\r\t]/',' ',$desc);
	$desc = preg_replace('/  /',' ',$desc);

	return $desc;
	//    return strtr (strip_tags($string), $trans_tbl);
}

/*
// IMAP authentication functions                                        |
*/

function imap_auth($server, $username, $password)
{
	$auth = FALSE;
	$fp = fsockopen($server, 143, $errno, $errstr, 10);
	if ($fp) {
		fputs ($fp, "A1 LOGIN ".imap_literal($username).
		" ".imap_literal($password)."\r\n");
		fputs ($fp, "A2 LOGOUT\r\n");
		while (!feof($fp)) {
			$line = fgets ($fp,200);
			if (substr($line, 0, 5) == 'A1 OK') {
				$auth = TRUE;
			}
		}
		fclose ($fp);
	}
	return $auth;
}

function imap_literal($s)
{
	return "{".strlen($s)."}\r\n$s";
}


// -----------------------------------------------------------------------------------
// checking the mysql version
// note version_compare() is used for checking the php version but works for mysql too
// ------------------------------------------------------------------------------------

function mysql_version() {
	$ver = mysql_get_server_info();
	if (version_compare("4.1", $ver) <= 0)
	return true;
	else
	return false;
}


/**
 * @param $text
 * @return $text
 * @author Patrick Cool <patrick.cool@UGent.be>
 * @version June 2004
 * @desc apply parsing to content to parse tex commandos that are seperated by [tex][/tex] to make itreadable for techexplorer plugin.
*/
function parse_tex($textext)
{
	$textext=str_replace("[tex]","<EMBED TYPE='application/x-techexplorer' TEXDATA='",$textext);
	$textext=str_replace("[/tex]","' width='100%'>",$textext);
	return $textext;
}


// --------------------------------------
// Useful functions for creating courses
// -------------------------------------

// Returns the code of a faculty given its name
function find_faculty_by_name($name) {
	$code = mysql_fetch_row(db_query("SELECT code FROM faculte
		WHERE name = '$name'"));
	if (!$code) {
		return FALSE;
	} else {
		return $code[0];
	}
}

// Returns the name of a faculty given its code or its name
function find_faculty_by_id($id) {
	$req = mysql_query("SELECT name FROM faculte WHERE id = $id");
	if ($req and mysql_num_rows($req)) {
		$fac = mysql_fetch_row($req);
		return $fac[0];
	} else {
		$req = mysql_query("SELECT name FROM faculte WHERE name = '" . addslashes($id) ."'");
		if ($req and mysql_num_rows($req)) {
			$fac = mysql_fetch_row($req);
			return $fac[0];
		}
	}
	return false;
}

// Returns next available code for a new course in faculty with id $fac
function new_code($fac) {
	global $mysqlMainDb;

	mysql_select_db($mysqlMainDb);
	$gencode = mysql_fetch_row(db_query("SELECT code, generator
		FROM faculte WHERE id = $fac"));
	do {
		$code = $gencode[0].$gencode[1];
		$gencode[1] += 1;
		db_query("UPDATE $mysqlMainDb.faculte SET generator = '$gencode[1]'
			WHERE id = '$fac'");
	} while (mysql_select_db($code));
	mysql_select_db($mysqlMainDb);

	// Make sure the code returned isn't empty!
	if (empty($code)) {
		die("Course Code is empty!");
	}

	return $code;
}

// due to a bug (?) to php function basename() our implementation
// handles correct multibyte characters (e.g. greek)
function my_basename($path) {
	return preg_replace('#^.*/#', '', $path);
}


// transform the date format from "date year-month-day" to "day-month-year"
function greek_format($date) {

	return implode("-",array_reverse(explode("-",$date)));
}

// format the date according to language
function nice_format($date) {

	if ($GLOBALS['language'] == 'greek')
		return greek_format($date);
	else
		return $date;

}

// creating passwords automatically
function create_pass() {

	$parts = array('a', 'ba', 'fa', 'ga', 'ka', 'la', 'ma', 'xa',
                       'e', 'be', 'fe', 'ge', 'ke', 'le', 'me', 'xe',
                       'i', 'bi', 'fi', 'gi', 'ki', 'li', 'mi', 'xi',
                       'o', 'bo', 'fo', 'go', 'ko', 'lo', 'mo', 'xo',
                       'u', 'bu', 'fu', 'gu', 'ku', 'lu', 'mu', 'xu',
                       'ru', 'bur', 'fur', 'gur', 'kur', 'lur', 'mur',
                       'sy', 'zy', 'gy', 'ky', 'tri', 'kro', 'pra');
        $max = count($parts) - 1;
        $num = rand(10,499);
        return $parts[rand(0,$max)] . $parts[rand(0,$max)] . $num;
}


// Returns user's previous login date, or today's date if no previous login
function last_login($uid)
{
        global $mysqlMainDb;

        $q = mysql_query("SELECT DATE_FORMAT(MAX(`when`), '%Y-%m-%d') FROM loginout
                          WHERE id_user = $uid AND action = 'LOGIN'");
        list($last_login) = mysql_fetch_row($q);
        if (!$last_login) {
                $last_login = date('Y-m-d');
        }
        return $last_login;
}


// check for new announcements
function check_new_announce() {
        global $uid;

        $lastlogin = last_login($uid);
        $q = mysql_query("SELECT * FROM annonces, cours_user
                          WHERE annonces.cours_id = cours_user.cours_id AND
                                cours_user.user_id = $uid AND
                                annonces.temps >= '$lastlogin'
                          ORDER BY temps DESC LIMIT 1");
        if ($q and mysql_num_rows($q) > 0) {
                return true;
        } else {
                return false;
        }
}


// Create a JavaScript-escaped mailto: link
function mailto($address, $alternative='(e-mail address hidden)')
{
        if (empty($address)) {
                echo '&nbsp;';
        } else {
                $prog = urlenc("var a='" . urlenc(str_replace('@', '&#64;', $address)) .
                      "';document.write('<a href=\"mailto:'+unescape(a)+'\">'+unescape(a)+'</a>');");
                return "<script type='text/javascript'>eval(unescape('" .
                      $prog . "'));</script><noscript>$alternative</noscript>";
        }
}


function urlenc($string)
{
        $out = '';
        for ($i = 0; $i < strlen($string); $i++) {
                $out .= sprintf("%%%02x", ord(substr($string, $i, 1)));
        }
        return $out;
}


/*
 * Get user data on the platform
 * @param $user_id integer
 * @return  array( `user_id`, `lastname`, `firstname`, `username`, `email`, `picture`, `officialCode`, `phone`, `status` ) with user data
 * @author Mathieu Laurent <laurent@cerdecam.be>
 */

function user_get_data($user_id)
{
	global $mysqlMainDb;
	mysql_select_db($mysqlMainDb);

    $sql = 'SELECT  `user_id`,
                    `nom` AS `lastname` ,
                    `prenom`  AS `firstname`,
                    `username`,
                    `email`,
                    `phone` AS `phone`,
                    `statut` AS `status`
		      	FROM   `user`
		            WHERE `user_id` = "' . (int) $user_id . '"';
    $result = db_query($sql);

    if (mysql_num_rows($result)) {
        $data = mysql_fetch_array($result);
        return $data;
    }
    else
    {
        return null;
    }
}


//function pou epistrefei tyxaious xarakthres. to orisma $length kathorizei to megethos tou apistrefomenou xarakthra
function randomkeys($length)
{
	$key = "";
	$pattern = "1234567890abcdefghijklmnopqrstuvwxyz";
	for($i=0;$i<$length;$i++)
	{
		$key .= $pattern{rand(0,35)};
	}
	return $key;

}

// A helper function, when passed a number representing KB,
// and optionally the number of decimal places required,
// it returns a formated number string, with unit identifier.
function format_bytesize ($kbytes, $dec_places = 2)
{
	global $text;
	if ($kbytes > 1048576) {
		$result  = sprintf('%.' . $dec_places . 'f', $kbytes / 1048576);
		$result .= '&nbsp;Gb';
	} elseif ($kbytes > 1024) {
		$result  = sprintf('%.' . $dec_places . 'f', $kbytes / 1024);
		$result .= '&nbsp;Mb';
	} else {
		$result  = sprintf('%.' . $dec_places . 'f', $kbytes);
		$result .= '&nbsp;Kb';
	}
	return $result;
}


// used in documents and group documents path navigation bar
function make_clickable_path($dbTable, $path)
{
	global $langRoot, $userGroupId;

        if (isset($userGroupId)) {
                $base = $_SERVER['PHP_SELF'] . '?userGroupId=' . $userGroupId . '&amp;';
        } else {
                $base = $_SERVER['PHP_SELF'] . '?';
        }

	$cur = '';
	$out = '';
	foreach (explode('/', $path) as $component) {
		if (empty($component)) {
			$out = "<a href='{$base}openDir=/'>$langRoot</a>";
		} else {
			$cur .= rawurlencode("/$component");
			$row = mysql_fetch_array(db_query ("SELECT filename FROM $dbTable
					WHERE path LIKE '%$component'"));
			$dirname = $row['filename'];
			$out .= " &raquo; <a href='{$base}openDir=$cur'>$dirname</a>";
		}
	}
	return $out;
}



/*
 * Checks if Javascript is enabled on the client browser
 * A cookie is set on the header by javascript code.
 * If this cookie isn't set, it means javascript isn't enabled.
 *
 * return boolean enabling state of javascript
 * author Hugues Peeters <hugues.peeters@claroline.net>
 */

function is_javascript_enabled()
{
        return isset($_COOKIE['javascriptEnabled'])
                and $_COOKIE['javascriptEnabled'];
}

function add_check_if_javascript_enabled_js()
{
        return '<script type="text/javascript">document.cookie="javascriptEnabled=true";</script>';
}



/*
 * check extension and  write  if exist  in a  <LI></LI>
 * @params string	$extensionName 	name  of  php extension to be checked
 * @params boolean	$echoWhenOk	true => show ok when  extension exist
 * @author Christophe Gesche
 * @desc check extension and  write  if exist  in a  <LI></LI>
 */
function warnIfExtNotLoaded($extensionName) {

	global $tool_content, $langModuleNotInstalled, $langReadHelp, $langHere;
	if (extension_loaded ($extensionName)) {
		$tool_content .= "<li> $extensionName - <b>ok!</b> </li> ";
	} else {
		$tool_content .= "
                <li>$extensionName
                <font color=\"#FF0000\"> - <b>$langModuleNotInstalled</b></font>
                (<a href=\"http://www.php.net/$extensionName\" target=_blank>$langReadHelp $langHere)</a>
                </li>";
	}
}


/*
 * to create missing directory in a gived path
 *
 * @returns a resource identifier or FALSE if the query was not executed correctly.
 * @author KilerCris@Mail.com original function from  php manual
 * @author Christophe Gesche gesche@ipm.ucl.ac.be Claroline Team
 * @since  28-Aug-2001 09:12
 * @param sting		$path 		wanted path
 */
function mkpath($path)  {

	$path = str_replace("/","\\",$path);
	$dirs = explode("\\",$path);
	$path = $dirs[0];
	for($i = 1;$i < count($dirs);$i++)
	{
		$path .= "/".$dirs[$i];
		if(!is_dir($path))
		{
			mkdir($path, 0755);
		}
	}
}


// checks if a module is visible
function display_activation_link($module_id) {

	global $currentCourseID;

	$v = mysql_fetch_array(db_query("SELECT lien FROM accueil
		WHERE id ='$module_id'", $currentCourseID));
	$newlien = str_replace("../..","","$v[lien]");

	if (strpos($_SERVER['PHP_SELF'],$newlien) === FALSE) {
		return FALSE;
	} else {
		return TRUE;
	}
}

// checks if a module is visible
function visible_module($module_id) {

	global $currentCourseID;

	$v = mysql_fetch_array(db_query("SELECT visible FROM accueil
		WHERE id ='$module_id'", $currentCourseID));

	if ($v['visible'] == 1) {
		return TRUE;
	} else {
		return FALSE;
	}
}


// Returns true if a string is invalid UTF-8
function invalid_utf8($s)
{
        return !@iconv('UTF-8', 'UTF-32', $s);
}


function utf8_to_cp1253($s)
{
	// First try with iconv() directly
        $cp1253 = @iconv('UTF-8', 'Windows-1253', $s);
        if ($cp1253 === false) {
        	// ... if it fails, fall back to indirect conversion
		$cp1253 = str_replace("\xB6", "\xA2", @iconv('UTF-8', 'ISO-8859-7', $s));
	}
        return $cp1253;
}


// Converts a string from Code Page 737 (DOS Greek) to UTF-8
function cp737_to_utf8($s)
{
        // First try with iconv()...
        $cp737 = @iconv('CP737', 'UTF-8', $s);
        if ($cp737 !== false) {
                return $cp737;
        } else {
                // ... if it fails, fall back to manual conversion
                return strtr($s,
                        array("\x80" => 'Ξ', "\x81" => 'Ξ', "\x82" => 'Ξ', "\x83" => 'Ξ',
                              "\x84" => 'Ξ', "\x85" => 'Ξ', "\x86" => 'Ξ', "\x87" => 'Ξ',
                              "\x88" => 'Ξ', "\x89" => 'Ξ', "\x8a" => 'Ξ', "\x8b" => 'Ξ',
                              "\x8c" => 'Ξ', "\x8d" => 'Ξ', "\x8e" => 'Ξ', "\x8f" => 'Ξ ',
                              "\x90" => 'Ξ‘', "\x91" => 'Ξ£', "\x92" => 'Ξ�', "\x93" => 'Ξ�',
                              "\x94" => 'Ξ¦', "\x95" => 'Ξ§', "\x96" => 'Ξ¨', "\x97" => 'Ξ©',
                              "\x98" => 'Ξ±', "\x99" => 'Ξ²', "\x9a" => 'Ξ³', "\x9b" => 'Ξ΄',
                              "\x9c" => 'Ξ΅', "\x9d" => 'ΞΆ', "\x9e" => 'Ξ·', "\x9f" => 'ΞΈ',
                              "\xa0" => 'ΞΉ', "\xa1" => 'ΞΊ', "\xa2" => 'Ξ»', "\xa3" => 'ΞΌ',
                              "\xa4" => 'Ξ½', "\xa5" => 'ΞΎ', "\xa6" => 'ΞΏ', "\xa7" => 'Ο',
                              "\xa8" => 'Ο', "\xa9" => 'Ο', "\xaa" => 'Ο', "\xab" => 'Ο',
                              "\xac" => 'Ο', "\xad" => 'Ο', "\xae" => 'Ο', "\xaf" => 'Ο',
                              "\xb0" => 'β', "\xb1" => 'β', "\xb2" => 'β', "\xb3" => 'β',
                              "\xb4" => 'β�', "\xb5" => 'β‘', "\xb6" => 'β’', "\xb7" => 'β',
                              "\xb8" => 'β', "\xb9" => 'β£', "\xba" => 'β', "\xbb" => 'β',
                              "\xbc" => 'β', "\xbd" => 'β', "\xbe" => 'β', "\xbf" => 'β',
                              "\xc0" => 'β', "\xc1" => 'β΄', "\xc2" => 'β¬', "\xc3" => 'β',
                              "\xc4" => 'β', "\xc5" => 'βΌ', "\xc6" => 'β', "\xc7" => 'β',
                              "\xc8" => 'β', "\xc9" => 'β', "\xca" => 'β©', "\xcb" => 'β¦',
                              "\xcc" => 'β ', "\xcd" => 'β', "\xce" => 'β¬', "\xcf" => 'β§',
                              "\xd0" => 'β¨', "\xd1" => 'β�', "\xd2" => 'β�', "\xd3" => 'β',
                              "\xd4" => 'β', "\xd5" => 'β', "\xd6" => 'β', "\xd7" => 'β«',
                              "\xd8" => 'β�', "\xd9" => 'β', "\xda" => 'β', "\xdb" => 'β',
                              "\xdc" => 'β', "\xdd" => 'β', "\xde" => 'β', "\xdf" => 'β',
                              "\xe0" => 'Ο', "\xe1" => 'Ξ¬', "\xe2" => 'Ξ­', "\xe3" => 'Ξ�',
                              "\xe4" => 'Ο', "\xe5" => 'Ξ―', "\xe6" => 'Ο', "\xe7" => 'Ο',
                              "\xe8" => 'Ο', "\xe9" => 'Ο', "\xea" => 'Ξ', "\xeb" => 'Ξ',
                              "\xec" => 'Ξ', "\xed" => 'Ξ', "\xee" => 'Ξ', "\xef" => 'Ξ',
                              "\xf0" => 'Ξ', "\xf1" => 'Β±', "\xf2" => 'β�', "\xf3" => 'β�',
                              "\xf4" => 'Ξ�', "\xf5" => 'Ξ«', "\xf6" => 'Γ·', "\xf7" => 'β',
                              "\xf8" => 'Β°', "\xf9" => 'β', "\xfa" => 'Β·', "\xfb" => 'β',
                              "\xfc" => 'βΏ', "\xfd" => 'Β²', "\xfe" => 'β ', "\xff" => 'Β '));
        }
}


// Return a new random filename, with the given extension
function safe_filename($extension = '')
{
        $prefix = sprintf('%08x', time()) . randomkeys(4);
        if (empty($extension)) {
                return $prefix;
        } else {
                return $prefix . '.' . $extension;
        }
}


// Wrap each $item with single quote
function wrap_each(&$item)
{
    $item = "'$item'";
}


// Remove whitespace from start and end of string and convert
// sequences of whitespace characters to single spaces
function canonicalize_whitespace($s)
{
        return preg_replace('/[ \t\n\r\0\x0B]+/', ' ', trim($s));
}


# Only languages defined below are available for selection in the UI
# If you add any new languages, make sure they are defined in the
# next array as well
$native_language_names_init = array (
	'el' => 'Ελληνικά',
	'en' => 'English',
	'es' => 'Español',
	'cs' => 'Česky',
	'sq' => 'Shqip',
	'bg' => 'Български',
	'ca' => 'Català',
	'da' => 'Dansk',
	'nl' => 'Nederlands',
	'fi' => 'Suomi',
	'fr' => 'Français',
	'de' => 'Deutsch',
	'is' => 'Íslenska',
	'it' => 'Italiano',
	'jp' => '日本語',
	'pl' => 'Polski',
	'ru' => 'Русский',
	'tr' => 'Türkçe',
);

$language_codes = array(
	'el' => 'greek',
	'en' => 'english',
	'es' => 'spanish',
	'cs' => 'czech',
	'sq' => 'albanian',
	'bg' => 'bulgarian',
	'ca' => 'catalan',
	'da' => 'danish',
	'nl' => 'dutch',
	'fi' => 'finnish',
	'fr' => 'french',
	'de' => 'german',
	'is' => 'icelandic',
	'it' => 'italian',
	'jp' => 'japanese',
	'pl' => 'polish',
	'ru' => 'russian',
	'tr' => 'turkish',
);

// Convert language code to language name in English lowercase (for message files etc.)
// Returns 'english' if code is not in array
function langcode_to_name($langcode)
{
        global $language_codes;
        if (isset($language_codes[$langcode])) {
		return $language_codes[$langcode];
	} else {
		return 'english';
	}
}

// Convert language name to language code
function langname_to_code($langname)
{
        global $language_codes;
        $langcode = array_search($langname, $language_codes);
        if ($langcode) {
		return $langcode;
	} else {
		return 'en';
	}
}


function append_units($amount, $singular, $plural)
{
	if ($amount == 1) {
		return $amount . ' ' . $singular;
	} else {
		return $amount . ' ' . $plural;
	}
}


function format_time_duration($sec)
{
        global $langsecond, $langseconds, $langminute, $langminutes, $langhour, $langhours;

        if ($sec < 60) {
                return append_units($sec, $langsecond, $langseconds);
        }
        $min = floor($sec / 60);
        $sec = $sec % 60;
        if ($min < 2) {
                return append_units($min, $langminute, $langminutes) .
                       (($sec == 0)? '': (', ' . append_units($sec, $langsecond, $langseconds)));
        }
        if ($min < 60) {
                return append_units($min, $langminute, $langminutes);
        }
        $hour = floor($min / 60);
        $min = $min % 60;
        return append_units($hour, $langhour, $langhours) .
               (($min == 0)? '': (', ' . append_units($min, $langminute, $langminutes)));
}

// Return the URL for a video found in $table (video or videolinks)
function video_url($table, $url, $path)
{
        if ($table == 'video') {
                return $GLOBALS['urlServer'] . 'modules/video/video.php?action2=download&amp;id=' . $path;
        } else {
                return $url;

        }
}

// Move entry $id in $table to $direction 'up' or 'down', where
// order is in field $order_field and id in $id_field
// Use $condition as extra SQL to limit the operation
function move_order($table, $id_field, $id, $order_field, $direction, $condition = '')
{
        if ($condition) {
                $condition = ' AND ' . $condition;
        }
        if ($direction == 'down') {
                $op = '>';
                $desc = '';
        } else {
                $op = '<';
                $desc = 'DESC';
        }
        $sql = db_query("SELECT `$order_field` FROM `$table`
                         WHERE `$id_field` = '$id'");
        if (!$sql or mysql_num_rows($sql) == 0) {
                return false;
        }
        list($current) = mysql_fetch_row($sql);
        $sql = db_query("SELECT `$id_field`, `$order_field` FROM `$table`
                        WHERE `order` $op '$current' $condition
                        ORDER BY `$order_field` $desc LIMIT 1");
        if ($sql and mysql_num_rows($sql) > 0) {
                list($next_id, $next) = mysql_fetch_row($sql);
                db_query("UPDATE `$table` SET `$order_field` = $next
                          WHERE `$id_field` = $id");
                db_query("UPDATE `$table` SET `$order_field` = $current
                          WHERE `$id_field` = $next_id");
                return true;
        }
        return false;
}

// Add a link to the appropriate course unit if the page was requested
// with a unit=ID parametre. This happens if the user got to the module
// page from a unit resource link. If entry_page == TRUE this is the initial page of module
// and is assumed that you're exiting the current unit unless $_GET['unit'] is set
function add_units_navigation($entry_page = FALSE)
{
        global $navigation, $cours_id, $is_adminOfCourse, $mysqlMainDb;
        if ($entry_page and !isset($_GET['unit'])) {
		unset($_SESSION['unit']);
		return FALSE;
	} elseif (isset($_GET['unit']) or isset($_SESSION['unit'])) {
                if ($is_adminOfCourse) {
                        $visibility_check = '';
                } else {
                        $visibility_check = "AND visibility='v'";
                }
		if (isset($_GET['unit'])) {
			$unit_id = intval($_GET['unit']);
		} elseif (isset($_SESSION['unit'])) {
			$unit_id = intval($_SESSION['unit']);
		}
                $q = db_query("SELECT title FROM course_units
                       WHERE id=$unit_id AND course_id=$cours_id " .
                       $visibility_check, $mysqlMainDb);
                if ($q and mysql_num_rows($q) > 0) {
                        list($unit_name) = mysql_fetch_row($q);
                        $navigation[] = array("url"=>"../units/index.php?id=$unit_id", "name"=> htmlspecialchars($unit_name));
                }
		return TRUE;
	} else {
		return FALSE;
	}
}

// Cut a string to be no more than $maxlen characters long, appending
// the $postfix (default: ellipsis "...") if so
function ellipsize($string, $maxlen, $postfix = '...')
{
        if (mb_strlen($string, 'UTF-8') > $maxlen) {
                return (mb_substr($string, 0, $maxlen, 'UTF-8')) . $postfix;
        } else {
                return $string;
        }
}

// Find the title of a course from its code
function course_code_to_title($code)
{
        global $mysqlMainDb;
        $r = db_query("SELECT intitule FROM cours WHERE code='$code'", $mysqlMainDb);
        if ($r and mysql_num_rows($r) > 0) {
                $row = mysql_fetch_row($r);
                return $row[0];
        } else {
                return false;
        }
}


// Find the course id of a course from its code
function course_code_to_id($code)
{
        global $mysqlMainDb;
        $r = db_query("SELECT cours_id FROM cours WHERE code='$code'", $mysqlMainDb);
        if ($r and mysql_num_rows($r) > 0) {
                $row = mysql_fetch_row($r);
                return $row[0];
	} else {
                return false;
	}
}

function csv_escape($string, $force = false)
{
        global $charset;

        if ($charset != 'UTF-8') {
                if ($charset == 'Windows-1253') {
                        $string = utf8_to_cp1253($string);
                } else {
                        $string = iconv('UTF-8', $charset, $string);
                }
        }
        $string = preg_replace('/[\r\n]+/', ' ', $string);
        if (!preg_match("/[ ,!;\"'\\\\]/", $string) and !$force) {
                return $string;
	} else {
                return '"' . str_replace('"', '""', $string) . '"';

	}
}


// Return the value of a key from the config table, or false if not found
function get_config($key)
{
        $r = db_query("SELECT value FROM config WHERE `key` = '$key'");
        if ($r and mysql_num_rows($r) > 0) {
                $row = mysql_fetch_row($r);
                return $row[0];
        } else {
                return false;
        }
}


// Copy variables from $_POST[] to $GLOBALS[], trimming and canonicalizing whitespace
// $var_array = array('var1' => true, 'var2' => false, [varname] => required...)
// Returns true if all vars with required=true are set, false if not
function register_posted_variables($var_array)
{
        $all_set = true;
        foreach ($var_array as $varname => $required) {
                if (isset($_POST[$varname])) {
                        $GLOBALS[$varname] = preg_replace('/ +/', ' ', trim($_POST[$varname]));
                        if ($required and empty($GLOBALS[$varname])) {
                                $all_set = false;
                        }
                } else {
                        $GLOBALS[$varname] = '';
                        $all_set = false;
                }
        }
        return $all_set;
}
