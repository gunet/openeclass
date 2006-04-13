<?php

##################################################################################
# This function will output in html format a table
# with a title ($title) and a list of Items with corresponding
# bar graph representing percentage
# $val must be an array such as:
#    [Total] => 20
#    [Win95] => 12
#    [Win98] => 7
#    [WinNT] => 1
##################################################################################
function ProcessBarGraph($title, $val) {
 echo "<table width=\"290\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\" class=\"normal\">";
 echo "<tr bgcolor=\"#CCCCCC\">";
 echo "<td colspan=\"2\"><b>".$title."</b></td>";
 echo "<td width=\"0\">"."Hits"."</td>";
 echo "<td>"."%"."</td>";
 echo "</tr>";
 echo "<tr>";

 $sta = true;

 # Find max of percentage
 $temparray = $val;
 $temparray['Total'] = 0;
 if ($val["Total"]>0) $maxpcbar = 100 * max($temparray) / $val["Total"];

 while (($bar=each($val)) && ($val['Total']<> 0)) {
   if ($bar[0]<>"Total") {
      if ($sta) { $color = "#CCCCFF" ; } else { $color = "#FFFFCC" ;}
      $pcbar = round(100 * $bar[1] / $val["Total"]);
      $barwidth = round(100 * $pcbar / $maxpcbar);
      echo "<tr>";
      # title
      echo "<td width=\"180\"  bgcolor=\"$color\">".$bar[0]."</td>";
      # List of stuff
      echo "<td width=\"170\"  bgcolor=\"$color\">";
      # Start of bar graph
      print("<img src=\"../../images/bar_1.gif\" width=\"1\" height=\"12\" alt=\"$bar[1] hits  -  $pcbar %\">");
      # Bar graph itself
      print("<img src=\"../../images/bar_1u.gif\" width=\"$barwidth\" height=\"12\" alt=\"$bar[1] hits  -  $pcbar %\">");
      # End of bar graph
      print("<img src=\"../../images/bar_1.gif\" width=\"1\" height=\"12\" alt=\"$bar[1] hits  -  $pcbar %\">");
      echo "</td>";

      echo "<td  bgcolor=\"$color\">".$bar[1]."</td>";
      echo "<td bgcolor=\"$color\">".$pcbar."%</td>";

      echo "</tr>";
      $sta = !$sta;
   }
 }
    # print total
   echo "<tr bgcolor=\"#CCCCCC\">";
   echo "<td colspan=\"4\">Συνολικά: ".$val['Total']."</td>";
   echo "<tr>";

 echo "</table>";
} # end of fucntion ProcessBarGraph

##################################################################################
function MonthSort($table_log, $reqdate) {

global $currentCourseID;

$monthsArray = array("Ιανουάριος","Φεβρουάριος","Μάρτιος","Απρίλιος","Μάιος","Ιούνιος","Ιούλιος","Αύγουστος","Σεπτέμβριος","Οκτώβριος","Νοέμβριος","Δεκέμβριος");

 $result = db_query ("SELECT UNIX_TIMESTAMP(date) FROM $table_log order by date ASC", $currentCourseID);
 $val_array = array("Total"=>0);
 $last_date_array = array(0,0,0,0,0,0,0,0,0,0);
 $nb_lastval = 1;
 $today_array = getdate($reqdate);
 $i = 0;
 while ($row = mysql_fetch_array ($result)) {
    $date_array = getdate($row[0]);
    if ($date_array["year"] == $today_array["year"]) {
              if (  (@$last_date_array["mon"] == $date_array["mon"])  ) {
                 $nb_lastval += 1;
              } else {
                         if ($i<>0) {
                           $val_array = $val_array + array($monthsArray[$last_date_array["mon"]-1]." ".$last_date_array["year"]=>$nb_lastval);
                           $nb_lastval=1;
                         }
                     }
              $last_date_array = $date_array;
              $i += 1;
    }  # end if year
 } # end while

 $val_array = $val_array + array($monthsArray[$last_date_array["mon"]-1]." ".$last_date_array["year"]=>$nb_lastval);
 $val_array["Total"] = $i;
 mysql_free_result ($result);
 return $val_array;
}

