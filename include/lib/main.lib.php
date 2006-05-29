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
     +----------------------------------------------------------------------+
     | General useful functions for e-Class                                        |
     +----------------------------------------------------------------------+
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

// ------------------------------------------------------
// Other useful functions. We use it in various scripts.
// -----------------------------------------------------


// Translate uid to username
function uid_to_username($uid)
{
	global $mysqlMainDb;

	if ($r = mysql_fetch_row(db_query(
			"SELECT username FROM user WHERE user_id = '$uid'",
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
						FROM user WHERE user_id = '$uid'", $mysqlMainDb))) {
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
function check_admin()
{
        global $uid;
        if (isset($uid)) {
                $res = mysql_query("SELECT * FROM admin WHERE idUser='$uid'");
        }
        if (!isset($uid) or !$res or mysql_num_rows($res) == 0) {
                ?>
<center><h1>Χώρος Ελεγχόμενης Πρόσβασης</h1>
<font face="arial, helvetica" size=2>
<p>Η σελίδα που προσπαθείτε να μπείτε απαιτεί δικαιώματα διαχειριστή.<br>
Παρακαλούμε πηγαίνετε στην <a href=../index.php>αρχική σελίδα</a> και
δώστε τα στοιχεία σας.</p></font>
</center>
                <?
                exit();
        }
}


// ------------------------------------------
// function to check if user is a guest user
// ------------------------------------------

function check_guest() {
	global $mysqlMainDb, $uid;

	$res = db_query("SELECT statut FROM user WHERE user_id = '$uid'", $mysqlMainDb);
	$g = mysql_fetch_row($res);

	if ($g[0] == 10) {
      echo "<center><br><br><b>Χώρος Ελεγχόμενης Πρόσβασης</b><br><br><br>
		  <font face=\"arial, helvetica\" size=2>Ο λογαριασμός Επισκέπτη που έχετε δεν σας δίνει αυτό το δικαίωμα.<br>
			Επικοινωνήστε με τον διδάσκοντα του μαθήματος για την απόκτηση λογαριασμού κανονικού χρήστη.<br>
			Επιστροφή στην <a href=\"../../index.php\">αρχική σελίδα</a><br>
			</center>";
			exit();
	}																																																				
}

// ---------------------------------------------------------------------
// function to check that we are really a professor (and not fake!). 
// It is used in various scripts 
// --------------------------------------------------------------------

// check if a user is professor 

function check_prof()
{
	global $uid;
	if (isset($uid)) {
		$res = db_query("SELECT statut FROM user WHERE user_id='$uid'");
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


/**
 * handling simple and double apostrofe in order that strings be stored properly in database
 *
 * @author Denes Nagy
 * @param  string variable - the variable to be revised
 */

function domesticate($input) {
  $input = stripslashes($input);
  $input = str_replace("'","''",$input);
//  $input = str_replace('"',"''",$input);
  return($input);
}


// ------------------------------------------------------------------------------
//  Below there are some IMAP authenticated functions. They have used in one special adaptation
// of the platform.
// ------------------------------------------------------------------------------

/*
     +----------------------------------------------------------------------+
     | IMAP authentication functions                                        |
     +----------------------------------------------------------------------+
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

?>
