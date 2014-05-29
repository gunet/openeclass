<?php
/*========================================================================
*   Open eClass 2.9
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2014  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* ========================================================================*/

/**
 * @file listusers.php
 * @brief display users list
 */

$require_usermanage_user = TRUE;
include '../../include/baseTheme.php';
include_once '../../modules/auth/auth.inc.php';

if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    // get the incoming values
    $search = isset($_GET['search'])? $_GET['search']:'';
    $c = isset($_GET['c'])? intval($_GET['c']):'';
    $lname = isset($_GET['user_surname'])?$_GET['user_surname']:'';
    $fname = isset($_GET['user_firstname'])?$_GET['user_firstname']:'';
    $uname = isset($_GET['user_username'])?canonicalize_whitespace($_GET['user_username']):'';
    $am = isset($_GET['user_am'])?$_GET['user_am']:'';
    $verified_mail = isset($_GET['verified_mail'])?intval($_GET['verified_mail']):3;
    $user_type = isset($_GET['user_type'])?$_GET['user_type']:'';
    $auth_type = isset($_GET['auth_type']) ? intval($_GET['auth_type']) : '';
    $user_email = isset($_GET['user_email'])?mb_strtolower(trim($_GET['user_email'])):'';
    $user_registered_at_flag = isset($_GET['user_registered_at_flag'])?$_GET['user_registered_at_flag']:'';
    $user_registered_at = isset($_GET['user_registered_at'])?$_GET['user_registered_at']:'';
    $hour = isset($_GET['hour'])?$_GET['hour']:0;
    $minute = isset($_GET['minute'])?$_GET['minute']:0;    
    $mail_ver_required = get_config('email_verification_required');
            
    // pagination
    $limit = intval($_GET['iDisplayLength']);
    $offset = intval($_GET['iDisplayStart']);
    
    /* * *************
      Criteria/Filters
     * ************* */
    $criteria = array();    
    // Registration date/time search
    if (isset($_GET['date'])) {
        $date = explode('-',  $_GET['date']);
        if (count($date) == 3) {
                $day = intval($date[0]);
                $month = intval($date[1]);
                $year = intval($date[2]);
                $user_registered_at = mktime($hour, $minute, 0, $month, $day, $year);
        } else {
                $user_registered_at = mktime($hour, $minute, 0, 0, 0, 0);
        }
    }
    
    if (!empty($user_registered_at_flag) and !empty($user_registered_at)) {
	$user_registered_at_qry = "registered_at " .
                (($user_registered_at_flag == 1)? '>=': '<=') .
                ' ' . $user_registered_at;
	$criteria[] = $user_registered_at_qry;
    }
        
    // nom search
    if (!empty($lname)) {
        $criteria[] = 'nom LIKE ' . quote('%' . $lname . '%');        
    }
    // first name search
    if (!empty($fname)) {
        $criteria[] = 'prenom LIKE ' . quote('%' . $fname . '%');        
    }
    // username search
    if (!empty($uname)) {
        $criteria[] = 'username LIKE ' . quote('%' . $uname . '%');        
    }
    // mail verified
    if ($verified_mail === EMAIL_VERIFICATION_REQUIRED or
        $verified_mail === EMAIL_VERIFIED or
        $verified_mail === EMAIL_UNVERIFIED) {
            $criteria[] = 'verified_mail = ' . $verified_mail;        
    }
    //user am search
    if (!empty($am)) {
        $criteria[] = "am LIKE " . quote('%' . $am . '%');
    }
    // user type search
    if (!empty($user_type)) {
        $criteria[] = "statut = " . $user_type;
    }
    // auth type search
    if (!empty($auth_type)) {
        if ($auth_type >= 2) {
            $criteria[] = 'password = ' . quote($auth_ids[$auth_type]);
        } elseif ($auth_type == 1) {
            $criteria[] = 'password NOT IN (' .
                    implode(', ', $auth_ids) . ')';
        }        
    }
    // email search
    if (!empty($email)) {
        $criteria[] = 'email LIKE ' . quote('%' . $email . '%');
    }
    // search for inactive users
    if ($search == 'inactive') {
        $criteria[] = "expires_at < ".time()." AND user.user_id <> 1";        
    }
    
    if (count($criteria)) {
        $qry_criteria = implode(' AND ', $criteria);
    } else {
        $qry_criteria = '';
    }

    // end filter/criteria
    if ($c) { // users per course
        $qry_base = "FROM user AS a LEFT JOIN cours_user AS b ON a.user_id = b.user_id WHERE b.course_id = $c";
        if ($qry_criteria) {
            $qry_base .= ' AND ' . $qry_criteria;
        }    
        $qry = "SELECT DISTINCT a.user_id, a.nom, a.prenom, a.username, a.email,
                           a.verified_mail, b.statut " . $qry_base;                
    } elseif ($search == 'no_login') { // users who have never logged in
        $qry_base = "FROM user LEFT JOIN loginout ON user.user_id = loginout.id_user WHERE loginout.id_user IS NULL";
        if ($qry_criteria) {
            $qry_base .= ' AND ' . $qry_criteria;
        }   
        $qry = "SELECT DISTINCT user.user_id, nom, prenom, username, email, verified_mail, statut " .
                $qry_base;        
    } else {
        $qry_base = ' FROM user';
        if ($qry_criteria) {
            $qry_base .= ' WHERE ' . $qry_criteria;
        }    
        $qry = 'SELECT DISTINCT user.user_id, nom, prenom, username, email, statut, verified_mail' .
                $qry_base;
    }
    
    // internal search
    if (!empty($_GET['sSearch'])) {
        $keyword = quote('%' . $_GET['sSearch'] . '%');
        if (($qry_criteria) or ($c)) {
            $qry .= ' AND (nom LIKE '.$keyword.' OR prenom LIKE '.$keyword.' OR username LIKE '.$keyword.' OR email LIKE '.$keyword.')';
        } else {
             $qry .= ' WHERE (nom LIKE '.$keyword.' OR prenom LIKE '.$keyword.' OR username LIKE '.$keyword.' OR email LIKE '.$keyword.')';
        }        
    } else {
        $keyword = "'%%'";
    }
    // sorting
    if (!empty($_GET['iSortCol_0'])) {
        switch ($_GET['iSortCol_0']) {
            case '0': $qry .= ' ORDER BY nom ';
                break;
            case '1': $qry .= ' ORDER BY prenom ';
                break;
            case '2': $qry .= ' ORDER BY username ';
                break;
            default: $qry .= ' ORDER BY statut';
        }
        $qry .= $_GET['sSortDir_0'];
    } else {
        $qry .= ' ORDER BY statut, nom';
        $qry .= ' '.$_GET['sSortDir_0'];
    }   
    //pagination
    ($limit > 0) ? $qry .= " LIMIT $offset,$limit" : $qry .= "";
   
    $sql = db_query($qry);

    $all_results = db_query_get_single_value("SELECT COUNT(*) AS total $qry_base");
    if (($qry_criteria) or ($c)) {
        $filtered_results = db_query_get_single_value("SELECT COUNT(*) AS total $qry_base
                                                        AND (nom LIKE $keyword
                                                        OR prenom LIKE $keyword
                                                        OR username LIKE $keyword
                                                        OR email LIKE $keyword)");
    } else {
        $filtered_results = db_query_get_single_value("SELECT COUNT(*) AS total FROM user 
                                                     WHERE (nom LIKE $keyword
                                                        OR prenom LIKE $keyword
                                                        OR username LIKE $keyword
                                                        OR email LIKE $keyword)");
    }
    $data['iTotalRecords'] = $all_results;
    $data['iTotalDisplayRecords'] = $filtered_results;
    $data['aaData'] = array();
    
    while ($logs = mysql_fetch_array($sql)) {
        $email_legend = $logs['email'];
        if ($mail_ver_required) {
            switch($logs['verified_mail']) {
                case EMAIL_VERIFICATION_REQUIRED:
                        $email_icon = "<img align='right' src='$themeimg/pending.png' title='".q($langMailVerificationPendingU)."' alt='".q($langMailVerificationPendingU)."'>";
                        break;
                case EMAIL_VERIFIED:
                        $email_icon = "<img align='right' src='$themeimg/tick_1.png' title='".q($langMailVerificationYesU)."' alt='".q($langMailVerificationYesU)."'>";
                        break;
                case EMAIL_UNVERIFIED:
                        $email_icon = "<img align='right' src='$themeimg/not_confirmed.png' title='".q($langMailVerificationNoU)."' alt='".q($langMailVerificationNoU)."'>";
                        break;
            }
            $email_legend .= ' '.$email_icon;
        }
        
        switch ($logs['statut'])
            {
                case 1:	$statut_icon = "<img src='$themeimg/teacher.png' title='".q($langTeacher)."' alt='".q($langTeacher)."'>";
                    break;
                case 5:	$statut_icon = "<img src='$themeimg/student.png' title='".q($langStudent)."' alt='".q($langStudent)."'>";
                    break;
                case 10: $statut_icon = "<img src='$themeimg/guest.png' title='".q($langVisitor)."' alt='".q($langVisitor)."'>";
                    break;
                default: $statut_icon = $langOther; 
                    break;
            }
                                            
        if ($logs['user_id'] == 1) { // don't display actions for admin user
            $icon_content = "&mdash;&nbsp;";
        } else {
                $icon_content = "<a href='edituser.php?u=".$logs['user_id']."'>
                    <img src='$themeimg/edit.png' title='".q($langEdit)."' alt='".q($langEdit)."'></a>
                    <a href='deluser.php?u=".$logs['user_id']."'>
                    <img src='$themeimg/delete.png' title='".q($langDelete)."' alt='".q($langDelete)."'>
                    </a>
                    <a href='userstats.php?u=".$logs['user_id']."'>
                    <img src='$themeimg/platform_stats.png' title='".q($langStat)."' alt='".q($langStat)."'></a>
                    <a href='change_user.php?username=".urlencode($logs['username'])."'>
                    <img src='$themeimg/log_as.png' title='".q($langChangeUserAs)." ".
                         q($logs['username'])."' alt='".q($langChangeUserAs)." ".
                         q($logs['username'])."'></a>";                        
            }
        $data['aaData'][] = array(
                        '0' => $logs['nom'],
                        '1' => $logs['prenom'],
                        '2' => $logs['username'],
                        '3' => $email_legend,
                        '4' => $statut_icon,
                        '5' => $icon_content
                    );
    }
    echo json_encode($data);
    exit();
}

load_js('tools.js');
load_js('jquery');
load_js('datatables');
load_js('datatables_filtering_delay');
$head_content .= "<script type='text/javascript'>
        $(document).ready(function() {
            $('#search_results_table').DataTable ({            
                'bProcessing': true,
                'bServerSide': true,                
                'sAjaxSource': '$_SERVER[REQUEST_URI]',
                'aLengthMenu': [
                   [10, 15, 20 , -1],
                   [10, 15, 20, '$langAllOfThem'] // change per page values here
                ],
                'sPaginationType': 'full_numbers',
                    'aoColumns': [
                        null,
                        null,
                        null,
                        {'bSortable' : false },
                        {'bSortable' : false },
                        {'bSortable' : false },
                    ],
                    'oLanguage': {                       
                       'sLengthMenu':   '$langDisplay _MENU_ $langResults2',
                       'sZeroRecords':  '".$langNoResult."',
                       'sInfo':         '$langDisplayed _START_ $langTill _END_ $langFrom2 _TOTAL_ $langTotalResults',
                       'sInfoEmpty':    '$langDisplayed 0 $langTill 0 $langFrom2 0 $langResults2',
                       'sInfoFiltered': '',
                       'sInfoPostFix':  '',
                       'sSearch':       '".$langSearch."',
                       'sUrl':          '',
                       'oPaginate': {
                           'sFirst':    '&laquo;',
                           'sPrevious': '&lsaquo;',
                           'sNext':     '&rsaquo;',
                           'sLast':     '&raquo;'
                       }
                   }
            }).fnSetFilteringDelay(1000);
            $('.dataTables_filter input').attr('placeholder', '$langName, $langSurname, $langUsername');
        });
        </script>";

$navigation[] = array("url" => "index.php", "name" => $langAdmin);
$navigation[] = array('url' => 'search_user.php', 'name' => $langSearchUser);
$nameTools = $langListUsersActions;
      
// Display Actions Toolbar
$tool_content .= "
  <div id='operations_container'>
    <ul id='opslist'>
      <li><a href='$_SERVER[SCRIPT_NAME]?search=yes'>$langAllUsers</a></li>      
      <li><a href='$_SERVER[SCRIPT_NAME]?search=inactive'>$langInactiveUsers</a></li>
    </ul>
  </div>";

$tool_content .= "<table id='search_results_table' class='display'>
            <thead>
            <tr>
              <th width='150'>$langSurname</th>
              <th width='100' class='left'>$langName</th>
              <th width='170' class='left'>$langUsername</th>
              <th>$langEmail</th>
              <th>$langProperty</th>
              <th width='130' class='center'>$langActions</th>
            </tr></thead>";
$tool_content .= "<tbody></tbody></table>";

// delete all function
$tool_content .= "<div align='center' style='margin-top: 60px; margin-bottom:10px;'>";
$tool_content .= "<form action='multideluser.php' method='post' name='delall_user_search'>";
// redirect all request vars towards delete all action
foreach ($_GET as $key => $value) {
    $tool_content .= "<input type='hidden' name='$key' value='$value' />";
}
$tool_content .= "<input type='submit' name='dellall_submit' value='".q($langDelList)."'></form>";
$tool_content .= "</div>";

$tool_content .= "<p align='right'><a href='index.php'>$langBack</a></p>";

draw($tool_content, 3, null, $head_content);
