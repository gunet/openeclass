<?
/*
=============================================================================
GUnet e-Class 2.0
E-learning and Course Management Program
================================================================================
Copyright(c) 2003-2006  Greek Universities Network - GUnet
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
General useful functions for e-Class
Standard header included by all e-class files
Defines standard functions and validates variables
---------------------------------------------------------------------
*/

// Show query string and then do MySQL query
function db_query2($sql, $db = FALSE)
{
	echo "<hr><pre>$sql</pre><hr>";
	return db_query($sql, $db);
}

/*
-------------------------------------------------------------------------
it is better to use the function below instead of the usual mysql_query()
first argument: the query
second argument (optional) : the name of the data base
If error happens just display the error and the code
-----------------------------------------------------------------------
*/
// Debug MySQL queries
// commented, not working in all cases
/*
function db_query($sql, $db = FALSE) {
if ($db) {
mysql_select_db($db);
$r = mysql_query($sql,$GLOBALS['db']);
} else {
$r = mysql_query($sql,$GLOBALS['db']);
}
if (mysql_errno()) {
echo '<hr>' . mysql_errno() . ': ' . mysql_error() .
"<br><pre>$sql</pre><hr>";
}
return $r;
}

*/

function db_query($sql, $db = FALSE) {

	if ($db) {
		mysql_select_db($db);
	}
	$r = mysql_query($sql);

	if (mysql_errno()) {
		echo '<hr>' . mysql_errno() . ': ' . mysql_error()
		. "<br><pre>$sql</pre><hr>";
	}
	return $r;
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

// Shortcut for htmlspecialchars()
function q($s)
{
	return htmlspecialchars($s);
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
	global $db;
	if (get_magic_quotes_gpc())
	{
		return addslashes($str);
	}
	else
	{
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

// -------------------------------------------------------------------
// Often useful function (with so many selection boxes in e-Class !!)
// ------------------------------------------------------------------


// Show a selection box.
// $entries: an array of (value => label)
// $name: the name of the selection element
// $default: if it matches one of the values, specifies the default entry
// Changed by vagpits
function selection($entries, $name, $default = '')
{
	$retString = "";
	$retString .= "<select name='$name'>\n";
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



// --------------------------------------------------------------------------
// The check_admin() function is used in the very first place in all scripts in the admin
// directory. Just checks that we are really admin users (and not fake!) to proceed...
// ----------------------------------------------------------------------------
function check_admin() {

	global $uid, $urlServer, $toolContent_ErrorExists;
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
	global $mysqlMainDb, $uid, $urlServer;
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
	global $mysqlMainDb, $uid;
	if (isset($uid)) {
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


// Returns next available code for a new course in faculty with code $fac
function new_code($fac) {
	global $mysqlMainDb;

	mysql_select_db($mysqlMainDb);
	$gencode = mysql_fetch_row(db_query("SELECT code, generator
		FROM faculte WHERE code = '$fac'"));
	do {
		$code = $gencode[0].$gencode[1];
		$gencode[1] += 1;
		db_query("UPDATE $mysqlMainDb.faculte SET generator = '$gencode[1]'
			WHERE code = '$fac'");
	} while (mysql_select_db($code));
	mysql_select_db($mysqlMainDb);

	// Make sure the code returned isn't empty!
	if (empty($code)) {
		die("Course Code is empty!");
	}

	return $code;
}

// due to a bug (?) to php function basename() our implementation
function my_basename($path) {
	return preg_replace('#^.*/#', '', $path);
}


// transform the date format from "date year-month-day" to "day-month-year"
function greek_format($date) {
	return implode("-",array_reverse(split("-",$date)));
}

// creating passwords automatically
function create_pass($length) {
  	$res = "";
	  $PASSCHARS="abcdefghijklmnopqrstuvwxyz023456789";
	  $PASSL = 35;
		srand ((double) microtime() * 1000000);
		 for ($i = 1; $i<=$length ; $i++ ) {
			    $res .= $PASSCHARS[rand(0,$PASSL-1)];
			}
	 return $res;
	}

?>
