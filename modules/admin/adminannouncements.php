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


$require_admin = TRUE;
require_once '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';

if (isset($_GET['pin'])) {
    if ($_GET['pin'] == 1) {
        $top_order = Database::get()->querySingle("SELECT MAX(`order`) as max from admin_announcement")->max + 1;
        Database::get()->query("UPDATE admin_announcement SET `order` = ?d  where id = ?d", $top_order, $_GET['pin_an_id']);
    } elseif ($_GET['pin'] == 0) {
        Database::get()->query("UPDATE admin_announcement SET `order` = 0  where id = ?d", $_GET['pin_an_id']);
    }
}

$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$toolName = $langAdminAn;

load_js('tools.js');
load_js('bootstrap-datetimepicker');
load_js('trunk8');

if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if (isset($_POST['action'])) {
        if ($_POST['action']=='delete') {
            /* delete announcement */
            $row_id = intval($_POST['value']);
            $announce = Database::get()->querySingle("SELECT title, content FROM admin_announcement WHERE id = ?d ", $row_id);
            $txt_content = ellipsize_html(canonicalize_whitespace(strip_tags($announce->body)), 50, '+');
            Database::get()->query("DELETE FROM admin_announcement WHERE id= ?d", $row_id);
            Indexer::queueAsync(Indexer::REQUEST_REMOVE, Indexer::RESOURCE_ANNOUNCEMENT, $row_id);
            Log::record($course_id, MODULE_ID_ANNOUNCE, LOG_DELETE, array('id' => $row_id,
                'title' => $announce->title,
                'content' => $txt_content));
            exit();
        } elseif ($_POST['action']=='visible') {
            /* modify visibility */
            $row_id = intval($_POST['value']);
            $visible = intval($_POST['visible']) ? 1 : 0;
            Database::get()->query("UPDATE admin_announcement SET visible = ?d WHERE id = ?d", $visible, $row_id);
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_ANNOUNCEMENT, $row_id);
            exit();
        }
    }
    $limit = intval($_GET['iDisplayLength']);
    $offset = intval($_GET['iDisplayStart']);
    $keyword = '%' . $_GET['sSearch'] . '%';


    $all_announc = Database::get()->querySingle("SELECT COUNT(*) AS total FROM admin_announcement");
    $filtered_announc = Database::get()->querySingle("SELECT COUNT(*) AS total FROM admin_announcement WHERE title LIKE ?s", $keyword);
    if ($limit>0) {
        $extra_sql = 'LIMIT ?d, ?d';
        $extra_terms = array($offset, $limit);
    } else {
        $extra_sql = '';
        $extra_terms = array();
    }
    $result = Database::get()->queryArray("SELECT * FROM admin_announcement WHERE title LIKE ?s ORDER BY `order` DESC , `date` DESC  $extra_sql", $keyword, $extra_terms);

    $data['iTotalRecords'] = $all_announc->total;
    $data['iTotalDisplayRecords'] = $filtered_announc->total;
    $data['aaData'] = array();
        $iterator = 1;
        $now = date("Y-m-d H:i:s");
        $pinned_greater = Database::get()->querySingle("SELECT MAX(`order`) AS max_order FROM admin_announcement")->max_order;
        foreach ($result as $myrow) {

            $to_top = "";

            //checking visible status
            if ($myrow->visible == '0') {
                $visible = 1;
                $status_icon_list = "<li data-toggle='tooltip' data-placement='left' title='$langAnnouncementIsNotVis'><span class='fa fa-eye-slash'></span> $langInvisible</li>";
                $vis_class = 'not_visible';
            } else {
                $visible = 0;
                if (isset($myrow->begin)) {
                    if (isset($myrow->end) && $myrow->end < $now) {
                        $vis_class = 'not_visible';
                        $status_icon_list = "<li class='text-danger'  data-toggle='tooltip' data-placement='left' title='$langAnnouncementWillNotBeVis$myrow->end'><span class='fa fa-clock-o'></span> $langAdminExpired</li>";
                    } elseif ($myrow->begin > $now) {
                        $vis_class = 'not_visible';
                        $status_icon_list = "<li class='text-success'  data-toggle='tooltip' data-placement='left' title='$langAnnouncementWillBeVis$myrow->begin'><span class='fa fa-clock-o'></span> $langAdminWaiting</li>";
                    } else {
                        $status_icon_list = "<li data-toggle='tooltip' data-placement='left' title='$langAnnouncementIsVis'><span class='fa fa-eye'></span> $langVisible</li>";
                        $vis_class = 'visible';
                    }
                }else{
                    $status_icon_list = "<li data-toggle='tooltip' data-placement='left' title='$langAnnouncementIsVis'><span class='fa fa-eye'></span> $langVisible</li>";
                    $vis_class = 'visible';
                }
            }

            //setting datables column data
            if ($myrow->order != 0) {
                $pinned_class = "text-danger";
                $pinned = 0;
                $tooltip = "data-toggle='tooltip' data-placement='top' title='$langAdminPinnedOff'";
                if ($myrow->order != $pinned_greater) {
                    $to_top = "<a class='reorder' href='$_SERVER[SCRIPT_NAME]?pin_an_id=$myrow->id&pin=1'><span class='fa fa-arrow-up  pull-right' data-toggle='tooltip' data-placement='top' title='$langAdminPinnedToTop'></span></a>";
                }
            } elseif ($myrow->order == 0) {
                $pinned_class = "not_visible";
                $pinned = 1;
                $tooltip = "data-toggle='tooltip' data-placement='top' title='$langAdminPinnedOn'";
            }

            $data['aaData'][] = array(
                'DT_RowId' => $myrow->id,
                'DT_RowClass' => $vis_class,
                '0' => "<div class='table_td'>
                        <div class='table_td_header clearfix'>
                            <a href='$_SERVER[SCRIPT_NAME]?an_id=$myrow->id'>".standard_text_escape($myrow->title)."</a>
                            <a class='reorder' href='$_SERVER[SCRIPT_NAME]?pin_an_id=$myrow->id&pin=$pinned'>
                                <span class='fa fa-thumb-tack $pinned_class pull-right' $tooltip></span>
                            </a>
                            $to_top
                        </div>
                        <div class='table_td_body' data-id='$myrow->id'>".standard_text_escape($myrow->body)."</div>
                        </div>",
                //'0' => '<a href="'.$_SERVER['SCRIPT_NAME'].'?course='.$course_code.'&an_id='.$myrow->id.'">'.q($myrow->title).'</a>',
                '1' => claro_format_locale_date($dateFormatLong, strtotime($myrow->date)),
                '2' => '<ul class="list-unstyled">'.$status_icon_list.'</ul>',
                '3' => action_button(array(
                    array('title' => $langEditChange,
                        'icon' => 'fa-edit',
                        'url' => "$_SERVER[SCRIPT_NAME]?modify=$myrow->id"),
                    array('title' => !$myrow->visible == '0' ? $langViewHide : $langViewShow,
                        'icon' => !$myrow->visible == '0' ? 'fa-eye-slash' : 'fa-eye',
                        'icon-class' => 'vis_btn',
                        'icon-extra' => "data-vis='$visible' data-id='$myrow->id'"),
                    array('title' => $langDelete,
                        'class' => 'delete',
                        'icon' => 'fa-times',
                        'icon-class' => 'delete_btn',
                        'icon-extra' => "data-id='$myrow->id'")
                )));
            $iterator++;
        }
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}
if (isset($_GET['an_id'])) {

    $row = Database::get()->querySingle("SELECT * FROM admin_announcement WHERE id = ". intval($_GET['an_id']));
    if(empty($row)){
        redirect_to_home_page("modules/admin/adminannouncements/");
    }
}

