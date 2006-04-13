<?
 $msgAdminPanel = "Administrator Panel";
 $msgStats = "Statistics";
 $msgStatsBy = "Statistics by";
 $msgHours = "hours";
 $msgDay = "day";
 $msgWeek = "week";
 $msgMonth = "month";
 $msgYear = "year";
 $msgFrom = "from ";
 $msgTo = "to ";
 $msgPreviousDay = "previous day";
 $msgNextDay = "next day";
 $msgPreviousWeek = "previous week";
 $msgNextWeek = "next week";
 $msgCalendar = "calendar";
 $msgShowRowLogs = "show row logs";
 $msgRowLogs = "row logs";
 $msgRecords = "records";
 $msgDaySort = "Day sort";
 $msgMonthSort = "Monthly sort";
 $msgCountrySort = "Country sort";
 $msgOsSort = "OS sort";
 $msgBrowserSort = "Browser sort";
 $msgProviderSort = "Provider sort";
 $msgTotal = "Total";
 $msgBaseConnectImpossible = "Unable to select SQL base";
 $msgSqlConnectImpossible = "SQL server connection impossible";
 $msgSqlQuerryError = "SQL querry impossible";
 $msgBaseCreateError = "An error occure when attempting to create ezboo base";
 $msgMonthsArray = array("january","february","march","april","may","june","july","august","september","october","november","december");
 $msgDaysArray = array("Sunday","Monday","Tuesday","Wenesday","Thursday","Friday","Saturday");
 $msgDaysShortArray=array("S","M","T","W","T","F","S");
 $msgToday = "Today";
 $msgOther = "Other";
 $msgUnknown = "Unknown";
 $msgServerInfo = "php Server info";
 $msgStatBy = "Statistics by";
 $msgVersion = "Webstats 1.30";
 $msgCreateCook = "<b>Administrator:</b> A cookie has been created on your computer,<BR>
     You will not appear anymore in your logs.<br><br><br><br>";
 $msgCreateCookError = "<b>Administrator:</b> cookie could not be saved on your computer.<br>
     Check your browser settings and refresh page.<br><br><br><br>";
 $msgInstalComments = "<p>The automatic install procedure will attempt to:</p>
       <ul>
         <li>create a table named <b>liste_domaines</b> in your SQL base<br>
           </b>This table will be automatically filled with country names with InterNIC
           codes</li>
         <li>create a table named <b>logezboo</b><br>
           This table will store your logs</li>
       </ul>
       <font color=\"#FF3333\">You must have modified manually:<ul><li><b>config_sql.php3</b> file with your <b>login</b>, <b>password</b> and <b>base name</b> for SQL sever connexion.</li><br><li>The file <b>config.inc.php3</b> must have been modified to select apropriate language.</font></li></ul><br>To do so, you can you anykind of text editor (such as Notepad).";
 $msgInstallAbort = "SETUP ABORTED";
 $msgInstall1 = "If there is no error message above, installation is successfull.";
 $msgInstall2 = "2 tables have been created in your SQL base";
 $msgInstall3 = "You can now open the main interface";
 $msgInstall4 = "In order to fill your table when pages are loaded, you must put a tag in monitored pages.";

 $msgUpgradeComments ="This new version of ezBOO WebStats uses the same table <b>logezboo</b> as previous 
  						versions.<br>
  						If countries are not written in english, you must erase table <b>liste_domaine</b> 
  						et launch setup.<br>
  						This will have no effect on the table <b>logezboo</b> .<br>
  						Error message is normal. :-)";


$langStats="Statistics";
?>