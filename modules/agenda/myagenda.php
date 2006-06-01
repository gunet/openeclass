<?
/*
	This file generates a general agenda of all items of the courses
	the user is registered for;
	based on the master-calendar code of Eric Remy (6 Oct 2003);
	adapted by Toon Van Hoecke (Dec 2003).
*/

//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2003 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: Eric Remy <eremy@rmwc.edu>
//			Toon Van Hoecke <Toon.VanHoecke@UGent.be>
//----------------------------------------------------------------------

$require_login = TRUE;

$langFiles="myagenda";
include '../../include/baseTheme.php';
include('../../include/lib/textLib.inc.php');

$nameTools = $langMyAgenda;
//begin_page();
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

//end_page();

function get_agendaitems($query, $month, $year)
{
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

function display_monthcalendar($agendaitems, $month, $year, $weekdaynames, $monthName, $langToday)
{
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
	$tool_content .=  "<th width=13%><a href=$backwardsURL>&lt;&lt;</a></th>";
	$tool_content .=  "<th width=65%>$monthName $year</td>";
	$tool_content .=  "<th width=13%><a href=$forewardsURL>&gt;&gt;</a></td>";
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
				$class_style = "";
		  		if (($curday==$today['mday'])&&($year ==$today['year'])&&($month == $today['mon']))
				{
		  			$dayheader = "<b><font size=-1 color=#CC3300>$curday - $langToday</font></b>";
		  			$class_style = "class=\"success\"";
				}
	      		$tool_content .=  "<td height=50 width=12%  valign=top $class_style>$dayheader";
				$tool_content .=  "<font size=-2>$agendaitems[$curday]</font></td>\n";
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
