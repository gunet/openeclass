<?
/**===========================================================================
*              GUnet e-Class 2.0
*       E-learning and Course Management Program
* ===========================================================================
*	Copyright(c) 2003-2006  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
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

/**
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
$langFiles="myagenda";
$ignore_module_ini = true;

include '../../include/baseTheme.php';
include('../../include/lib/textLib.inc.php');

$nameTools = $langMyAgenda;
$tool_content = "";

$TABLECOURS     = "`$mysqlMainDb`.cours";
$TABLECOURSUSER = "`$mysqlMainDb`.cours_user";

if (isset($uid))
{
 	$query = db_query("SELECT cours.code k, cours.fake_code fc,
				cours.intitule i, cours.titulaires t
	                        FROM cours, cours_user
	                        WHERE cours.code = cours_user.code_cours
	                        AND cours_user.user_id = '$uid'");
	@$year = $_GET['year'];
	@$month = $_GET['month'];
	if (($year==NULL)&&($month==NULL))
	{
		$today = getdate();
		$year = $today['year'];
		$month = $today['mon'];
	}

	@$agendaitems = get_agendaitems($query, $month, $year);
	$monthName = $langMonthNames['long'][$month-1];
	@display_monthcalendar($agendaitems, $month, $year, $langDay_of_weekNames['long'], $monthName, 
$langToday);
}

// -----------------------
// function list
// -----------------------

/**
 * Function get_agendaitems
 *
 * @param resource $query MySQL resource
 * @param string $month
 * @param string $year
 * @return array of agenda items
 */
function get_agendaitems($query, $month, $year) {
	global $urlServer;
	$items = array();

	// get agenda-items for every course
	while ($mycours = mysql_fetch_array($query))
	{
	$result = db_query("SELECT * FROM agenda WHERE month(day)='$month' AND year(day)='$year'","$mycours[k]");
		
	    while ($item = mysql_fetch_array($result))
	    {
			$agendadate = explode("-", $item['day']);
			$agendaday = intval($agendadate[2]);
			$agendatime = explode(":", $item['hour']);
			$time = $agendatime[0].":".$agendatime[1];
		        $URL = $urlServer."courses/".$mycours[k];
	    	$items[$agendaday][$item['hour']] .= "<br><i>$agendatime[0]:$agendatime[1]</i>
			 <a href=\"$URL\" title=\"$mycours[i]\">$mycours[fc]</a> $item[titre]";
		}
	}

	// sorting by hour for every day
	$agendaitems = array();
	while (list($agendaday, $tmpitems) = each($items))
	{
		sort($tmpitems);
		while (list($key,$val) = each($tmpitems))
		{
			$agendaitems[$agendaday].=$val;
		}
	}

	return $agendaitems;
}

/**
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
	//Handle leap year
	$numberofdays = array(0,31,28,31,30,31,30,31,31,30,31,30,31);
	if (($year%400 == 0) or ($year%4==0 and $year%100<>0)) $numberofdays[2] = 29;

	//Get the first day of the month
	$dayone = getdate(mktime(0,0,0,$month,1,$year));
  	//Start the week on monday
	$startdayofweek = $dayone['wday']<>0 ? ($dayone['wday']-1) : 6;

	$backwardsURL = "$_SERVER[PHP_SELF]?month=".($month==1 ? 12 : $month-1)."&year=".($month==1 ? $year-1 : $year);
	$forewardsURL = "$_SERVER[PHP_SELF]?month=".($month==12 ? 1 : $month+1)."&year=".($month==12 ? $year+1 : $year);

	$tool_content .=  "<table  width=99% ><thead>";
  	$tool_content .=  "<tr>";
	$tool_content .=  "<th width=12%><a href=$backwardsURL>&lt;&lt;</a></th>";
	$tool_content .=  "<th width=66%>$monthName $year</td>";
	$tool_content .=  "<th width=12%><a href=$forewardsURL>&gt;&gt;</a></td>";
	$tool_content .=  "</tr>";
	$tool_content .=  "</thead></table><br>";

	$tool_content .=  "<table width=99%><thead><tr>\n";
	for ($ii=1;$ii<8; $ii++)
	{
    	$tool_content .=  "<th width=13%>".$weekdaynames[$ii%7]."</th>";
	    }
	$tool_content .=  "</tr></thead><tbody>";
	$curday = -1;
	$today = getdate();
	while ($curday <=$numberofdays[$month])
  	{
  		$tool_content .=  "<tr>";
      	for ($ii=0; $ii<7; $ii++)
	  	{
	  		if (($curday == -1)&&($ii==$startdayofweek))
			{
	    		$curday = 1;
			}
			if (($curday>0)&&($curday<=$numberofdays[$month]))
			{
				$bgcolor = $ii<5 ? "class=\"cautionk\"" : "class=\"odd\"";
				$dayheader = "$curday";
				$class_style = "class=odd";
		  		if (($curday==$today['mday'])&&($year ==$today['year'])&&($month == $today['mon']))
				{
		  			$dayheader = "<b><font color=#CC3300>$curday - $langToday</font></b>";
		  			$class_style = "class=\"success\"";
				}
	      		$tool_content .=  "<td height=50 width=12% valign=top $class_style>$dayheader";
				$tool_content .=  "$agendaitems[$curday]</td>\n";
	      		$curday++;
	    	}
	  		else
	    	{
	    		$tool_content .=  "<td width=12% class=\"inactive\">&nbsp;</td>\n";
	    	}
		}
    	$tool_content .=  "</tr>\n";
    }
  	$tool_content .=  "</table></center>\n";
  	
draw($tool_content, 1);
}

?>
