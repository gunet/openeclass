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

$require_login = TRUE;
if(isset($_GET['course'])) {//course messages
    $require_current_course = TRUE;
} else {//personal messages
    $require_current_course = FALSE;
}
$guest_allowed = FALSE;

require_once '../../include/baseTheme.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'class.msg.php';

if (!isset($course_id)) {
    $ajax_url = "ajax_handler.php";
    $ajax_url_outbox = "$ajax_url?mbox_type=outbox";
    $course_id = 0;
}  else {
    $ajax_url = "ajax_handler.php?course=$course_code";
    $ajax_url_outbox = "$ajax_url&mbox_type=outbox";
}

if (isset($_GET['mid'])) {

    $mid = intval($_GET['mid']);
    $msg = new Msg($mid, $uid, 'msg_view');
    if (!$msg->error) {

        $urlstr = '';
        if ($course_id != 0) {
            $urlstr = "?course=".$course_code;
        }
        $out = action_bar(array(
                            array('title' => $langBack,
                                  'url' => "outbox.php".$urlstr,
                                  'icon' => 'fa-reply',
                                  'button-class' => 'back_index btn-secondary',
                                  'level' => 'primary'),
                            array('title' => $langDelete,
                                    'url' => 'javascript:void(0)',
                                    'icon' => 'fa-xmark',
                                    'class' => 'delete_out_inner',
                                    'link-attrs' => "data-id='$msg->id'")
                        ));
        $recipients = '';
        foreach ($msg->recipients as $r) {
            if ($r != $msg->author_id) {
                $recipients .= display_user($r, false, false, "outtabs").' ,&nbsp;';
            }
        }
        $recipients = rtrim($recipients, ',&nbsp;'); // remove the last comma
        $out .= "
                    <div id='out_del_msg'></div>
                    <div id='out_msg_area'>
                        <div class='row row-cols-1 g-4'>
                                    <div class='col'>
                                        <div class='card panelCard border-card-left-default px-lg-4 py-lg-3 h-100'>
                                            <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                                <h3>$langMessageInfo</h3>
                                            </div>
                                            <div class='card-body'>
                                                <ul class='list-group list-group-flush'>
                                                    <li class='list-group-item element'>
                                                        <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                            <div class='col-md-3 col-12'>
                                                                <div class='title-default'>$langSubject</div>
                                                            </div>
                                                            <div class='col-md-9 col-12 title-default-line-height'>
                                                                ".q($msg->subject)."
                                                            </div>
                                                        </div>
                                                    </li>";
                                if ($msg->course_id != 0 && $course_id == 0) {
                                    $out .= "
                                                <li class='list-group-item element'>
                                                    <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                        <div class='col-md-3 col-12'>
                                                            <div class='title-default'>$langCourse</div>
                                                        </div>
                                                        <div class='col-md-9 col-12 title-default-line-height'>
                                                            <a class=\"outtabs\" href=\"index.php?course=".course_id_to_code($msg->course_id)."\">".q(course_id_to_title($msg->course_id))."</a>
                                                        </div>
                                                    </div>
                                                </li>";
                                }
                                $out .= "           <li class='list-group-item element'>
                                                        <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                            <div class='col-md-3 col-12'>
                                                                <div class='title-default'>$langDate</div>
                                                            </div>
                                                            <div class='col-md-9 col-12 title-default-line-height'>
                                                                ". format_locale_date($msg->timestamp, 'short') ."
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li class='list-group-item element'>
                                                        <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                            <div class='col-md-3 col-12'>
                                                                <div class='title-default'>$langSender</div>
                                                            </div>
                                                            <div class='col-md-9 col-12 title-default-line-height'>
                                                                ".display_user($msg->author_id, false, false, "outtabs")."
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li class='list-group-item element'>
                                                        <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                            <div class='col-md-3 col-12'>
                                                                <div class='title-default'>$langRecipients</div>
                                                            </div>
                                                            <div class='col-md-9 col-12 title-default-line-height'>
                                                                $recipients
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>


                                    <div class='col'>
                                        <div class='card panelCard border-card-left-default px-lg-4 py-lg-3 h-100'>
                                            <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                                 <h3>$langMessageContent</h3>
                                            </div>
                                            <div class='card-body'>
                                                            <div class='d-flex justify-content-start align-items-center flex-wrap gap-2 mb-4'>
                                                                <div>
                                                                    ".standard_text_escape($msg->body)."
                                                                </div>
                                                            </div>";
                                                if ($msg->filename != '') {
                                                $out .= "
                                                            <div class='d-flex justify-content-start align-items-center flex-wrap gap-2'>
                                                                <div>
                                                                    $langAttachedFile
                                                                </div>
                                                                <div>
                                                                    <a href=\"message_download.php?course=".course_id_to_code($msg->course_id)."&amp;id=$msg->id\" class=\"outtabs\" target=\"_blank\">" . q($msg->real_filename) . "
                                                                        &nbsp;<i class='fa fa-save'></i>
                                                                    </a>
                                                                    &nbsp;&nbsp;(".format_file_size($msg->filesize).")
                                                                </div>
                                                            </div>";
                                }
                                    $out .= "</div>
                                        </div>
                                    </div>
                        </div>
                    </div>";

        $out .= '<script>

        $(".row.title-row").next(".row").hide();
        $("#dropboxTabs .nav.nav-tabs").hide();

        $(".back_index").on("click", function(){
                                $(".row.title-row").next(".row").show();
                                $("#dropboxTabs .nav.nav-tabs").show();
                            });

        $(function() {
        $("#out_msg_body").find("a").addClass("outtabs");

        $(document).off( "click",".delete_out");

        $(document).on( "click",".delete_out_inner", function (e) {
            e.preventDefault();
            var id = $(this).children("a").data("id");
            var string = "mid="+id+"&'. generate_csrf_token_link_parameter() .'";

            // bootbox.confirm("'.js_escape($langConfirmDelete).'", function(result) {
            //     if(result) {
            //         $.ajax({
            //             type: "POST",
            //             url: "'.$ajax_url.'",
            //             datatype: "json",
            //             data: string,
            //             success: function(){
            //                 $("#out_del_msg").html("<p class=\"alert alert-success\"><i class=\"fa-solid fa-circle-check fa-lg\"></i><span>'.js_escape($langMessageDeleteSuccess).'</span></p>");
            //                 $(".alert-success").delay(3000).fadeOut(1500);
            //                 $("#out_msg_area").remove();
            //         }});
            //     }
            // });

            bootbox.confirm({
                closeButton: false,
                title: "<div class=\"icon-modal-default\"><i class=\"fa-regular fa-trash-can fa-xl Accent-200-cl\"></i></div><div class=\"modal-title-default text-center mb-0\">'.js_escape($langConfirmDelete).'</div>",
                message: "<p class=\"text-center\">'.js_escape($langConfirmDelete).'</p>",
                buttons: {
                    cancel: {
                        label: "'.js_escape($langCancel).'",
                        className: "cancelAdminBtn position-center"
                    },
                    confirm: {
                        label: "'.js_escape($langDelete).'",
                        className: "deleteAdminBtn position-center",
                    }
                },
                callback: function (result) {
                    if(result) {
                        $.ajax({
                            type: "POST",
                            url: "'.$ajax_url.'",
                            datatype: "json",
                            data: string,
                            success: function(){
                                $("#out_del_msg").html("<p class=\"alert alert-success\"><i class=\"fa-solid fa-circle-check fa-lg\"></i><span>'.js_escape($langMessageDeleteSuccess).'</span></p>");
                                $(".alert-success").delay(3000).fadeOut(1500);
                                $("#out_msg_area").remove();
                        }});
                    }
                }
            });



         });

        $(".delete").click(function() {
            if (confirm("' . js_escape($langConfirmDelete) . '")) {
                var rowContainer = $(this).parent().parent();
                var id = rowContainer.attr("id");
                var string = "mid="+id+"&'. generate_csrf_token_link_parameter() .'";
                $.ajax({
                       type: "POST",
                       url: "'.$ajax_url.'",
                       data: string,
                       cache: false,
                       success: function(){
                           $("#out_msg_area").slideUp(\'fast\', function() {
                                $(this).remove();
                                $("#out_del_msg").html("<p class=\'success\'>'.q($langMessageDeleteSuccess).'</p>");
                           });
                       }
                    });
                    return false;
            }
        });
        });
        </script>';
    }
} else {

    $out = "<div id='out_del_msg'></div><div id='outbox' class='table-responsive'>";
    $out .= "<table id='outbox_table' class='table-default'>
               <thead>
                 <tr class='list-header'>
                    <th>$langSubject</th>";
    if ($course_id == 0) {
        $out .= "<th>$langCourse</th>";
    }
    $out .= "      <th>$langRecipients</th>
                   <th style='width:15%;'>$langDate</th>
                   <th style='width:10%;' aria-label='$langDelete'><i class='fa fa-cogs'></i></th>
                 </tr>
               </thead>
               <tbody>
               </tbody>
             </table></div>";

    $out .= "<script type='text/javascript'>
               $(document).ready(function() {

                 var oTable2 = $('#outbox_table').DataTable({
                    'aoColumnDefs':[{'sClass':'option-btn-cell text-end', 'aTargets':[-1]}],
                    'bStateSave' : true,
                    'bProcessing': true,
                    'sDom': '<\"top\"fl<\"clear\">>rt<\"bottom\"ip<\"clear\">>',
                    'bServerSide': true,
                    'searchDelay': 1000,
                    ajax: {
                        url: '$ajax_url_outbox',
                        type: 'POST'
                    }, 
                    'lengthMenu': [10, 15, 20 , -1],
                    'sPaginationType': 'full_numbers',
                    'bSort': false,
                    'bAutoWidth' : false,
                    'fnDrawCallback': function( oSettings ) {
                        $('#outbox_table_wrapper .dt-search input').attr({
                          'class' : 'form-control input-sm ms-0 mb-3',
                          'placeholder' : '".js_escape($langSearch)."...'
                        });
                        $('#outbox_table_wrapper .dt-search label').attr('aria-label', '".js_escape($langSearch)."');  
                        $('.recipients').each(function(){
                            $(this).trunk8({
                                parseHTML: 'true',
                                lines: 2,
                                fill: '&hellip; sdfasfasd'
                            });
                        });
                    },
                    'oLanguage': {
                            'lengthLabels': { 
                                '-1': '$langAllOfThem' 
                            },
                            'sLengthMenu':   '".js_escape("$langDisplay _MENU_ $langResults2")."',
                            'zeroRecords':  '".js_escape($langNoResult)."',
                            'sInfo':         '".js_escape("$langDisplayed _START_ $langTill _END_ $langFrom2 _TOTAL_ $langTotalResults")."',
                            'sInfoEmpty':    '".js_escape("$langDisplayed 0 $langTill 0 $langFrom2 0 $langResults2")."',
                            'sInfoFiltered': '',
                            'sInfoPostFix':  '',
                            'sSearch':       '',
                            'oPaginate': {
                                 'sFirst':    '&laquo;',
                                 'sPrevious': '&lsaquo;',
                                 'sNext':     '&rsaquo;',
                                 'sLast':     '&raquo;'
                            }
                        }
                    });

                    $(document).off( 'click','.delete_out_inner');
                    $(document).on( 'click','.delete_out', function (e) {
                        e.preventDefault();
                        var id = $(this).data('id');
                        var string = 'mid='+id+'&". generate_csrf_token_link_parameter() ."';

                        // bootbox.confirm('".js_escape($langConfirmDelete)."', function(result) {
                        //     if (result) {
                        //         $.ajax({
                        //           type: 'POST',
                        //           url: '$ajax_url',
                        //           data: string,
                        //           cache: false,
                        //           success: function(){
                        //             var num_page_records = oTable2.fnGetData().length;
                        //             var per_page = $('#outbox_table').DataTable().page.info().length;
                        //             var page_number = $('#outbox_table').DataTable().page.info().page;
                        //             if(num_page_records==1){
                        //                 if(page_number!=0) {
                        //                     page_number--;
                        //                 }
                        //             }
                        //             $('#out_del_msg').html('<p class=\'alert alert-success\'><i class=\'fa-solid fa-circle-check fa-lg\'></i><span>".js_escape($langMessageDeleteSuccess)."</span></p>');
                        //             $('.alert-success').delay(3000).fadeOut(1500);
                        //             $('#out_msg_area').remove();
                        //             oTable2.fnPageChange(page_number);
                        //           }
                        //        });
                        //      }
                        // })

                        bootbox.confirm({
                            closeButton: false,
                            title: '<div class=\'icon-modal-default\'><i class=\'fa-regular fa-trash-can fa-xl Accent-200-cl\'></i></div><div class=\'modal-title-default text-center mb-0\'>".js_escape($langConfirmDelete)."</div>',
                            message: '<p class=\'text-center\'>".js_escape($langConfirmDelete)."</p>',
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
                                if(result) {
                                    $.ajax({
                                        type: 'POST',
                                        url: '$ajax_url',
                                        data: string,
                                        cache: false,
                                        success: function(){
                                          var num_page_records = oTable2.fnGetData().length;
                                          var per_page = $('#outbox_table').DataTable().page.info().length;
                                          var page_number = $('#outbox_table').DataTable().page.info().page;
                                          if(num_page_records==1){
                                              if(page_number!=0) {
                                                  page_number--;
                                              }
                                          }
                                          $('#out_del_msg').html('<p class=\'alert alert-success\'><i class=\'fa-solid fa-circle-check fa-lg\'></i><span>".js_escape($langMessageDeleteSuccess)."</span></p>');
                                          $('.alert-success').delay(3000).fadeOut(1500);
                                          $('#out_msg_area').remove();
                                          oTable2.fnPageChange(page_number);
                                        }
                                    });
                                }
                            }
                        });

                     });

                    $('.delete_all_out').click(function() {
                        // bootbox.confirm('".js_escape($langConfirmDeleteAllMsgs)."', function(result) {
                        //     if(result) {
                        //         var string = 'all_outbox=1&". generate_csrf_token_link_parameter() ."';
                        //         $.ajax({
                        //             type: 'POST',
                        //             url: '$ajax_url',
                        //             data: string,
                        //             cache: false,
                        //             success: function(){
                        //                 var num_page_records = oTable2.fnGetData().length;
                        //                 var per_page = $('#outbox_table').DataTable().page.info().length;
                        //                 var page_number = $('#outbox_table').DataTable().page.info().page;
                        //                 if(num_page_records==1){
                        //                 if(page_number!=0) {
                        //                     page_number--;
                        //                 }
                        //                 }
                        //                 $('#out_del_msg').html('<p class=\'alert alert-success\'><i class=\'fa-solid fa-circle-check fa-lg\'></i><span>".js_escape($langMessageDeleteAllSuccess)."</span></p>');
                        //                 $('.alert-success').delay(3000).fadeOut(1500);
                        //                 oTable2.fnPageChange(page_number);
                        //             }
                        //         });
                        //     }
                        // })

                        bootbox.confirm({
                            closeButton: false,
                            title: '<div class=\'icon-modal-default\'><i class=\'fa-regular fa-trash-can fa-xl Accent-200-cl\'></i></div><div class=\'modal-title-default text-center mb-0\'>".js_escape($langConfirmDelete)."</div>',
                            message: '<p class=\'text-center\'>".js_escape($langConfirmDeleteAllMsgs)."</p>',
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
                                if(result) {
                                    var string = 'all_outbox=1&". generate_csrf_token_link_parameter() ."';
                                    $.ajax({
                                        type: 'POST',
                                        url: '$ajax_url',
                                        data: string,
                                        cache: false,
                                        success: function(){
                                            var num_page_records = oTable2.fnGetData().length;
                                            var per_page = $('#outbox_table').DataTable().page.info().length;
                                            var page_number = $('#outbox_table').DataTable().page.info().page;
                                            if(num_page_records==1){
                                            if(page_number!=0) {
                                                page_number--;
                                            }
                                            }
                                            $('#out_del_msg').html('<p class=\'alert alert-success\'><i class=\'fa-solid fa-circle-check fa-lg\'></i><span>".js_escape($langMessageDeleteAllSuccess)."</span></p>');
                                            $('.alert-success').delay(3000).fadeOut(1500);
                                            oTable2.fnPageChange(page_number);
                                        }
                                    });
                                }
                            }
                        });
                    });

               });
             </script>";
}
echo $out;
