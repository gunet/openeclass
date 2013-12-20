<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */
/*
  
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
 *         Regex fixes by Alexandros Diamantidis - Jan 22, 2008
 *
 * Actually this function is taken from the PHP BB 1.4 script
 * - Goes through the given string, and replaces xxxx://yyyy with an HTML <a> tag linking
 * 	to that URL
 * - Goes through the given string, and replaces www.xxxx.yyyy[zzzz] with an HTML <a> tag linking
 * 	to http://www.xxxx.yyyy[/zzzz]
 * - Goes through the given string, and replaces xxxx@yyyy with an HTML mailto: tag linking
 * 		to that email address
 */

function make_clickable($text) {

    // If the user has decided to deeply use html and manage himself
    // hyperlink cancel the make clickable() function and return the text
    // untouched.

    if (preg_match("<(a|img)[[:space:]]*(href|src)[[:space:]]*=(.*)>", $text)) {
        return $text;
    }

    // matches an "xxxx://yyyy" URL
    // xxxx can only be alphanumeric characters
    // yyyy is anything up to the first space, newline, ()<>

    $text = preg_replace("#\b([a-z0-9]+?://[^, \n\r()<>]+)#i", "<a href='$1'>$1</a>", $text);

    // matches a "www.xxxx.yyyy[/zzzz]" kinda lazy URL thing
    // Must contain at least 2 dots. xxxx contains either alphanum, or "-"
    // yyyy contains either alphanum, "-", or "."
    // zzzz is optional.. will contain everything up to the first space, newline, or comma.
    // This is slightly restrictive - it's not going to match stuff like "forums.foo.com"
    // This is to keep it from getting annoying and matching stuff that's not meant to be a link.

    $text = preg_replace("#\b((?<!://)www\.([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,}(/[^, \n\r()<>]*)?)#i", "<a href='http://$1'>$1</a>", $text);

    // matches an email@domain type address

    $text = preg_replace("#\b([0-9a-z_\.\+-]+@([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,})\b#i", "<a href='mailto:$1'>$1</a>", $text);

    return($text);
}

/*
 * formats the date according to the locale settings
 *
 * @author  Christophe Gesche <gesche@ipm.ucl.ac.be>
 *          originally inspired from from PhpMyAdmin
 *
 * @params  string  $formatOfDate date pattern
 * @params  integer $timestamp, default is NOW.
 *
 * @return the formatted date
 *
 */

function claro_format_locale_date($dateFormat, $timeStamp = -1) {
    // Retrieve $langMonthNames and $langDay_of_weekNames

    $langMonthNames = $GLOBALS['langMonthNames'];
    $langDay_of_weekNames = $GLOBALS['langDay_of_weekNames'];

    if ($timeStamp == -1)
        $timeStamp = time();

    // we replace %aAbB of date format
    // (they can be done by the system when locale date aren't available
    $date = preg_replace('/%[A]/', $langDay_of_weekNames['long'][(int) strftime('%w', $timeStamp)], $dateFormat);
    $date = preg_replace('/%[a]/', $langDay_of_weekNames['short'][(int) strftime('%w', $timeStamp)], $date);
    $date = preg_replace('/%[B]/', $langMonthNames['fine'][(int) strftime('%m', $timeStamp) - 1], $date);
    $date = preg_replace('/%[b]/', $langMonthNames['short'][(int) strftime('%m', $timeStamp) - 1], $date);

    return strftime($date, $timeStamp);
}
