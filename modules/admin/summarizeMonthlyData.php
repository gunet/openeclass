<?php

/*
===========================================================================
    admin/summarizeMonthlyData.php
    @last update: 23-09-2006
    @authors list: ophelia neofytou
==============================================================================
    @Description:  Takes general statistics information for each month and inserts
     it to table 'monthly_summary'. Data from 'monthly summary are used for
     monthly reports.
==============================================================================
*/


    //check if data for last month have already been inserted in 'monthly_summary'...
    $lmon = mktime(0, 0, 0, date("m")-1, date("d"),  date("Y"));
    $last_month = date('m Y', $lmon);
    
    $sql = "SELECT id FROM monthly_summary WHERE `month` = '$last_month'";
    $result = db_query($sql, $mysqlMainDb);

    $isReported = 0;
    while ($row = mysql_fetch_assoc($result)) {
        if ($row['id']) {
            $isReported = 1;
        }
    }
    mysql_free_result($result);
    
    //... and if not inserted yet
    if (!$isReported) {
        $current_month = date('Y-m-01 00:00:00');
        $prev_month = date('Y-m-01 00:00:00', $lmon);

        $login_sum=0;
        $cours_sum = 0;
        $prof_sum = 0;
        $stud_sum = 0;
        $vis_sum = 0;
        
        $sql = "SELECT count(idLog) as sum_id FROM loginout WHERE `when` >= '$prev_month' AND `when`< '$current_month' AND action='LOGIN'";
        $result = db_query($sql, $mysqlMainDb);
        while ($row = mysql_fetch_assoc($result)) {
            $login_sum = $row['sum_id'];
           
        }
        
        mysql_free_result($result);
        if (!isset($cours_sum)) {$cours_sum = 0;}

        $sql = "SELECT count(cours_id) as cours_sum FROM cours";
        $result = db_query($sql, $mysqlMainDb);
        while ($row = mysql_fetch_assoc($result)) {
            $cours_sum = $row['cours_sum'];
        }
        mysql_free_result($result);
        if (!isset($cours_sum)) {$cours_sum = 0;}

        $sql = "SELECT count(user_id) as prof_sum FROM user WHERE statut=1";
        $result = db_query($sql, $mysqlMainDb);
        while ($row = mysql_fetch_assoc($result)) {
            $prof_sum = $row['prof_sum'];
        }
        mysql_free_result($result);
        if (!isset($prof_sum)) {$prof_sum = 0;}
        
        
        $sql = "SELECT count(user_id) as stud_sum FROM user WHERE statut=5";
        $result = db_query($sql, $mysqlMainDb);
        while ($row = mysql_fetch_assoc($result)) {
            $stud_sum = $row['stud_sum'];
        }
        mysql_free_result($result);
        if (!isset($stud_sum)) {$stud_sum = 0;}
        
        $sql = "SELECT count(user_id) as vis_sum FROM user WHERE statut=10";
        $result = db_query($sql, $mysqlMainDb);
        while ($row = mysql_fetch_assoc($result)) {
            $vis_sum = $row['vis_sum'];
        }
        mysql_free_result($result);
        if (!isset($vis_sum)) {$vis_sum = 0;}
        
        
        $mtext = "<table>";
        $mtext .= "<tr><th>".$langCourse."</th><th>".$langCoursVisible."</th><th>".$langType."</th><th>".$langDepartment."</th><th>".$langTeacher. "</th><th>".$langNbUsers."</th></tr>";

        
        $sql = "SELECT cours.intitule AS name, cours.visible as visible, cours.type as type, cours.faculte as dept, cours.titulaires as proff, count(user_id) AS cnt FROM cours_user LEFT JOIN cours ON ".
            " cours.code = cours_user.code_cours GROUP BY code_cours ";
        $result = db_query($sql, $mysqlMainDb);
        while ($row = mysql_fetch_assoc($result)) {
            //declare course type
            if ($row['type'] == 'pre') {
              $ctype = $langPre;
            }
            else {
              $ctype = $langPost;
            }
            //declare visibility
            if ($row['visible'] == 0) {
              $cvisible = $langClosed;
            }
            else if ($row['visible']==1) {
              $cvisible = $langTypesRegistration;
            }
            else {
                $cvisible = $langOpen;
            }
            
            $mtext .= "<tr><td>".$row['name']."</td><td> ".$cvisible."</td><td> ".$ctype."</td><td> ".$row['dept']."</td><td>".$row['proff']."</td><td>".$row['cnt']."</td></tr>";
        }
        mysql_free_result($result);
        $mtext .= '</table>';

        $sql = "INSERT INTO monthly_summary SET month='$last_month', profesNum = $prof_sum, studNum = $stud_sum, ".
            " visitorsNum = $vis_sum, coursNum = $cours_sum, logins = $login_sum, details = '$mtext'";
        $result= db_query($sql, $mysqlMainDb);
        @mysql_free_result($result);

    }

  
?>
