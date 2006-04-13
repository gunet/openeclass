<?
$require_current_course = TRUE;
$require_login = TRUE;
$langFiles = 'stat';

include '../../include/init.php';
$nameTools = $langStats;
begin_page();

if (!isset($reqdate)) $reqdate = time();

echo "<table>";
echo "<tr><td><font face='arial, helvetica' size='2'>";

 $today = date("Y-m-d" , $reqdate);

 switch ($period) {
    case "jour":
        echo date("d " , $reqdate).$msgMonthsArray[date("n", $reqdate)-1].date(" Y" , $reqdate);
        break;
    case "semaine":
        $weeklowreqdate = ($reqdate-(86400*date("w" , $reqdate)));
        $weekhighreqdate = ($reqdate+(86400*(6-date("w" , $reqdate)) ));
        echo "<b>".$msgFrom."</b>".date("d " , $weeklowreqdate).$msgMonthsArray[date("n", $weeklowreqdate)-1].date(" Y" , $weeklowreqdate);
        echo " <b>".$msgTo."</b>".date("d " , $weekhighreqdate ).$msgMonthsArray[date("n", $weekhighreqdate)-1].date(" Y" , $weekhighreqdate);
        break;
    case "mois":
        echo $msgMonthsArray[date("n", $reqdate)-1].date(" Y", $reqdate);
       break;
 }

echo "</td>";
echo "<td width='300' align='right'><font face='arial, helvetica' size='2'>";

  if ($period == "jour") {
    $ysreqdate = $reqdate - 86400;
    $PHP_SELF2 = basename($_SERVER['PHP_SELF']);
    echo "<a href='$PHP_SELF2?table=$table&reqdate=$ysreqdate&reset=0&period=$period'>$msgPreviousDay <b>&lt;&lt;</b></a>";
    echo "<br>";
    $tmreqdate = $reqdate + 86400;
    echo "<a href='$PHP_SELF2?table=$table&reqdate=$tmreqdate&reset=0&period=$period'>$msgNextDay <b>&gt;&gt;</b></a>";
    echo "<br>";
  }
  if ($period== "semaine") {
    $ysreqdate = $reqdate - 7*86400;
    $PHP_SELF2 = basename($_SERVER['PHP_SELF']);
    echo "<a href='$PHP_SELF2?table=$table&reqdate=$ysreqdate&reset=0&period=$period'>$msgPreviousWeek <b>&lt;&lt;</b></a>";
    echo "<br>";
    $tmreqdate = $reqdate + 7*86400;
    echo "<a href='$PHP_SELF2?table=$table&reqdate=$tmreqdate&reset=0&period=$period'>$msgNextWeek <b>&gt;&gt;</b></a>";
    echo "<br>";
  }
?> </p>
          </td></tr><tr>
          <td colspan="2"><font face="arial, helvetica" size="2">[ <a href="index2.php?table=<?php echo $table ?>&amp;<?echo "reqdate=".time()."&reset=0"."&period=jour"; ?>"><?php echo $msgDay ?></a>
            ]&nbsp;&nbsp; [ <a href="index2.php?table=<?php echo $table ?>&amp;<?echo "reqdate=".time()."&reset=0"."&period=semaine"; ?>"><?php echo $msgWeek ?></a>
            ] &nbsp;&nbsp;[ <a href="index2.php?table=<?php echo $table ?>&amp;<?echo "reqdate=".time()."&reset=0"."&period=mois"; ?>"><?php echo $msgMonth ?></a>
            ]</td>
        </tr>
<?

  include ("log_func.php");

  $table_log = $table;

  print("<tr valign=\"top\"> ");
  print("<td><font face=\"arial, helvetica\" size=\"2\"> ");

  if ($reset) { ClearAll($table_log); }  # clear fields: "country provider os wb" to take into acount modif in log_func (ie: new browser new NIC extention ...)
  ProcessEmptyEntry($table_log);

  $today = date("Y-m-d" , $reqdate);
  $today_2 = getdate($reqdate);


  if ($period == "jour") {
       $valjour = HourSort($table_log, $reqdate );
       $title = $msgDaysArray[$today_2['wday']]." ".$today_2['mday']." ".$msgMonthsArray[$today_2['mon']-1]." ".$today_2['year'];
       ProcessBarGraph($title,$valjour);
       echo "<br>";
  }

  if ($period != "jour") {
       $val = DaySort($table_log, $reqdate, $period );
       ProcessBarGraph($msgDaySort,$val);
       echo "<br>";
  }

  $val = MonthSort($table_log, $reqdate );
  ProcessBarGraph($msgMonthSort,$val);
  echo "<br>";

  $val = ClassAndCountField($table_log, "country",$reqdate,$period);
  ProcessBarGraph($msgCountrySort,$val);
  echo "<br>";
  print("</td>");

  print("<td><font face=\"arial, helvetica\" size=\"2\"> ");

  $val = ClassAndCountField($table_log, "os",$reqdate,$period);
  ProcessBarGraph($msgOsSort,$val);
  echo "<br>";

  $val = ClassAndCountField($table_log, "wb",$reqdate,$period);
  ProcessBarGraph($msgBrowserSort,$val);
  echo "<br>";
  $val = ClassAndCountField($table_log, "provider",$reqdate,$period);
  ProcessBarGraph($msgProviderSort,$val);

?>
      <br></td></tr><tr><td colspan=2><font face="arial, helvetica" size="2">
      <?= $msgStatBy ?> <a href="http://www.ezboo.com" target="_blank">ezBOO</a> <?= $msgVersion ?>
    
<?
	end_page();
?>
