<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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
 * My-Agenda Component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract This component generates a month-view agenda of all items of the courses
 *	the user is enrolled in
 *
 */

$require_login = TRUE;
$ignore_module_ini = true;
$require_help = TRUE;
$helpTopic = 'MyAgenda';

include '../../include/baseTheme.php';
include '../../include/lib/textLib.inc.php';

$nameTools = $langMyAgenda;

if (isset($uid)) {
        $year = '';
        $month = '';
 	$query = db_query("SELECT cours.code k, cours.fake_code fc,
                             cours.intitule i, cours.titulaires t, cours.cours_id id
	                        FROM cours, cours_user, modules
	                        WHERE cours.cours_id = cours_user.cours_id
                                AND cours.visible != ".COURSE_INACTIVE."
	                        AND cours_user.user_id = $uid
                                AND modules.module_id  = ".MODULE_ID_AGENDA."
                                AND modules.visible = 1
                                AND modules.course_id = cours.cours_id");
        $today = getdate();
        if (isset($_GET['year'])) {
                $year = intval($_GET['year']);
        } else {
                $year = $today['year'];
        }
        if (isset($_GET['month'])) {
                $month = intval($_GET['month']);
        } else {
                $month = $today['mon'];
        }

	$agendaitems = get_agendaitems($query, $month, $year);
	$monthName = $langMonthNames['long'][$month-1];
	display_monthcalendar($agendaitems, $month, $year, $langDay_of_weekNames['long'], $monthName, $langToday);
}

// -----------------------
// function list
// -----------------------

/*
 * Function get_agendaitems
 *
 * @param resource $query MySQL resource
 * @param string $month
 * @param string $year
 * @return array of agenda items
 */
function get_agendaitems($query, $month, $year) {
	global $urlServer, $mysqlMainDb;
	
        $items = array();
	// get agenda-items for every course
	while ($mycours = mysql_fetch_array($query)) {                      
                $result = db_query("SELECT * FROM agenda WHERE course_id = ". $mycours['id'] ." 
                                        AND MONTH(DAY)='$month' 
                                        AND YEAR(DAY)='$year' 
                                        AND visibility = 'v'", $mysqlMainDb);

                while ($item = mysql_fetch_array($result)) {
                        $URL = $urlServer."modules/agenda/agenda.php?course=".$mycours['k'];
                        $agendadate = explode("-", $item['day']);                            
                        $agendatime = explode(":", $item['hour']);
                        $time = $agendatime[0].":".$agendatime[1];
                        $agendaday = intval($agendadate[2]);                                                
                        $items[$agendaday][$item['hour']] = "<br /><small>($time) <a href='$URL' title='$mycours[i] ($mycours[fc])'>$mycours[i]</a> $item[title]</small>";
                }
	}        

	// sorting by hour for every day
	$agendaitems = array();        
	while (list($agendaday, $tmpitems) = each($items)) {
		sort($tmpitems);
                $agendaitems[$agendaday] = '';
		while (list($key,$val) = each($tmpitems)) {                        
			$agendaitems[$agendaday] .= $val;
		}
	} 
	return $agendaitems;
}

/*
 * Function display_monthcalendar
 *
 * Creates the html content of the agenda module
 *
 * @param array $agendaitems
 * @param string $month
 * @param string $year
 * @param array $weekdaynames days of the week
 * @param string $monthName
 * @param string $langToday
 */
function display_monthcalendar($agendaitems, $month, $year, $weekdaynames, $monthName, $langToday) {
        global $tool_content;
        
	//Handle leap year
	$numberofdays = array(0,31,28,31,30,31,30,31,31,30,31,30,31);
	if (($year%400 == 0) or ($year%4 == 0 and $year%100 <> 0)) {
                $numberofdays[2] = 29;
        }

	//Get the first day of the month
	$dayone = getdate(mktime(0,0,0,$month,1,$year));
  	//Start the week on monday
	$startdayofweek = $dayone['wday']<>0 ? ($dayone['wday']-1) : 6;

	$backwardsURL = "$_SERVER[PHP_SELF]?month=".($month==1 ? 12 : $month-1)."&amp;year=".($month==1 ? $year-1 : $year);
	$forewardsURL = "$_SERVER[PHP_SELF]?month=".($month==12 ? 1 : $month+1)."&amp;year=".($month==12 ? $year+1 : $year);

	$tool_content .= "<table width=100% class=\"title1\">\n";
  	$tool_content .= "<tr>";
	$tool_content .= "<td width='250'><a href=$backwardsURL>&laquo;</a></td>";
	$tool_content .= "<td class='center'><b>$monthName $year</b></td>";
	$tool_content .= "<td width='250' class='right'><a href=$forewardsURL>&raquo;</a></td>";
	$tool_content .= "</tr>";
	$tool_content .= "</table><br />";
	$tool_content .= "<table width=100% class='tbl_1'><tr>";
	for ($ii=1; $ii<8; $ii++) {
                $tool_content .=  "<th class='center'>".$weekdaynames[$ii%7]."</th>";
	}
	$tool_content .= "</tr>";
	$curday = -1;
	$today = getdate();
    
	while ($curday <= $numberofdays[$month]) {
  		$tool_content .= "<tr>";
                
                for ($ii=0; $ii<7; $ii++) {
	  		if (($curday == -1) && ($ii==$startdayofweek)) {
                                $curday = 1;
			}
			if (($curday>0) && ($curday<=$numberofdays[$month])) {
				$bgcolor = $ii<5 ? "class='cautionk'" : "class='odd'";
				$dayheader = "$curday";
				$class_style = "class=odd";
		  		if (($curday==$today['mday']) && ($year ==$today['year']) && ($month == $today['mon']))
				{
		  			$dayheader = "<b>$curday</b> <small>($langToday)</small>";
		  			$class_style = "class='today'";		  			
				}
                                $tool_content .= "<td height=50 width=14% valign=top $class_style><b>$dayheader</b>";
                                if (!empty($agendaitems[$curday])) {
                                        $tool_content .= "$agendaitems[$curday]</td>";
                                }
                                $curday++;
                        } else {
                                $tool_content .= "<td width=14%>&nbsp;</td>";
                        }
		}
                $tool_content .= "</tr>";
        }
  	$tool_content .= "</table>";
        draw($tool_content, 1);
}