##################################################################################
function DaySort($table_log, $reqdate, $period) {
global $currentCourseID;
 # select what to search for depending on selected period
 switch ($period) {
    case "semaine":
        $q_string = "SELECT UNIX_TIMESTAMP(date) FROM $table_log WHERE WEEK(date)=WEEK(FROM_UNIXTIME('$reqdate')) order by date ASC";
        $result = db_query ($q_string, $currentCourseID);
        break;
    case "mois":
        $q_string = "SELECT UNIX_TIMESTAMP(date) FROM $table_log WHERE MONTH(date)=MONTH(FROM_UNIXTIME('$reqdate')) order by date ASC";
        $result = db_query ($q_string, $currentCourseID);
       break;
 }

 $val_array = array("Total"=>0);
 $last_date_array = array(0,0,0,0,0,0,0,0,0,0);
 $nb_lastval = 1;
 $today_array = getdate($reqdate);
 $i = 0;
 while ($row = mysql_fetch_array ($result)) {
    $date_array = getdate($row[0]);
    if ($date_array["year"] == $today_array["year"]) {
       if ($date_array["mon"] == $today_array["mon"]) {
              if ((@$last_date_array["mday"] == $date_array["mday"])  ) {
                 $nb_lastval += 1;
              } else {
                         if ($i<>0) {
                           $val_array = $val_array + array($last_date_array["mday"]."/".$last_date_array["mon"]."/".$last_date_array["year"]=>$nb_lastval);
                           $nb_lastval=1;
                         }
                     }
              $last_date_array = $date_array;
              $i += 1;
       }  # end if mon
    }  # end if year
 } # end while
 $val_array = $val_array + array(@$last_date_array["mday"]."/".@$last_date_array["mon"]."/".@$last_date_array["year"]=>$nb_lastval);
 $val_array["Total"] = $i;
 mysql_free_result ($result);
 return $val_array;
}

##################################################################################
//TR function HourSort($table_log, $today) {
function HourSort($table_log, $reqdate) {

global $currentCourseID;
 
 $result = db_query ("SELECT UNIX_TIMESTAMP(date) FROM $table_log WHERE DAYOFYEAR(date)=DAYOFYEAR(FROM_UNIXTIME($reqdate)) order by date ASC", $currentCourseID);

 $val_array = array("Total"=>0);
 $last_date_array = array(0,0,0,0,0,0,0,0,0,0);
 $nb_lastval = 1;
 $today_array = getdate($reqdate);

 $i = 0;
 while ($row = mysql_fetch_array ($result)) {
    $date_array = getdate($row[0]);
    if ($date_array["year"] == $today_array["year"]) {
       if ($date_array["mon"] == $today_array["mon"]) {
          if ($date_array["mday"] == $today_array["mday"]) {
              if (@($last_date_array["hours"] == $date_array["hours"])  ) {
                 $nb_lastval += 1;
              } else {
                         if ($i<>0) {
                           $val_array = $val_array + array($last_date_array["hours"]." h"=>$nb_lastval);
                           $nb_lastval=1;
                         }
                     }
              $last_date_array = $date_array;
              $i += 1;
          }  # end if mday
       }  # end if mon
    }  # end if year
 } # end while

 $val_array = $val_array + array(@$last_date_array["hours"]." h"=>$nb_lastval);
 $val_array["Total"] = $i;
 mysql_free_result ($result);
 return $val_array;
}

