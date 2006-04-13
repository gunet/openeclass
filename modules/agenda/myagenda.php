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

include '../../include/init.php';
include('../../include/lib/textLib.inc.php');

$nameTools = $langMyAgenda;
begin_page();


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

end_page();

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
		        $URL = $urlServer."/".$mycours[k];
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

	echo "<center><table class=\"invertedBg\" width=95% border=\"2\" bordercolor=\"#FFFFFF\" cellspacing=\"0\">\n";//style=\"border-collapse: collapse\"
  	echo "<tr>\n";
	echo "<td width=13%><center><b><a href=$backwardsURL><font color=\"black\">&lt;&lt;</font></a></b></center></td>\n";
	echo "<td width=65%><center><b>$monthName $year</b></center></td>\n";
	echo "<td width=13%><center><b><a href=$forewardsURL><font color=\"black\">&gt;&gt;</font></a></b></center></td>\n";
	echo "</tr>\n";
	echo "</table></center>\n\n";

	echo "<center><table width=95% border=\"0\" cellspacing=\"2\"><tr>\n";
	for ($ii=1;$ii<8; $ii++)
	{
    	echo "<th width=13%><font size=-1>".$weekdaynames[$ii%7]."</font></th>\n";
	    }
	echo "</tr>\n";
	$curday = -1;
	$today = getdate();
	while ($curday <=$numberofdays[$month])
  	{
  		echo "<tr>\n";
      	for ($ii=0; $ii<7; $ii++)
	  	{
	  		if (($curday == -1)&&($ii==$startdayofweek))
			{
	    		$curday = 1;
			}
			if (($curday>0)&&($curday<=$numberofdays[$month]))
			{
				$bgcolor = $ii<5 ? "class=\"alternativeBgLight\"" : "class=\"alternativeBgDark\"";
				$dayheader = "<b><font size=-1>$curday</font></b>";
		  		if (($curday==$today['mday'])&&($year ==$today['year'])&&($month == $today['mon']))
				{
		  			$dayheader = "<b><font size=-1 color=#CC3300>$curday - $langToday</font></b>";
				}
	      		echo "<td height=40 width=12% align=left valign=top $bgcolor>$dayheader";
				echo "<font size=-2>$agendaitems[$curday]</font></td>\n";
	      		$curday++;
	    	}
	  		else
	    	{
	    		echo "<td width=12%>&nbsp;</td>\n";
	    	}
		}
    	echo "</tr>\n";
    }
  	echo "</table></center>\n";
}
?>
