<?
 /*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.4.* Lib transform text $Revision$          |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2003 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
 */


/**
 * function make_clickable($text) 
 *
 * @desc   completes url contained in the text with "<a href ...".
 *         However the function simply returns the submitted text without any 
 *         transformation if it already contains some "<a href:" or "<img src=".
 * @params string $text text to be converted
 * @return text after conversion 
 * @author Rewritten by Nathan Codding - Feb 6, 2001.
 *         completed by Hugues Peeters - July 22, 2002
 *
 * Actually this function is taken from the PHP BB 1.4 script
 * - Goes through the given string, and replaces xxxx://yyyy with an HTML <a> tag linking
 * 	to that URL
 * - Goes through the given string, and replaces www.xxxx.yyyy[zzzz] with an HTML <a> tag linking
 * 	to http://www.xxxx.yyyy[/zzzz] 
 * - Goes through the given string, and replaces xxxx@yyyy with an HTML mailto: tag linking
 *		to that email address
 * - Only matches these 2 patterns either after a space, or at the beginning of a line
 *
 * Notes: the email one might get annoying - it's easy to make it more restrictive, though.. maybe
 * have it require something like xxxx@yyyy.zzzz or such. We'll see.
 */

function make_clickable($text)
{

	// If the user has decided to deeply use html and manage himself hyperlink
	// cancel the make clickable() function and return the text untouched.

	if (preg_match ( "<(a|img)[[:space:]]*(href|src)[[:space:]]*=(.*)>", $text) )
	{
		return $text;
	}
	
	// pad it with a space so we can match things at the start of the 1st line.
	$ret = " " . $text;


	// matches an "xxxx://yyyy" URL at the start of a line, or after a space.
	// xxxx can only be alpha characters.
	// yyyy is anything up to the first space, newline, or comma.

	$ret = preg_replace("#([\n ])([a-z]+?)://([^, \n\r]+)#i", 
						"\\1<a href=\"\\2://\\3\" >\\2://\\3</a>", 
						$ret);

	// matches a "www.xxxx.yyyy[/zzzz]" kinda lazy URL thing
	// Must contain at least 2 dots. xxxx contains either alphanum, or "-"
	// yyyy contains either alphanum, "-", or "."
	// zzzz is optional.. will contain everything up to the first space, newline, or comma.
	// This is slightly restrictive - it's not going to match stuff like "forums.foo.com"
	// This is to keep it from getting annoying and matching stuff that's not meant to be a link.

	$ret = preg_replace("#([\n ])www\.([a-z0-9\-]+)\.([a-z0-9\-.\~]+)((?:/[^, \n\r]*)?)#i", 
						"\\1<a href=\"http://www.\\2.\\3\\4\" >www.\\2.\\3\\4</a>", 
						$ret);
	
	// matches an email@domain type address at the start of a line, or after a space.
	// Note: before the @ sign, the only valid characters are the alphanums and "-", "_", or ".".
	// After the @ sign, we accept anything up to the first space, linebreak, or comma.

	$ret = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([^, \n\r]+)#i", 
						"\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", 
						$ret);
	
	// Remove our padding..
	$ret = substr($ret, 1);
	
	return($ret);
}

/**
 * formats the date according to the locale settings
 *
 * @author  Christophe Gesché <gesche@ipm.ucl.ac.be>
 *          originally inspired from from PhpMyAdmin
 *
 * @params  string  $formatOfDate date pattern
 * @params  integer $timestamp, default is NOW.
 *
 * @globals $langMonthNames and $langDay_of_weekNames 
 *          set in lang/.../trad4all.inc.php
 *
 * @return the formatted date
 *
 * @see lang/.../trad4all.inc.php for the locale format
 * @see http://www.php.net/manual/fr/function.strftime.php
 *      to understand the possible date format
 *
 */

function claro_format_locale_date( $dateFormat, $timeStamp = -1)
{
	// Retrieve $langMonthNames and $langDay_of_weekNames 
	// from the approriate lang/*/trad4all.inc.php where they are set

	$langMonthNames	= $GLOBALS['langMonthNames']; 
	$langDay_of_weekNames = $GLOBALS['langDay_of_weekNames'];

	if ($timeStamp == -1) $timeStamp = time();

	// with the ereg  we  replace %aAbB of date format
	//(they can be done by the system when  locale date aren't aivailable
	$date = ereg_replace('%[A]', $langDay_of_weekNames['long'][(int)strftime('%w', $timeStamp)], $dateFormat);
	$date = ereg_replace('%[a]', $langDay_of_weekNames['short'][(int)strftime('%w', $timeStamp)], $date);
	$date = ereg_replace('%[B]', $langMonthNames['fine'][(int)strftime('%m', $timeStamp)-1], $date);
	$date = ereg_replace('%[b]', $langMonthNames['short'][(int)strftime('%m', $timeStamp)-1], $date);

	return strftime($date, $timeStamp);

} // end function claro_format_locale_date



?>
