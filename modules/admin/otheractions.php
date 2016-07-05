<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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


/**
 * @file otheractions.php
 * @brief display other actions
 */ 
  
$require_admin = TRUE;
require_once '../../include/baseTheme.php';
require_once 'include/log.class.php';

load_js('tools.js');
load_js('datatables');
load_js('bootstrap-datetimepicker');

$head_content .= "<script type='text/javascript'>
        $(document).ready(function() {
            $('#log_results_table').DataTable ({                                
                'sPaginationType': 'full_numbers',
                'bAutoWidth': true,
                'searchDelay': 1000,
                'oLanguage': {
                   'sLengthMenu':   '$langDisplay _MENU_ $langResults2',
                   'sZeroRecords':  '" . $langNoResult . "',
                   'sInfo':         '$langDisplayed _START_ $langTill _END_ $langFrom2 _TOTAL_ $langTotalResults',
                   'sInfoEmpty':    '$langDisplayed 0 $langTill 0 $langFrom2 0 $langResults2',
                   'sInfoFiltered': '',
                   'sInfoPostFix':  '',
                   'sSearch':       '" . $langSearch . "',
                   'sUrl':          '',
                   'oPaginate': {
                       'sFirst':    '&laquo;',
                       'sPrevious': '&lsaquo;',
                       'sNext':     '&rsaquo;',
                       'sLast':     '&raquo;'
                   }
               }
            });
            $('.dataTables_filter input').attr('placeholder', '$langDetail');
        });
        </script>";

$toolName = $langPlatformGenStats;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);

$tool_content .= action_bar(array(
                    array('title' => $langBack,
                        'url' => "index.php",
                        'icon' => 'fa-reply',
                        'level' => 'primary-label')
                    ));

$tool_content .= "<div class='table-responsive'>
                <table class='table-default'>
                    <tr><td><a href='../usage/displaylog.php?from_other=TRUE'>$langSystemActions</a></td></tr>
                    <tr><td><a href='$_SERVER[SCRIPT_NAME]?stats=failurelogin'>$langLoginFailures</a><small> ($langLast15Days)</small></td></tr>
                    <tr><td><a href='$_SERVER[SCRIPT_NAME]?stats=musers'>$langMultipleUsers</a></td></tr>
                    <tr><td><a href='$_SERVER[SCRIPT_NAME]?stats=memail'>$langMultipleAddr e-mail</a></td></tr>
                    <tr><td><a href='$_SERVER[SCRIPT_NAME]?stats=mlogins'>$langMultiplePairs LOGIN - PASS</a></td></tr>
                    <tr><td><a href='$_SERVER[SCRIPT_NAME]?stats=vmusers'>$langMailVerification</a></td></tr>
                    <tr><td><a href='$_SERVER[SCRIPT_NAME]?stats=unregusers'>$langUnregUsers</a><small> ($langLastMonth)</small></td></tr>
                </table>            
            </div>";

