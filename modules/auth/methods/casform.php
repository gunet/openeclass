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

require_once 'include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';
$tree = new Hierarchy();


$checked ='';
if ($auth_data['cas_gunet']) {
    $checked = 'checked';
}
if (!method_exists('phpCAS', 'setDebug')) {
    $tool_content .= "<div class='alert alert-danger'>$langCASNotWork</div>";
}

if (!empty($auth_data['auth_settings'])) {
    $cas_port = $auth_data['cas_port'];
    if (empty($cas_port)) {
        $cas_port = 443;
    }
} else {
    $auth_data['cas_host'] = $auth_data['cas_context'] =
    $auth_data['cas_logout'] = $auth_data['cas_ssout'] =
    $auth_data['cas_cachain'] = $auth_data['casusermailattr'] =
    $auth_data['casusermailattr'] = '';
    $cas_port = 443;
    $auth_data['casusermailattr'] = 'mail';
    $auth_data['casuserfirstattr'] = 'givenName';
    $auth_data['casuserlastattr'] = 'sn';
    $auth_data['cas_altauth'] = 0;
    $auth_data['cas_altauth_use'] = 'mobile';
}

$cas_ssout_data = array(0 => $langNo, 1 => $langYes);

$cas_altauth_data = array(
    0 => '-',
    1 => 'eClass',
    2 => 'POP3',
    3 => 'IMAP',
    4 => 'LDAP',
    5 => 'External DB');

$cas_altauth_use_data = array('mobile' => $langcas_altauth_use_mobile, 'all' => $langcas_altauth_use_all);
if (!isset($auth_data['cas_altauth_use'])) {
    $auth_data['cas_altauth_use'] = 'mobile';
}

load_js('jstree3');
load_js('select2');
load_js('datatables');

$allow_only_defaults = get_config('restrict_teacher_owndep') && !$is_admin;
$allowables = array();
if ($allow_only_defaults) {
    // Method: getDepartmentIdsAllowedForCourseCreation
    // fetches only specific tree nodes, not their sub-children
    //$user->getDepartmentIdsAllowedForCourseCreation($uid);
    // the code below searches for the allow_course flag in the user's department subtrees
    $userdeps = $user->getDepartmentIds($uid);
    $subs = $tree->buildSubtreesFull($userdeps);
    foreach ($subs as $node) {
        if (intval($node->allow_course) === 1) {
            $allowables[] = $node->id;
        }
    }
}

list($js, $html) = $tree->buildCourseNodePicker(array('defaults' => $allowables, 'allow_only_defaults' => $allow_only_defaults, 'skip_preloaded_defaults' => true));
$head_content .= $js;