##################################################################################
# This function will class a given collumn $field
# $field must be equal to a column field (i.e.: provider)
# function output a array like for os:
#    [Total] => 20
#    [Win95] => 12
#    [Win98] => 7
#    [WinNT] => 1
##################################################################################
function ClassAndCountField($table_log, $field,$reqdate,$period) {
global $currentCourseID;

 # select what to search for depending on selected period
 switch ($period) {
    case "jour":
        $q_string = "SELECT $field FROM $table_log WHERE DAYOFYEAR(date)=DAYOFYEAR(FROM_UNIXTIME('$reqdate')) order by $field ASC";
        $result = db_query ($q_string, $currentCourseID);
        break;
    case "semaine":
        $q_string = "SELECT $field FROM $table_log WHERE WEEK(date)=WEEK(FROM_UNIXTIME('$reqdate')) order by $field ASC";
        $result = db_query ($q_string, $currentCourseID);
        break;
    case "mois":
        $q_string = "SELECT $field FROM $table_log WHERE MONTH(date)=MONTH(FROM_UNIXTIME('$reqdate')) order by $field ASC";
        $result = db_query ($q_string, $currentCourseID);
       break;
 }

 $val_array = array("Total"=>0);
 $lastval = "xxx";
 $i = 0;
 $nb_lastval = 1;

 while ($row = mysql_fetch_array ($result)) {
    if ($lastval == $row[$field]) {
      $nb_lastval += 1;
    } else {
         if ($i<>0) {
            $val_array = $val_array + array("$lastval"=>$nb_lastval);
            $nb_lastval=1;
         }
      }
    $lastval = $row[$field];
    $i += 1;
 }
 $val_array = $val_array + array("$lastval"=>$nb_lastval);
 $val_array["Total"] = $i;
 arsort($val_array);
 mysql_free_result ($result);
 return $val_array;
}
##################################################################################
# This function will attempt to fill empty field in table_log such as
# country, provider, os, wb
##################################################################################
function ProcessEmptyEntry($table_log) {

global $currentCourseID;	
	
 # Fill country column
 $result = db_query ("SELECT * from $table_log WHERE country='' ", $currentCourseID);
 while ($row = mysql_fetch_array ($result)) {
    $newval=ExtractCountry($row['host']);
    $req = db_query("UPDATE $table_log SET country='$newval' where id='$row[id]' ", $currentCourseID);
 }
 # Fill provider column
 $result = db_query ("SELECT * from $table_log WHERE provider='' ", $currentCourseID);
 while ($row = mysql_fetch_array ($result)) {
    $newval=ExtractProvider($row['host'],$row['address']);
    $req = db_query("UPDATE $table_log SET provider='$newval' where id='$row[id]' ", $currentCourseID);
 }
 # Fill os column
 $result = db_query ("SELECT * from $table_log WHERE os='' ", $currentCourseID);
 while ($row = mysql_fetch_array ($result)) {
    list($wb,$newval)=split(";",ExtractAgent($row['agent']));
    $req = db_query("UPDATE $table_log SET os='$newval' where id='$row[id]' ", $currentCourseID);
 }

 # Fill wb column
 $result = db_query ("SELECT * from $table_log WHERE wb='' ", $currentCourseID);
 while ($row = mysql_fetch_array ($result)) {
    list($newval,$os)=split(";",ExtractAgent($row['agent']));
    $req = db_query("UPDATE $table_log SET wb='$newval' where id='$row[id]' ", $currentCourseID);
 }
 mysql_free_result ($result);
 # mysql_free_result ($req);
 return True;
} # end of function ProcessEmptyEntry

##################################################################################
# This function will clear : country, provider, os, wb
##################################################################################
function ClearAll($table_log) {

global $currentCourseID;
 
 $req = db_query("UPDATE $table_log SET country=''", $currentCourseID);
 $req = db_query("UPDATE $table_log SET provider=''", $currentCourseID);
 $req = db_query("UPDATE $table_log SET os=''", $currentCourseID);
 $req = db_query("UPDATE $table_log SET wb=''", $currentCourseID);
 return True;
}  # end of ClearAll