// ---------------------
// actions
// ---------------------
if (isset($_GET['stats'])) {
    switch ($_GET['stats']) {
        case 'failurelogin':
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 0;
            $tool_content .= "<br>";
            $date_start = date("Y-m-d", strtotime("-15 days"));
            $date_end = date("Y-m-d", strtotime("+1 days"));
            $page_link = "&amp;stats=failurelogin";
            $log = new Log();
            $log->display(0, 0, 0, LOG_LOGIN_FAILURE, $date_start, $date_end, $_SERVER['SCRIPT_NAME'], $limit, $page_link);
            break;
        case 'unregusers':
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 0;
            $tool_content .= "<br>";
            $date_start = date("Y-m-d", strtotime("-1 month"));
            $date_end = date("Y-m-d", strtotime("+1 days"));
            $page_link = "&amp;stats=unregusers";
            $log = new Log();
            $log->display(0, -1, 0, LOG_DELETE_USER, $date_start, $date_end, $_SERVER['SCRIPT_NAME'], $limit, $page_link);
            break;        
        case 'musers':
            $tool_content .= "<div class='table-responsive'>
                            <table class='table-default'>";
            $loginDouble = list_ManyResult("SELECT DISTINCT username, COUNT(*) AS nb
				FROM user GROUP BY BINARY username HAVING nb > 1 ORDER BY nb DESC", 'username');
            $tool_content .= "<tr class='list-header'><th><b>$langMultipleUsers</b></th>
			<th class='right'><strong>$langResult</strong></th>
			</tr>";
            if (count($loginDouble) > 0) {
                $tool_content .= tablize($loginDouble);
                $tool_content .= "<tr><td class='right' colspan='2'>" . error_message() . "</td></tr>";
            } else {
                $tool_content .= "<tr><td class='right' colspan='2'>" . ok_message() . "</td></tr>";
            }
            $tool_content .= "</table></div>";
            break;        
        case 'memail':
            $sqlLoginDouble = "SELECT DISTINCT email, COUNT(*) AS nb FROM user GROUP BY email
				HAVING nb > 1 ORDER BY nb DESC";
            $loginDouble = list_ManyResult($sqlLoginDouble, 'email');
            $tool_content .= "<div class='table-responsive'>
                            <table class='table-default'>
                            <tr class='list-header'>
                            <th><b>$langMultipleAddr e-mail</b></th>
                            <th class='right'><strong>$langResult</strong></th>
                            </tr>";
            if (count($loginDouble) > 0) {
                $tool_content .= tablize($loginDouble);
                $tool_content .= "<tr><td class=right colspan='2'>";
                $tool_content .= error_message();
                $tool_content .= "</td></tr>";
            } else {
                $tool_content .= "<tr><td class=right colspan='2'>";
                $tool_content .= ok_message();
                $tool_content .= "</td></tr>";
            }
            $tool_content .= "</table></div>";
            break;
        case 'mlogins':
            $sqlLoginDouble = "SELECT DISTINCT CONCAT(username, \" -- \", password) AS pair,
				COUNT(*) AS nb FROM user GROUP BY BINARY pair HAVING nb > 1 ORDER BY nb DESC";
            $loginDouble = list_ManyResult($sqlLoginDouble, 'pair');
            $tool_content .= "<div class='table-responsive'>
                            <table class='table-default'>
                            <tr class='list-header'>
                            <th><b>$langMultiplePairs LOGIN - PASS</b></th>
                            <th class='right'><b>$langResult</b></th>
                            </tr>";
            if (count($loginDouble) > 0) {
                $tool_content .= tablize($loginDouble);
                $tool_content .= "<tr><td class='right' colspan='2'>";
                $tool_content .= error_message();
                $tool_content .= "</td></tr>";
            } else {
                $tool_content .= "<tr><td class='right' colspan='2'>";
                $tool_content .= ok_message();
                $tool_content .= "</td></tr>";
            }
            $tool_content .= "</table></div>";
            break;
        case 'vmusers':
            $tool_content .= "<div class='row'>
                        <div class='col-sm-12'>
                        <div class='content-title h3'>$langUsers</div>
                        <ul class='list-group'>
                        <li class='list-group-item'><label><a href='listusers.php?search=yes&verified_mail=1'>$langMailVerificationYes</a></label>          
                            <span class='badge'>" . Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE verified_mail = " . EMAIL_VERIFIED . ";")->cnt . "</span>
                        </li>
                        <li class='list-group-item'><label><a href='listusers.php?search=yes&verified_mail=2'>$langMailVerificationNo</a></label>                            
                            <span class='badge'>" . Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE verified_mail = " . EMAIL_UNVERIFIED . ";")->cnt . "</span>
                        </li>
                        <li class='list-group-item'><label><a href='listusers.php?search=yes&verified_mail=0'>$langMailVerificationPending</a></label>
                            <span class='badge'>" . Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE verified_mail = " . EMAIL_VERIFICATION_REQUIRED . ";")->cnt . "</span>
                        </li>
                        <li class='list-group-item'><label><a href='listusers.php?search=yes'>$langTotal</a></label>
                            <span class='badge'>" . Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user;")->cnt . "</span>
                        </li>
                        </ul>
                        </div></div>";
            break;
        default:
            break;
    }
}


draw($tool_content, 3, null, $head_content);

/**
 * @brief output a <tr> with an array 
 * @param type $table
 * @return string
 */
function tablize($table) {   
    $ret = "";    
    if (is_array($table)) {
        foreach ($table as $key => $thevalue) {            
            $ret .= "<tr>";            
            $ret .= "<td style='font-size: 90%'>" . $key . "</td>";
            $ret .= "<td class='right'><strong>" . $thevalue . "</strong></td></tr>";
        }
    }
    return $ret;
}

/**
 * @brief ok message 
 * @global type $langNotExist
 * @return type
 */
function ok_message() {
    global $langNotExist;

    return "<div class='text-center not_visible'> - $langNotExist - </div>";
}

/**
 * @brief error message
 * @global type $langExist
 * @return type
 */
function error_message() {
    global $langExist;

    return "<b><span style='color: #FF0000'>$langExist</span></b>";
}

/**
 * 
 * @param type $sql
 * @param type $fieldname
 * @return type
 */
function list_ManyResult($sql, $fieldname) {
    
    $resu = array();
    $res = Database::get()->queryArray($sql);
    foreach ($res as $resA) {     
        $name = $resA->$fieldname;     
        $resu[$name] = $resA->nb;
    }
    return $resu;
}