if (isset($_GET['an_id'])) {
    $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => $_SERVER['SCRIPT_NAME'],
            'icon' => 'fa-reply',
            'level' => 'primary-label')),false);
}

if (isset($_GET['an_id'])) {
    $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]", "name" => $langAnnouncements);
    $tool_content .= "<div class='row'><div class='col-xs-12'><div class='panel'>";
    $tool_content .= "<div class='panel-body'>";
    $tool_content .= "
                        <div class='single_announcement'>
                            <div class='announcement-title'>
                                ".standard_text_escape($row->title)."
                            </div>
                            <span class='announcement-date'>
                                - ".claro_format_locale_date($dateFormatLong, strtotime($row->date))." -
                            </span>
                            <div class='announcement-main'>
                                ".standard_text_escape($row->body)."
                            </div>
                        </div>";

    $tool_content .= "
                    </div>
                </div></div></div>";
}

//check if Datables code is needed
if (!isset($_GET['addAnnounce']) && !isset($_GET['modify']) && !isset($_GET['an_id'])) {
    load_js('datatables');
    $head_content .= "<script type='text/javascript'>
        $(document).ready(function() {

           var oTable = $('#ann_table_admin').DataTable ({
                'aoColumnDefs':[{'sClass':'option-btn-cell', 'aTargets':[-1]}],
                'bStateSave': true,
                'bProcessing': true,
                'bServerSide': true,
                'sScrollX': true,
                'responsive': true,
                'searchDelay': 1000,
                'sAjaxSource': '$_SERVER[REQUEST_URI]',
                'aLengthMenu': [
                   [10, 15, 20 , -1],
                   [10, 15, 20, '$langAllOfThem'] // change per page values here
               ],
                'fnDrawCallback': function( oSettings ) {
                    popover_init();
                    tooltip_init();
                    $('.table_td_body').each(function() {
                        $(this).trunk8({
                            lines: '3',
                            fill: '&hellip;<div class=\"clearfix\"></div><a style=\"float:right;\" href=\"$_SERVER[SCRIPT_NAME]?an_id='+ $(this).data('id')+'\">$langMore</div>'
                        })
                    });
                    $('#ann_table_admin_filter label input').attr({
                          class : 'form-control input-sm',
                          placeholder : '$langSearch...'
                        });
                 },
                 'sPaginationType': 'full_numbers',
                'bSort': false,
                'oLanguage': {
                       'sLengthMenu':   '$langDisplay _MENU_ $langResults2',
                       'sZeroRecords':  '".$langNoResult."',
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

            $(document).on( 'click', '.reorder', function(e) {
                e.preventDefault();
                var link = $(this).attr('href');
                var tr_affected = $(this).closest('tr');

                $.ajax({
                    type: 'POST',
                    url: link,
                    data: {
                        pin_announce: 1
                    },
                    beforeSend: function(){
                        console.log(tr_affected);
                        tr_affected.css('backgroundColor','rgba(100,100,100,0.3)');
                    },
                    success: function(data){
                        oTable.ajax.reload(null, false);
                    }
                });
            });

            $(document).on( 'click','.delete_btn', function (e) {
                e.preventDefault();
                var row_id = $(this).data('id');
                bootbox.confirm('".js_escape($langSureToDelAnnounce)."', function(result) {
                    if(result) {
                        $.ajax({
                          type: 'POST',
                          url: '',
                          datatype: 'json',
                          data: {
                             action: 'delete',
                             value: row_id
                          },
                          success: function(data){
                            var info = oTable.page.info();
                            /*var num_page_records = info.recordsDisplay;
                            var per_page = info.iLength;*/
                            var page_number = info.page;
                            /*if(num_page_records==1){
                                if(page_number!=0) {
                                    page_number--;
                                }
                            } */
                            oTable.draw(false);
                          },
                          error: function(xhr, textStatus, error){
                              console.log(xhr.statusText);
                              console.log(textStatus);
                              console.log(error);
                          }
                        });
                        $.ajax({
                            type: 'POST',
                            url: '{$urlAppend}/modules/search/idxasync.php'
                        });
                    }
                });
            });
            $(document).on( 'click','.vis_btn', function (g) {
                g.preventDefault();
                var vis = $(this).data('vis');
                var row_id = $(this).data('id');
                $.ajax({
                  type: 'POST',
                  url: '',
                  datatype: 'json',
                  data: {
                        action: 'visible',
                        value: row_id,
                        visible: vis
                  },
                  success: function(data){
                    oTable.draw(false);
                  },
                  error: function(xhr, textStatus, error){
                      console.log(xhr.statusText);
                      console.log(textStatus);
                      console.log(error);
                  }
                });
                $.ajax({
                    type: 'POST',
                    url: '{$urlAppend}/modules/search/idxasync.php'
                });
            });
            $('.success').delay(3000).fadeOut(1500);

        });
        </script>";
}

$head_content .= <<<hContent
<script type='text/javascript'>
function toggle(id, checkbox, name)
{
        var f = document.getElementById('f-calendar-field-' + id);
        f.disabled = !checkbox.checked;
}
</script>
hContent;

$head_content .= "<script type='text/javascript'>
        $(document).ready(function () {
            $('.table_td_body').each(function() {
                $(this).trunk8({
                    lines: '3',
                    fill: '&hellip;<div class=\"clearfix\"></div><a style=\"float:right;\" href=\"adminannouncements_single.php?ann_id='
                    + $(this).data('id')+'\">$langMore</div>'
                })
            });

            if ( $('#submitAnnouncement').length > 0 ) {

            $('input[type=checkbox]').eq(0).prop('checked') ? $('input[type=checkbox]').eq(0).parents('.input-group').children('input').prop('disabled', false) : $('input[type=checkbox]').eq(0).parents('.input-group').children('input').prop('disabled', true);
            $('input[type=checkbox]').eq(1).prop('checked') ? $('input[type=checkbox]').eq(1).parents('.input-group').children('input').prop('disabled', false) : $('input[type=checkbox]').eq(1).parents('.input-group').children('input').prop('disabled', true);

                $('input[type=checkbox]').eq(0).on('click', function() {
                    if ($('input[type=checkbox]').eq(0).prop('checked')) {
                        $('input[type=checkbox]').eq(1).prop('disabled', false);
                    } else {
                        $('input[type=checkbox]').eq(1).prop('disabled', true);
                        $('input[type=checkbox]').eq(1).prop('checked', false);
                        $('input[type=checkbox]').eq(1).parents('.input-group').children('input').prop('disabled', true);
                    }
                });


                $('.input-group-addon input[type=checkbox]').on('click', function(){
                var prop = $(this).parents('.input-group').children('input').prop('disabled');
                    if(prop){
                        $(this).parents('.input-group').children('input').prop('disabled', false);
                    } else {
                        $(this).parents('.input-group').children('input').prop('disabled', true);
                    }
                });
            }

            $('#startdate, #enddate').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-right',
                language: '".$language."',
                autoclose: true
            });
        });
    </script>";

// display settings
$displayAnnouncementList = true;
$displayForm = true;

$newContent = isset($_POST['newContent']) ? $_POST['newContent'] : '';

foreach (array('title', 'lang_admin_ann') as $var) {
    if (isset($_POST[$var])) {
        $GLOBALS[$var] = q($_POST[$var]);
    } else {
        $GLOBALS[$var] = '';
    }
}

if (isset($_GET['addAnnounce']) or isset($_GET['modify'])) {
        if (isset($_GET['addAnnounce'])) {
            $pageName = $langAdminAddAnn;
        } else {
            $pageName = $langAdminModifAnn;
        }
        $tool_content .= action_bar(array(
                    array('title' => $langBack,
                        'url' => $_SERVER['SCRIPT_NAME'],
                        'icon' => 'fa-reply',
                        'level' => 'primary-label')
                    ));
    } elseif (!isset($_GET['an_id'])) {
        $tool_content .= action_bar(array(
                array('title' => $langAdminAddAnn,
                    'url' => $_SERVER['SCRIPT_NAME'] . "?addAnnounce=1",
                    'icon' => 'fa-plus-circle',
                    'level' => 'primary-label',
                    'button-class' => 'btn-success')
                ));

    }

// modify visibility
if (isset($_GET['vis'])) {
    $id = q($_GET['id']);
    $vis = q($_GET['vis']);
    Database::get()->query("UPDATE admin_announcement SET visible = ?b WHERE id = ?d", $vis, $id);
}

if (isset($_GET['delete'])) {
    // delete announcement command
    $id = intval($_GET['delete']);
    Database::get()->query("DELETE FROM admin_announcement WHERE id = ?d", $id)->affectedRows;
    $message = $langAdminAnnDel;
} elseif (isset($_GET['modify'])) {
    // modify announcement command
    $id = intval($_GET['modify']);
    $myrow = Database::get()->querySingle("SELECT id, title, body, `date`, `begin`,`end`,
                                                lang, `order`, visible FROM admin_announcement WHERE id = ?d", $id);
    if ($myrow) {
        $titleToModify = q($myrow->title);
        $contentToModify = standard_text_escape($myrow->body);
        $displayAnnouncementList = true;
        $d1 = DateTime::createFromFormat('Y-m-d H:i:s', $myrow->begin);
        if ($d1) {
            $begindate = $d1->format("d-m-Y H:i");
        }
        $d2 = DateTime::createFromFormat('Y-m-d H:i:s', $myrow->end);
        if ($d2) {
            $enddate = $d2->format("d-m-Y H:i");
        }
    }
} elseif (isset($_POST['submitAnnouncement'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('title'));
    $v->labels(array('title' => "$langTheField $langAnnTitle"));
    if($v->validate()) {
        // submit announcement command
        $dates = array();
        if (isset($_POST['show_public'])){
            $show_public =  1;
        } else {
            $show_public =  0;
        }
        if (isset($_POST['startdate_active']) and isset($_POST['startdate'])) {
            $start_sql = 'begin = ?s';
            $date_started = DateTime::createFromFormat("d-m-Y H:i", $_POST['startdate']);
            $dates[] = $date_started->format("Y-m-d H:i:s");
        } else {
            $start_sql = 'begin = NULL';
        }
        if (isset($_POST['enddate_active']) and isset($_POST['enddate'])) {
            $end_sql = 'end = ?s';
            $date_ended = DateTime::createFromFormat("d-m-Y H:i", $_POST['enddate']);
            $dates[] = $date_ended->format("Y-m-d H:i:s");
        } else {
            $end_sql = 'end = NULL';
        }
        $newContent = purify($newContent);
        if (isset($_POST['id'])) {
            // modify announcement
            $id = $_POST['id'];
            Database::get()->query("UPDATE admin_announcement
                            SET title = ?s, body = ?s, lang = ?s,
                                `date` = " . DBHelper::timeAfter() . ", $start_sql, $end_sql, `visible`=?d
                            WHERE id = ?d", $title, $newContent, $lang_admin_ann, $dates, $show_public, $id);
            $message = $langAdminAnnModify;
        } else {
            // add new announcement

            Database::get()->query("INSERT INTO admin_announcement
                            SET title = ?s,
                                body = ?s,
                                lang = ?s,
                                `date` = " . DBHelper::timeAfter() . ",
                                `order` = 0,
                                $start_sql,
                                $end_sql, `visible`=?d", $title, $newContent, $lang_admin_ann, $dates, $show_public);
            $message = $langAdminAnnAdd;
        }
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page("modules/admin/adminannouncements.php?addAnnounce=1");
    }
}

// action message
if (isset($message) && !empty($message)) {
    Session::Messages($message, 'alert-success');
    redirect_to_home_page("/modules/admin/adminannouncements.php");
}

// display form
if ($displayForm && isset($_GET['addAnnounce']) || isset($_GET['modify'])) {
    $displayAnnouncementList = false;
    // display add announcement command
    $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]", "name" => $langAdminAn);

    $checked_public = "checked";

    if (!isset($contentToModify)) {
        $contentToModify = '';
    }
    if (!isset($titleToModify)) {
        $titleToModify = '';
    }
    $tool_content .= "<div class='form-wrapper'>";
    $tool_content .= "<form role='form' class='form-horizontal' method='post' action='$_SERVER[SCRIPT_NAME]'>";
    if (isset($_GET['modify'])) {
        $tool_content .= "<input type='hidden' name='id' value='$id' />";
    }
    $antitle_error = Session::getError('title', "<span class='help-block'>:message</span>");
    $tool_content .= "<div class='form-group".($antitle_error ? " has-error" : "")."'>";
    $tool_content .= "<label for='title' class='col-sm-2 control-label'>$langTitle:</label>
                        <div class='col-sm-10'><input class='form-control' type='text' name='title' value='$titleToModify' size='50' /></div>
                    </div>
                    <div class='form-group'>
                        <label for='newContent' class='col-sm-2 control-label'>$langAnnouncement:</label>
                    <div class='col-sm-10'>" . rich_text_editor('newContent', 5, 40, $contentToModify) . "</div></div>";
    $tool_content .= "<div class='form-group'><label class='col-sm-2 control-label'>$langLanguage:</label>";
    if (isset($_GET['modify'])) {
        if ($myrow->visible == 1) {
            $checked_public = "checked";
        } else {
            $checked_public = "";
        }
        if (isset($begindate)) {
            $start_checkbox = "checked";
            $start_text_disabled = "";
            $end_disabled = "";
            $startdate = $begindate;
            if (isset($enddate)) {
                $end_checkbox = "checked";
                $end_text_disabled = "";
            } else {
                $end_checkbox = "";
                $end_text_disabled = "disabled";
            }
        } else {
            $start_checkbox = "";
            $start_text_disabled = "disabled";
            $end_checkbox = "";
            $end_disabled = "disabled";
            $end_text_disabled = "disabled";
            $startdate = '';
        }
        if (isset($enddate)) {
            $end_checkbox = 'checked';
        } else {
            $end_checkbox = '';
            $enddate = '';
        }
        $tool_content .= "<div class='col-sm-10'>" . lang_select_options('lang_admin_ann', "class='form-control'", $myrow->lang) . "</div>";
    } else {
        $start_checkbox = $end_checkbox = '';
        $start_text_disabled = "disabled";
        $end_disabled = "disabled";
        $end_text_disabled = "disabled";

        $startdate = '';
        $enddate = '';
        $tool_content .= "<div class='col-sm-10'>" . lang_select_options('lang_admin_ann', "class='form-control'") . "</div>";
    }    
    $tool_content .= "<small class='text-right'><span class='help-block'>$langTipLangAdminAnn</span></small></div>
        <div class='form-group'>
            <label for='startdate' class='col-sm-2 control-label'>$langStartDate :</label>
            <div class='col-sm-10'>
                <div class='input-group'>
                    <span class='input-group-addon'>
                        <input type='checkbox' name='startdate_active' $start_checkbox>
                    </span>
                    <input class='form-control' name='startdate' id='startdate' type='text' value = '" .$startdate . "' $start_text_disabled>
                </div>
            </div>
        </div>
        <div class='form-group'>
            <label for='enddate' class='col-sm-2 control-label'>$langEndDate :</label>
            <div class='col-sm-10'>
                <div class='input-group'>
                    <span class='input-group-addon'>
                        <input type='checkbox' name='enddate_active' $end_checkbox $end_disabled>
                    </span>
                    <input class='form-control' name='enddate' id='enddate' type='text' value = '" .$enddate . "' $end_text_disabled>
                </div>
            </div>
        </div>
        <div class='form-group'>
            <div class='col-sm-10 col-sm-offset-2'>
                <div class='checkbox'>
                    <label>
                        <input type='checkbox' name='show_public' $checked_public> $langViewShow
                    </label>
                </div>
            </div>
        </div>
        <div class='form-group'>
            <div class='col-sm-offset-2 col-sm-10'>
                <input id='submitAnnouncement' class='btn btn-primary' type='submit' name='submitAnnouncement' value='$langSubmit'>
            </div>
        </div>
        ". generate_csrf_token_form_field() ."
        </form>
    </div>";
}

if (isset($_GET['down'])) {
    $thisAnnouncementId = q($_GET['down']);
    $sortDirection = "DESC";
}
if (isset($_GET['up'])) {
    $thisAnnouncementId = q($_GET['up']);
    $sortDirection = "ASC";
}



if (isset($thisAnnouncementId) && $thisAnnouncementId && isset($sortDirection) && $sortDirection) {
    Database::get()->queryFunc("SELECT id, `order` FROM admin_announcement ORDER BY `order` $sortDirection", function ($announcement) use(&$thisAnnouncementOrderFound, &$nextAnnouncementId, &$nextAnnouncementOrder, &$thisAnnouncementOrder, &$thisAnnouncementId) {
        if (isset($thisAnnouncementOrderFound) && $thisAnnouncementOrderFound == true) {
            $nextAnnouncementId = $announcement->id;
            $nextAnnouncementOrder = $announcement->order;
            Database::get()->query("UPDATE admin_announcement SET `order` = ?s WHERE id = ?d", $nextAnnouncementOrder, $thisAnnouncementId);
            Database::get()->query("UPDATE admin_announcement SET `order` = ?s WHERE id = ?d", $thisAnnouncementOrder, $nextAnnouncementId);
            return true;
        }
        // find the order
        if ($announcement->id == $thisAnnouncementId) {
            $thisAnnouncementOrder = $announcement->order;
            $thisAnnouncementOrderFound = true;
        }
    });
}

// display admin announcements
if ($displayAnnouncementList == true && !isset($_GET['an_id'])) {
    $tool_content .= "
        <div class='table-responsive'>
            <table id='ann_table_admin' class='table-default'>
                <thead>
                    <tr class='list-header'>
                        <th>$langAnnouncement</th>
                        <th>$langDate</th>
                        <th>$langNewBBBSessionStatus</th>
                        <th class='text-center'><i class='fa fa-cogs'></i></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>";
}


draw($tool_content, 3, null, $head_content);