##################################################################################
# Use this function to extract domain name from a Host name
#  $hst should be equal to @getHostByAddr($REMOTE_ADDR)
#  for exemple www.ezboo.fr will return ezboo.fr
# but www.ezboo.co.jp will return ezboo.co.jp
##################################################################################
function ExtractProvider($hst, $addressip) {

global $currentCourseID;

 $hst_array = split("[.]",$hst);
 $prov = $hst_array[sizeof($hst_array)-2].'.'.$hst_array[sizeof($hst_array)-1];
 if ($hst != $addressip) {
   if ($prov == "co.jp" or $prov == "co.uk" ) return $hst_array[sizeof($hst_array)-3].".".$prov;
     else return $prov ;
 } else return $msgOther;
}  #end of ExtractProvider

##################################################################################
# Use this function to extract country name from a Host name
#  $hst should be equal to @getHostByAddr($REMOTE_ADDR)
#  for exemple www.ezboo.fr will return France
##################################################################################
function ExtractCountry($hst) {

global $currentCourseID;

 $hst_array = split("[.]",$hst);
 $lastval = $hst_array[sizeof($hst_array)-1];  # last value in host name
 $table_dom = "liste_domaines";
 $req2 = db_query("select description from $table_dom where domaine='$lastval' ", $currentCourseID);

 # Check that extension exist and return country name
 # Otherwise return Unknown
 if (mysql_numrows($req2) > 0)
   return mysql_result($req2,0);
   else return $msgUnknown;
 mysql_close ($c2);
}  # end of  ExtractCountry

