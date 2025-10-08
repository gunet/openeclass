<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

$require_login = true;
$require_current_course = true;

require_once '../../include/baseTheme.php';
require_once 'include/log.class.php';

if (!get_config('course_invitation')) {
    redirect_to_home_page('modules/user/index.php?course=' . $course_code);
}

$up = new Permissions();

if (!$up->has_course_users_permission()) {
    Session::Messages($langCheckCourseAdmin, 'alert-danger');
    redirect_to_home_page('courses/'. $course_code);
}

if (isset($_POST['delete'])) {
    $delete_id = getDirectReference($_POST['delete']);
    Database::get()->query('DELETE FROM course_invitation
        WHERE course_id = ?d AND id = ?d',
        $course_id, $delete_id);
    exit();
}

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && $is_editor) {

    $limit = intval($_GET['iDisplayLength'] ?? 0);
    $offset = intval($_GET['iDisplayStart'] ?? 0);

    if (!empty($_GET['sSearch'])) {
        $search_values = array_fill(0, 3, '%' . $_GET['sSearch'] . '%');
        $search_sql = 'AND (surname LIKE ?s OR givenname LIKE ?s OR email LIKE ?s)';
    } else {
        $search_sql = '';
        $search_values = [];
    }
    $sortDir = ($_GET['sSortDir_0'] == 'desc')? 'DESC': '';
    switch ($_GET['iSortCol_0']) {
        case 0: $sortCol = 'email'; break;
        case 1: $sortCol = 'surname'; break;
        case 2: $sortCol = 'created_at'; break;
        case 3: $sortCol = 'expires_at'; break;
        case 4: $sortCol = 'registered_at'; break;
        default: $sortCol = 'created_at'; break;
    }
    $order_sql = "ORDER BY $sortCol $sortDir";

    $displayFilter = $_GET['displayFilter'] ?? 'all';
    if ($displayFilter == 'unreg') {
        $filter_sql = ' AND registered_at IS NULL';
    } elseif ($displayFilter == 'reg') {
        $filter_sql = ' AND registered_at IS NOT NULL';
    } else {
        $filter_sql = '';
    }

    $limit_sql = ($limit > 0) ? "LIMIT $offset, $limit" : '';

    $all_users = Database::get()->querySingle("SELECT COUNT(*) AS total FROM course_invitation
        WHERE course_id = ?d $filter_sql", $course_id)->total;
    $filtered_users = Database::get()->querySingle("SELECT COUNT(*) AS total FROM course_invitation
        WHERE course_id = ?d $filter_sql $search_sql",
        $course_id, $search_values)->total;
    $result = Database::get()->queryArray("SELECT * FROM course_invitation
        WHERE course_id = ?d $filter_sql $search_sql $order_sql $limit_sql",
        $course_id, $search_values);

    $data['iTotalRecords'] = $all_users;
    $data['iTotalDisplayRecords'] = $filtered_users;
    $data['aaData'] = [];
    foreach ($result as $myrow) {
        $id_indirect = getIndirectReference($myrow->id);
        $full_name = sanitize_utf8($myrow->surname . ' ' . $myrow->givenname);
        $date_field = format_locale_date(strtotime($myrow->created_at), 'medium', false);
        $expiration_field = $myrow->expires_at? format_locale_date(strtotime($myrow->expires_at), 'medium', false): '-';
        if ($myrow->registered_at) {
            $reg_field = format_locale_date(strtotime($myrow->registered_at), 'medium', false);
        } else {
            $reg_field = '-';
        }
        $user_role_controls = action_button([
            [ 'title' => $langDelete,
              'level' => 'primary',
              'url' => '#',
              'icon' => 'fa-times',
              'btn_class' => 'delete_btn deleteAdminBtn' ],
            [ 'title' => $langSendReminder,
              'url' => "invite_one.php?course=$course_code&amp;id=$id_indirect",
              'icon' => 'fa-check-square-o',
              'show' => is_null($myrow->registered_at) ],
        ]);
        $data['aaData'][] = [
            'DT_RowId' => $id_indirect,
            '0' => $myrow->email,
            '1' => $full_name,
            '2' => $date_field,
            '3' => $expiration_field,
            '4' => $reg_field,
            '5' => $user_role_controls ];
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

$toolName = $langCourseUsersInvitation;
load_js('tools.js');
load_js('datatables');
$head_content .= "
  <script type='text/javascript'>
    var displayFilter = 'all';
    $(document).ready(function() {
      var oTable = $('#invite_table{$course_id}').DataTable({
        bProcessing: true,
        bServerSide: true,
        sScrollX: true,
        drawCallback: function(oSettings) {
          tooltip_init();
          popover_init();
        },
        sAjaxSource: '$_SERVER[REQUEST_URI]',
        fnServerParams: function (aoData) {
            aoData.push({name: 'displayFilter', value: displayFilter});
        },
        aLengthMenu: [
          [10, 25, 50, -1],
          [10, 25, 50, '$langAllOfThem'] // change per page values here
        ],
        sPaginationType: 'full_numbers',
        bSort: true,
        aaSorting: [[0, 'desc']],
        oLanguage: {
          sLengthMenu:  '$langDisplay _MENU_ $langResults2',
          zeroRecords: '$langNoResult',
          sInfo:        '$langDisplayed _START_ $langTill _END_ $langFrom2 _TOTAL_ $langTotalResults',
          sInfoEmpty:   '$langDisplayed 0 $langTill 0 $langFrom2 0 $langResults2',
          sInfoFiltered: '',
          sInfoPostFix: '',
          sSearch:    '',
          sUrl:     '',
          oPaginate: {
            sFirst:  '&laquo;',
            sPrevious: '&lsaquo;',
            sNext:   '&rsaquo;',
            sLast:   '&raquo;'
          }
        },
        dom:
          \"<'row'<'col-sm-4'l><'#dtFilter.col-sm-4'><'col-sm-4'f>>\" +
          \"<'row'<'col-sm-12'tr>>\" +
          \"<'row'<'col-sm-5'i><'col-sm-7'p>>\",
      });
      $('#dtFilter').html('<select id=dtFilterSelect><option value=all>$langAll</option><option value=unreg>$langNotRegistered</option><option value=reg>$langRegistered</option></select>');
      $('#dtFilterSelect').change(function () {
        displayFilter = $(this).val();
        oTable.draw();
      });
      $(document).on('click', '.delete_btn', function (e) {
        e.preventDefault();
        var row_id = $(this).closest('tr').attr('id');
        bootbox.confirm({ 
            closeButton: false,
            title: '<div class=\'icon-modal-default\'><i class=\'fa-regular fa-trash-can fa-xl Accent-200-cl\'></i></div><div class=\'modal-title-default text-center mb-0\'>".js_escape($langConfirmDelete)."</div>',
            message: '<p class=\'text-center\'>".js_escape($langDeleteInvitation)."</p>',
            buttons: {
                cancel: {
                    label: '".js_escape($langCancel)."',
                    className: 'cancelAdminBtn position-center'
                },
                confirm: {
                    label: '".js_escape($langDelete)."',
                    className: 'deleteAdminBtn position-center',
                }
            },
            callback: function (result) {
                if (result) {
                    $.ajax({
                      type: 'POST',
                      url: '',
                      datatype: 'json',
                      data: {
                        delete: row_id
                      },
                      success: function(data) {
                        var info = oTable.page.info();
                        var per_page = info.length;
                        var page_number = info.page;
                        if (info.recordsDisplay % info.length == 1){
                        if (page_number!=0) {
                          page_number--;
                        }
                      }
                      $('#tool_title').after('<p class=\"success\">" . js_escape($langDeleteInvitationSuccess) . "</p>');
                      $('.success').delay(3000).fadeOut(1500);
                      oTable.page(page_number).draw(false);
                    },
                    error: function(xhr, textStatus, error) {
                      console.log(xhr.statusText);
                      console.log(textStatus);
                      console.log(error);
                    }
                    });
                }
            }
        });




      });
      $('.dt-search input').attr({style: 'width:200px', class:'form-control input-sm', placeholder: '$langName, e-mail'});
      $('.dt-search label').attr('aria-label', '$langName'); 
      $('.success').delay(3000).fadeOut(1500);
    });
    </script>";

$tool_content .=
        action_bar([
            [ 'title' => $langOneUser,
              'url' => "invite_one.php?course=$course_code",
              'icon' => 'fa-plus-circle',
              'button-class' => 'btn-success',
              'level' => 'primary-label' ],
            [ 'title' => $langManyUsers,
              'url' => "invite_many.php?course=$course_code",
              'icon' => 'fa-plus-circle',
              'button-class' => 'btn-success',
              'level' => 'primary-label' ],
        ]);

$tool_content .= "
    <table id='invite_table{$course_id}' class='table-default table-course-invitation'>
        <thead>
            <tr>
              <th>e-mail</th>
              <th>$langSurnameName</th>
              <th>$langDate</th>
              <th>$langExpirationDate</th>
              <th>$langRegistration</th>
              <th aria-label='$langSettingSelect'>".icon('fa-gears')."</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
            <tr>
                <th aria-label='$langSurnameName'></th>
                <th aria-label='$langDate'></th>
                <th aria-label='$langExpirationDate'></th>
                <th aria-label='$langRegistration'></th>
                <th aria-label='$langSettingSelect'></th>
            </tr>
        </tfoot>
    </table>";
draw($tool_content, 2, null, $head_content);