$minedu_school_data = Database::get()->queryArray("
    SELECT CONCAT(md.School,' - ', md.Department) AS School_Department, md.MineduID AS minedu_id, mda.department_id, h.name
    FROM minedu_department_association AS mda
    JOIN minedu_departments AS md ON mda.minedu_id = md.MineduID
    JOIN hierarchy AS h ON mda.department_id = h.id");

$minedu_department_association = json_encode(array_map(function ($item) {
    return ['minedu_id' => $item->minedu_id, 'department_id' => $item->department_id];
}, $minedu_school_data));

$minedu_institution = $auth_data['minedu_institution'] ?? '';
$minedu_institutions = Database::get()->queryArray('SELECT DISTINCT Institution
        FROM minedu_departments ORDER BY Institution');
$tool_content .= "
    <style>
        .select2-container {width:100%!important;margin-bottom: 20px;}
        .select2-container .select2-selection--single .select2-selection__rendered {font-size:12px;}
        #cas_gunet_table_info {display: none}
        #cas_gunet_table {margin-bottom: 20px;}

    </style>
    <script>
        $(document).ready(function() {

            if ($('#cas_gunet').prop('checked')) {
                $('.cas_gunet_container, .cas_port, .cas_logout, .cas_ssout, .cas_context, .casusermailattr, .casuserfirstattr, .casuserlastattr, .casuserstudentid, .cas_altauth, .cas_altauth_use').toggleClass('hide');
            }

            $('#cas_gunet').change(function() {
                $('.cas_gunet_container, .cas_port, .cas_logout, .cas_ssout, .cas_context, .casusermailattr, .casuserfirstattr, .casuserlastattr, .casuserstudentid, .cas_altauth, .cas_altauth_use').toggleClass('hide');
            });

            $('#cas_gunet_table').dataTable({
                searching: false,
                paging: false,
                columnDefs: [
                    { width: '40%', targets: [0, 2] },
                    { width: '10%', targets: [1, 3] }
                  ],
            });

            $('#minedu_School').prop('disabled', true).select2();

            $('#minedu_institution').select2().on('select2:select', function (e) {
                $('#minedu_School').val(null).trigger('change');
                var selectedInstitution = $(this).val();

                if (selectedInstitution) {
                    $('#minedu_School').prop('disabled', false);
                    $('#minedu_School').select2({
                        ajax: {
                          url: 'get_minedu_departments.php',
                          dataType: 'json',
                          delay: 250,
                          data: {
                            qtype: 'School',
                            Institution: selectedInstitution
                          },
                          processResults: function (data) {
                              console.log('School',data)
                            return {
                              results: data.map(function (item) {
                                return { text: item.Department, id: item.MineduID };
                              })
                            };
                          }
                        }
                    });
                } else {
                    $('#minedu_School').prop('disabled', true).empty();
                }
            }).trigger('select2:select');

            $('#cas_gunet_add').on('click', function(e) {
                e.preventDefault();
                let minedu_School = $('#minedu_School').select2('data');
                let minedu_School_text = minedu_School[0].text;
                let minedu_School_id = minedu_School[0].id;

                let local_dep_id = $( 'input[name=\"department[]\"]' ).val();
                let local_dep_text = $( '#dialog-set-value' ).val();

                var table = $('#cas_gunet_table').DataTable();
                let newRow = table.row.add([
                    minedu_School_text,
                    minedu_School_id,
                    local_dep_text,
                    local_dep_id
                ]).draw();

                let currentData = $('input[name=\"minedu_department_association\"]').val();
                let dataArray = currentData ? JSON.parse(currentData) : [];

                dataArray.push({
                    'minedu_id': minedu_School_id,
                    'department_id': local_dep_id
                });
                $('input[name=\"minedu_department_association\"]').val(JSON.stringify(dataArray));
            });
        });
    </script>
    <div class='form-group'>
        <label for='cas_host' class='col-sm-2 control-label'>$langcas_host:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='cas_host' id='cas_host' type='text' value='" . q($auth_data['cas_host']) . "'>
        </div>
    </div>
    <div class='form-group'>
        <div class='col-sm-10 col-sm-offset-2'>
            <input type='checkbox' name='cas_gunet' id='cas_gunet' value='1' $checked>
            <label for='cas_gunet'>$langCASGUnetIdentity</label>
        </div>
    </div>
    <div class='cas_gunet_container hide'>
        <div class='form-group'>
            <label for='minedu_institution' class='col-sm-2 control-label'>$langInstitution</label>
            <div class='col-sm-10'>
                <select id='minedu_institution' name='minedu_institution'>
                    <option value=''></option>" .
                    implode(array_map(function ($item) {
                        global $minedu_institution;
                        $institution = q($item->Institution);
                        $selected = $item->Institution == $minedu_institution? 'selected': '';
                        return "<option value='$institution' $selected>$institution</option>";
                    }, $minedu_institutions)) . "
                </select>
            </div>
        </div>
        <div class='form-group'>
            <label for='minedu_institution' class='col-sm-2 control-label'>$langSchoolDepartmentAssociation</label>
            <div class='col-sm-10'>
                <table id='cas_gunet_table'>
                    <thead>
                        <tr>
                            <th>$langSchoolDepartment</th>
                            <th>Minedu ID</th>
                            <th>$langFaculty</th>
                            <th>ID</th>
                        </tr>
                    </thead>
                    <tbody>" .
                        implode(array_map(function ($item) {
                            return "
                                <tr>
                                    <td>" . q($item->School_Department) . "</td>
                                    <td>{$item->minedu_id}</td>
                                    <td>" . q(getSerializedMessage($item->name)) . "</td>
                                    <td>{$item->department_id}</td>
                                </tr>";
                        }, $minedu_school_data)) . "
                    </tbody>
                </table>

                <div>
                    <div>
                        <div>
                            <label for='minedu_institution'>$langSchoolDepartment</label>
                            <select id='minedu_School'></select>
                        </div>
                        <div>
                            <label>$langLocalCategory</label>
                            $html
                        </div>
                    </div>
                    <button id='cas_gunet_add' class='btn btn-primary'>$langAdd</button>
                    <input type='hidden' name='minedu_department_association' value='$minedu_department_association'>
                </div>
            </div>
        </div>
    </div>
    <div class='form-group cas_port'>
        <label for='cas_port' class='col-sm-2 control-label'>$langcas_port:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='cas_port' id='cas_port' type='text' value='" . q($cas_port) . "'>
        </div>
    </div>
    <div class='form-group cas_context'>
        <label for='cas_context' class='col-sm-2 control-label'>$langcas_context:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='cas_context' id='cas_context' type='text' value='" . q($auth_data['cas_context']) . "'>
        </div>
    </div>
    <div class='form-group cas_logout'>
        <label for='cas_logout' class='col-sm-2 control-label'>$langcas_logout:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='cas_logout' id='cas_logout' type='text' value='" . q($auth_data['cas_logout']) . "'>
        </div>
    </div>
    <div class='form-group cas_ssout'>
        <label for='cas_logout' class='col-sm-2 control-label'>$langcas_ssout:</label>
        <div class='col-sm-10'>
            ". selection($cas_ssout_data, 'cas_ssout', $auth_data['cas_ssout'], 'class="form-control"') ."
        </div>
    </div>
    <div class='form-group cas_cachain'>
        <label for='cas_cachain' class='col-sm-2 control-label'>$langcas_cachain:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='cas_cachain' id='cas_cachain' type='text' value='" . q($auth_data['cas_cachain']) . "'>
        </div>
    </div>
    <div class='form-group casusermailattr'>
        <label for='casusermailattr' class='col-sm-2 control-label'>$langcasusermailattr:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='casusermailattr' id='casusermailattr' type='text' value='" . q($auth_data['casusermailattr']) . "'>
        </div>
    </div>
    <div class='form-group casuserfirstattr'>
        <label for='casuserfirstattr' class='col-sm-2 control-label'>$langcasuserfirstattr:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='casuserfirstattr' id='casuserfirstattr' type='text' value='" . q($auth_data['casuserfirstattr']) . "'>
        </div>
    </div>
    <div class='form-group casuserlastattr'>
        <label for='casuserlastattr' class='col-sm-2 control-label'>$langcasuserlastattr:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='casuserlastattr' id='casuserlastattr' type='text' value='" . q($auth_data['casuserlastattr']) . "'>
        </div>
    </div>
    <div class='form-group casuserstudentid'>
        <label for='casuserstudentid' class='col-sm-2 control-label'>$langcasuserstudentid:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='casuserstudentid' id='casuserstudentid' type='text' value='" . q($auth_data['casuserstudentid']) . "'>
        </div>
    </div>
    <div class='form-group cas_altauth'>
        <label for='cas_altauth' class='col-sm-2 control-label'>$langcas_altauth:</label>
        <div class='col-sm-10'>
            ". selection($cas_altauth_data, 'cas_altauth', $auth_data['cas_altauth'], 'class="form-control"') ."
        </div>
    </div>
    <div class='form-group cas_altauth_use'>
        <label for='cas_altauth_use' class='col-sm-2 control-label'>$langcas_altauth_use:</label>
        <div class='col-sm-10'>
            ". selection($cas_altauth_use_data, 'cas_altauth_use', $auth_data['cas_altauth_use'], 'class="form-control"') ."
        </div>
    </div>" .
    eclass_auth_form($auth_data['auth_title'], $auth_data['auth_instructions']);
