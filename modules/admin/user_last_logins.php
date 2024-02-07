<?php

$require_usermanage_user = TRUE;
require_once '../../include/baseTheme.php';

if (isset($_GET['u'])) {
    $u = intval($_GET['u']);
}

$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'listusers.php', 'name' => $langListUsersActions);

$tool_content .= action_bar(array(
    array('title' => $langBack,
        'url' => "edituser.php?u=$u",
        'icon' => 'fa-reply',
        'level' => 'primary-label')
));

$toolName = "$langUserLastLogins: " . uid_to_name($u);

load_js('datatables');

$head_content .= "<script type='text/javascript'>
        $(document).ready(function() {
            $('#user_last_logins').DataTable ({
                'sPaginationType': 'full_numbers',
                'bAutoWidth': true,
                'searchDelay': 1000,
                'aoColumns': [
                    {'bSortable' : false, 'sWidth': '70%' },
                    {'bSortable' : true },
                    {'bSortable' : false },
                ],
                'order' : [],
                'oLanguage': {
                   'sLengthMenu':   '$langDisplay _MENU_ $langResults2',
                   'sZeroRecords':  '" . $langNoResult . "',
                   'sInfo':         '$langDisplayed _START_ $langTill _END_ $langFrom2 _TOTAL_ $langTotalResults',
                   'sInfoEmpty':    '$langDisplayed 0 $langTill 0 $langFrom2 0 $langResults2',
                   'sInfoFiltered': '',
                   'sInfoPostFix':  '',
                   'sSearch':       '',
                   'sUrl':          '',
                   'oPaginate': {
                       'sFirst':    '&laquo;',
                       'sPrevious': '&lsaquo;',
                       'sNext':     '&rsaquo;',
                       'sLast':     '&raquo;'
                   }
               }
            });
            $('.dataTables_filter input').attr({
                      class : 'form-control input-sm',
                      placeholder : '$langSearch...'
            });                        
        });
</script>";

// fetch user last year login / logout
$result = Database::get()->queryArray("SELECT * FROM loginout WHERE id_user = ?d  
                                        AND `when` >= (NOW() - INTERVAL 1 YEAR)
                                    ORDER by idLog DESC", $u);

if (count($result) > 0) {
    $tool_content .= "<div class='table-responsive'>";
    $tool_content .= "<table id='user_last_logins' class='table-default'><thead><tr class='list-header'>";
    $tool_content .= "<th>$langUserLastLogins <small>($langLastYear)</small></th><th>$langAction</th><th>$langIpAddress</th>";
    $tool_content .= "</tr>";
    $tool_content .= "<tbody>";
    foreach ($result as $lastVisit) {
        $tool_content .= "<tr>";
        $tool_content .= "<td>" . format_locale_date(strtotime($lastVisit->when)) . "</td>";
        $tool_content .= "<td>" . action_text($lastVisit->action) . "</td>";
        $tool_content .= "<td>$lastVisit->ip</td>";
        $tool_content .= "</tr>";
    }
    $tool_content .= "</tbody></table>";
    $tool_content .= "</div>";
} else {
    $tool_content .= "<div class = 'alert alert-info text-center'>$langNoUserLastLogins</div>";
}

function action_text($action) {
    global $langLogin, $langLogout;

    switch ($action) {
        case 'LOGIN': $text = $langLogin;
            break;
        case 'LOGOUT': $text = $langLogout;
            break;
    }
    return $text;
}

draw($tool_content, 3, null, $head_content);
