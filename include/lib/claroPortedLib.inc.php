<?php

/**
 * Display a date at localized format
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @param formatOfDate
         see http://www.php.net/manual/en/function.strftime.php
         for syntax to use for this string
         I suggest to use the format you can find in trad4all.inc.php files
 * @param timestamp timestamp of date to format
 */

function claro_disp_localised_date($formatOfDate,$timestamp = -1) //PMAInspiration :)
{
    global $langMonthNames, $langDay_of_weekNames;

    if ($timestamp == -1) $timestamp = claro_time();

    // avec un ereg on fait nous même le replace des jours et des mois
    // with the ereg  we  replace %aAbB of date format
    //(they can be done by the system when  locale date aren't aivailable

    $date = ereg_replace('%[A]', $langDay_of_weekNames['long'][(int)strftime('%w', $timestamp)], $formatOfDate);
    $date = ereg_replace('%[a]', $langDay_of_weekNames['short'][(int)strftime('%w', $timestamp)], $date);
    $date = ereg_replace('%[B]', $langMonthNames['long'][(int)strftime('%m', $timestamp)-1], $date);
    $date = ereg_replace('%[b]', $langMonthNames['short'][(int)strftime('%m', $timestamp)-1], $date);
    return strftime($date, $timestamp);
}

/**
 * Get user data on the platform
 *
 * @param $user_id integer
 *
 * @return  array( `user_id`, `lastname`, `firstname`, `username`, `email`, `picture`, `officialCode`, `phone`, `status` ) with user data
 *
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */

function user_get_data($user_id)
{
	global $mysqlMainDb;
	
	mysql_select_db($mysqlMainDb);
	
    // user table
    //$tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_user      = 'user';

    $sql = 'SELECT  `user_id`,
                    `nom`         AS `lastname` ,
                    `prenom`      AS `firstname`,
                    `username`                  ,
                    `email`                     ,
                    `phone` AS `phone`    ,
                    `statut`      AS `status`
            FROM   `' . $tbl_user . '`
            WHERE  `user_id` = "' . (int) $user_id . '"';

    $result = db_query($sql);

    if ( mysql_num_rows($result) )
    {
        $data = mysql_fetch_array($result);
        return $data;
    }
    else
    {
        return null;
    }
}

?>