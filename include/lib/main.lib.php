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

	if (mysql_errno()) {
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


/*
 * Return the $uids of the users participating in a given ($code_cours) course
 * 
 * @param $code_cours code of the course as appears in 'cours_user' table in the main database, e.g. "TMA100"
 *
 * @return array containing the $uids of the users registered as students in the given course. 
 * 
 * @author Sakis Agorastos <thagorastos@gmail.com>
 */
function get_all_uids_of_students_in_course($code_cours)
{
	global $mysqlMainDb;
	
	$sql= "SELECT `user_id` FROM `cours_user` WHERE `code_cours` LIKE '%".$code_cours."%' AND statut='5'";
	$result = db_query($sql, $mysqlMainDb);
	
	$uids = array();
    	while($row = mysql_fetch_array($result))
    	{
	        $uids[] = $row["user_id"];
    	}
    return $uids;
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


// check for new announcements
function check_new_announce() {

  global $uid;

  $row = mysql_fetch_array(mysql_query("SELECT * FROM loginout 
					WHERE id_user='$uid' AND action = 'LOGIN' ORDER BY idLog DESC"));
  $lastlogin = $row['when'];
  $sql = "SELECT * FROM annonces,cours_user
                WHERE annonces.code_cours=cours_user.code_cours
                AND cours_user.user_id='$uid' AND annonces.temps >= '$lastlogin'
                ORDER BY temps DESC";
  if (mysql_num_rows(mysql_query($sql)) > 0)
	  return TRUE;
  else
  	return FALSE;

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
* Default Scoring function
* Goal : compute a default scoring for a grouped multiple choise.
*/

function DefaultScoring($ChoiceCount,$Z,$weight) {

    if ($Z==0)
    {
        $score = 10;
    }
    else{

        $m=20;
        $n=-0.2;
        $o=8;
        $p=-1.3;

        //intermediate computations
        $a=$m*pow($ChoiceCount,$n);
        $b=$o*pow($ChoiceCount,$p);

        //Scoring computation
        $score=(round(($a*exp(-$b*$Z))*2))/2;
    }

    return $score/10*$weight;
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
function make_clickable_path($path)
{
	global $langRoot;

	$cur = '';
	$out = '';
	$base = $_SERVER['PHP_SELF'];
	foreach (explode('/', $path) as $component) {
		if (empty($component)) {
			$out = "<a href='$base?openDir=/'>$langRoot</a>";
		} else {
			$cur .= rawurlencode("/$component");
			$row = mysql_fetch_array(db_query ("SELECT filename FROM document 
					WHERE path LIKE '%$component'"));
			$dirname = $row['filename'];
			//$out .= " &raquo; <a href='$base?openDir=$cur'>$component</a>";
			$out .= " &raquo; <a href='$base?openDir=$cur'>$dirname</a>";
		}
	}
	return $out;
}

?>