##################################################################################
### Fonction de correction du nom de l'agent (navigateur et OS)
### $agt should be HTTP_USER_AGENT
### Output syntax is a string like WebBrowser;OS
### You can extract OS and WebBrowser using:
### list($wb,$os)=split(";",ExtractAgent($HTTP_USER_AGENT));
### echo $wb."<br>";
### echo $os."<br>";
##################################################################################
function ExtractAgent($agt) {

global $currentCourseID;
  # List of all OS like it appear in logs
  $allOS1 = Array("Win98" , "Windows 98",  "Windows 95",  "Win95",  "WinNT",  "Windows NT",  "Linux", "SunOS",  "PPC",     "PowerPC",  "FreeBSD", "AIX", "IRIX", "HP-UX", "OS/2", "NetBSD");
  # List of all OS as we want to appear (label)
  $allOS2 = Array("Win 98", "Win 98",      "Win 95",      "Win 95", "Win NT", "Win NT",      "Linux", "Sun OS", "Mac PPC", "Mac PPC",  "FreeBSD", "AIX", "IRIX", "HP-UX", "OS/2", "NetBSD");
  # Value return if browser and OS are unknow
  $unagt = "άγνωστο;άγνωστο";
  ######################
  # Internet Explorer
  ######################
  if (ereg("MSIE", $agt)) {
    $new_agt = "IE";
    $agt = strtr($agt, "_", " ");
    # List of all web browser versions
    $allVersions = Array("5.5", "5.01", "5.0", "5.0b1", "5.0b2", "4.5", "4.01", "4.0", "3.02", "3.01");
    # Find browser version
    for ($cpt = 0, $ok = false; $cpt < count($allVersions) && !$ok; $cpt++)
      if ($ok = ereg($allVersions[$cpt], $agt)) $new_agt .= " ".$allVersions[$cpt];
    # We have not found WebBrowser
    if (!$ok) {
               $new_agt = $unagt;
               $ok = TRUE;
              }
    if ($ok)
      # find OS
      for ($cpt = 0, $ok = false; $cpt < count($allOS1) && !$ok; $cpt++)
        if ($ok = ereg($allOS1[$cpt], $agt)) $new_agt .= ";".$allOS2[$cpt];
      # We have not found OS
      if (!$ok) $new_agt .= ";".$unagt;

  } elseif (ereg("Opera", $agt)) {
        ###########
        ## OPERA
        ###########
        $new_agt = "OPERA";
        # List of all web browser versions
        $allVersions = Array("4.0", "3.60", "3.62");
        # Find browser version
        for ($cpt = 0, $ok = false; $cpt < count($allVersions) && !$ok; $cpt++)
           if ($ok = ereg($allVersions[$cpt], $agt)) $new_agt .= ";".$allVersions[$cpt];
        # We have not found WebBrowser
        if (!$ok) {
                   $new_agt = $unagt;
                   $ok = TRUE;
                  }
        # Find OS
        if ($ok)
           for ($cpt = 0, $ok = false; $cpt < count($allOS1) && !$ok; $cpt++)
              if ($ok = ereg($allOS1[$cpt], $agt)) $new_agt .= ";".$allOS2[$cpt];
        # We have not found OS
        if (!$ok) $new_agt .= ";".$unagt;

    } elseif (ereg("Mozilla/4.", $agt)) {
         ##############
         ## NETSCAPE
         ##############
        $new_agt = "NS";
        # List of all web browser versions
        $allVersions = Array("4.72", "4.71", "4.7", "4.61", "4.6", "4.51", "4.5", "4.08", "4.07", "4.06", "4.05", "4.04", "4.03");
        # Find browser version
        for ($cpt = 0, $ok = false; $cpt < count($allVersions) && !$ok; $cpt++)
              if ($ok = ereg($allVersions[$cpt], $agt)) $new_agt .= " ".$allVersions[$cpt];
        # We have not found WebBrowser
        if (!$ok) {
                    $new_agt = $unagt;
                    $ok = TRUE;
                  }
        # Find OS
        if ($ok)
             for ($cpt = 0, $ok = false; $cpt < count($allOS1) && !$ok; $cpt++)
                   if ($ok = ereg($allOS1[$cpt], $agt)) $new_agt .= ";".$allOS2[$cpt];
        # We have not found OS
        if (!$ok) $new_agt .= ";".$unagt;


      } elseif (ereg("Mozilla/5.0", $agt)) {
             ######################
             ## NETSCAPE 6 (BETA)
             ######################
             $new_agt = "NS";
             # List of all web browser versions
             $allVersions1 = Array("m14");
             $allVersions2 = Array("6.0");
             # Find browser version
             for ($cpt = 0, $ok = false; $cpt < count($allVersions1) && !$ok; $cpt++)
                   if ($ok = ereg($allVersions1[$cpt], $agt)) $new_agt .= ";".$allVersions2[$cpt];
             # We have not found WebBrowser
             if (!$ok) {
                        $new_agt = $unagt;
                        $ok = TRUE;
                       }
             # Find OS
             if ($ok)
                  for ($cpt = 0, $ok = false; $cpt < count($allOS1) && !$ok; $cpt++)
                        if ($ok = ereg($allOS1[$cpt], $agt)) $new_agt .= ";".$allOS2[$cpt];
             # We have not found OS
            if (!$ok) $new_agt .= ";".$unagt;

        } else { ######################
                 ## Others stuff
                 ######################
                 $new_agt = $agt;
                 $tNav[] = Array("Lynx",      "Lynx;Linux");
                 $tNav[] = Array("WWWOFFLE",  "WWWOFFLE;Linux");
                 $tNav[] = Array("Konqueror", "Konqueror;Linux");

                 for ($cpt = 0, $ok = false; $cpt < count($tNav) && !$ok; $cpt++)
                       if ($ok = ereg($tNav[$cpt][0], $agt)) $new_agt = $tNav[$cpt][1];
                 # We have not found browser
                 if (!$ok) $new_agt = $unagt.";".$unagt;
               }
  return($new_agt);   # Systax is=  WebBrowser;OS
}   # End of function ExtraireAgent
##################################################################################

?>
