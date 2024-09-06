<?php

/* ========================================================================
 * Open eClass
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
 * ========================================================================
 */

define('CPF_TEXTBOX', 1);
define('CPF_TEXTAREA', 2);
define('CPF_DATE', 3);
define('CPF_MENU', 4);
define('CPF_LINK', 5);

define('CPF_VIS_PROF', 1);
define('CPF_VIS_ALL', 10);

define('CPF_USER_TYPE_PROF', 1);
define('CPF_USER_TYPE_STUD', 5);
define('CPF_USER_TYPE_ALL', 10);

/**
 * Render custom profile fields in profile forms
 * @param array $context
 * @param boolean $valitron
 * @return string
 */
function render_profile_fields_form($context, $valitron = false) {
    global $langOptional, $langCompulsory;

    if ($context['origin'] == 'admin_edit_profile') { //admin editing users' profile
        $uid = $context['user_id'];
    } else {
        global $uid;
    }

    $return_string = "";

    $result = Database::get()->queryArray("SELECT id, name FROM custom_profile_fields_category ORDER BY sortorder DESC");

    foreach ($result as $c) {

        $args = array();
        $args[0] = $c->id;

        $registr = '';

        if ($context['origin'] == 'student_register') { //student registration form
            $registr = 'AND registration = ?d ';
            $args[] = 1;
            $args[] = CPF_USER_TYPE_PROF;
        } elseif ($context['origin'] == 'teacher_register') { //teacher registration form
            $registr = 'AND registration = ?d ';
            $args[] = 1;
            $args[] = CPF_USER_TYPE_STUD;
        } elseif ($context['origin'] == 'edit_profile') { //edit profile form
            if ($_SESSION['status'] == USER_TEACHER) {
                $args[] = CPF_USER_TYPE_STUD;
            } elseif ($_SESSION['status'] == USER_STUDENT) {
                $args[] = CPF_USER_TYPE_PROF;
            }
        } elseif ($context['origin'] == 'admin_edit_profile') { //admin edit user profile form
            $status = Database::get()->querySingle("SELECT status FROM user WHERE id = ?d", $uid)->status;
            if ($status == USER_TEACHER) {
                $args[] = CPF_USER_TYPE_STUD;
            } elseif ($status == USER_STUDENT) {
                $args[] = CPF_USER_TYPE_PROF;
            }
        }

        $res = Database::get()->queryArray("SELECT id, name, shortname, description, required, datatype, data
                                            FROM custom_profile_fields WHERE categoryid = ?d ".$registr.
                                            "AND user_type <> ?d ORDER BY sortorder DESC", $args);

        if (count($res) > 0) {
            
            foreach ($res as $f) {

                if (isset($fdata)) {
                    unset($fdata);
                }

                if ($valitron) {
                    if (Session::hasError('cpf_'.$f->shortname)) {
                        $form_class = 'form-group has-error mt-4';
                        $help_block = '<span class="help-block Accent-200-cl">' . Session::getError('cpf_'.$f->shortname) . '</span>';
                    } else {
                        $form_class = 'form-group mt-4';
                        $help_block = '';
                    }
                } else {
                    $form_class = 'form-group mt-4';
                    $help_block = '';
                }

                $column = 'col-lg-6 col-12';
                $padding = 'px-3';
                // if case is editor then set column to equals 12. 
                if($f->datatype == 2 or isset($_GET['edProfile'])){
                    $column = 'col-12';
                    $padding = 'px-0';
                    if($f->datatype == 2 and !isset($_GET['edProfile'])){
                        $padding = 'px-3';
                    }
                }
                $return_string .= '<div class="'.$column.' '.$padding.'"><div class="'.$form_class.'">';
                $return_string .= '<label class="col-sm-12 control-label-notes" for="'.$f->shortname.'">'.q($f->name).'</label>';
               

                //get data to prefill fields
                if ($context['origin'] == 'edit_profile' || $context['origin'] == 'admin_edit_profile') {
                    $data_res = Database::get()->querySingle("SELECT data FROM custom_profile_fields_data
                                                          WHERE field_id = ?d AND user_id = ?d", $f->id, $uid);
                    if ($data_res) {
                        $fdata = $data_res->data;
                    }
                } elseif (isset($context['pending']) && $context['pending']) {
                    $data_res = Database::get()->querySingle("SELECT data FROM custom_profile_fields_data_pending
                                                          WHERE field_id = ?d AND user_request_id = ?d", $f->id, $context['user_request_id']);
                    if ($data_res) {
                        $fdata = $data_res->data;
                    }
                }

                if ($valitron) {
                    if (Session::has('cpf_'.$f->shortname)) {
                        $fdata = Session::get('cpf_'.$f->shortname);
                    }
                }

                $val = '';
                $placeholder = '';
                $helpBlock = '';

                switch ($f->datatype) {
                    case CPF_TEXTBOX:
                        if (isset($fdata) && $fdata != '') {
                            $val = 'value="'.q($fdata).'"';
                        } elseif (isset($_REQUEST['cpf_'.$f->shortname]) && isset($_REQUEST['cpf_'.$f->shortname]) != '') {
                            $val = 'value="'.q($_REQUEST['cpf_'.$f->shortname]).'"';
                        }
                        if ($f->required == 0) {
                            //$placeholder = 'placeholder="'.$langOptional.'"';
                            $helpBlock = '<em>'.$langOptional.'</em>';
                        } else {
                            //$placeholder = 'placeholder="'.$langCompulsory.'"';
                            $helpBlock = '<em>'.$langCompulsory.'</em>';
                        }
                        $return_string .= '<input id="'.$f->shortname.'" class="form-control" '.$val.' type="text" name="cpf_'.$f->shortname.'">';
                        $return_string .= '<small>'.$helpBlock.'</small>';
                        break;
                    case CPF_TEXTAREA:
                        if (isset($fdata) && $fdata != '') {
                            $val = $fdata;
                        } elseif (isset($_REQUEST['cpf_'.$f->shortname]) && isset($_REQUEST['cpf_'.$f->shortname]) != '') {
                            $val = $_REQUEST['cpf_'.$f->shortname];
                        }
                        $return_string .= rich_text_editor('cpf_'.$f->shortname, 8, 20, $val);
                        if ($f->required == 0) {
                            $req_label = $langOptional;
                        } else {
                            $req_label = $langCompulsory;
                        }
                        break;
                    case CPF_DATE:
                        if (isset($fdata) && $fdata != '') {
                            $val = 'value="'.q($fdata).'"';
                        } elseif (isset($_REQUEST['cpf_'.$f->shortname]) && isset($_REQUEST['cpf_'.$f->shortname]) != '') {
                            $val = 'value="'.q($_REQUEST['cpf_'.$f->shortname]).'"';
                        }
                        if ($f->required == 0) {
                            //$placeholder = 'placeholder="'.$langOptional.'"';
                            $helpBlock = '<em>'.$langOptional.'</em>';
                        } else {
                            //$placeholder = 'placeholder="'.$langCompulsory.'"';
                            $helpBlock = '<em>'.$langCompulsory.'</em>';
                        }
                        load_js('bootstrap-datepicker');
                        $return_string .= '<input class="form-control" '.$val.' type="text" name="cpf_'.$f->shortname.'" data-provide="datepicker" data-date-format="dd-mm-yyyy">';
                        $return_string .= '<small>'.$helpBlock.'</small>';
                        break;
                    case CPF_MENU:
                        if (isset($fdata) && $fdata != '') {
                            $def_selection = intval($fdata);
                        } elseif (isset($_REQUEST['cpf_'.$f->shortname]) && isset($_REQUEST['cpf_'.$f->shortname]) != '') {
                            $def_selection = intval($_REQUEST['cpf_'.$f->shortname]);
                        } else {
                            $def_selection = 0;
                        }
                        $options = unserialize($f->data);
                        $options = array_combine(range(1, count($options)), array_values($options));
                        $options[0] = "";
                        ksort($options);
                        $return_string .= selection($options, 'cpf_'.$f->shortname, $def_selection);
                        if ($f->required == 0) {
                            $req_label = $langOptional;
                        } else {
                            $req_label = $langCompulsory;
                        }
                        break;
                    case CPF_LINK:
                        if (isset($fdata) && $fdata != '') {
                            $val = 'value="'.q($fdata).'"';
                        } elseif (isset($_REQUEST['cpf_'.$f->shortname]) && isset($_REQUEST['cpf_'.$f->shortname]) != '') {
                            $val = 'value="'.q($_REQUEST['cpf_'.$f->shortname]).'"';
                        }
                        if ($f->required == 0) {
                            //$placeholder = 'placeholder="'.$langOptional.'"';
                            $helpBlock = '<em>'.$langOptional.'</em>';
                        } else {
                            //$placeholder = 'placeholder="'.$langCompulsory.'"';
                            $helpBlock = '<em>'.$langCompulsory.'</em>';
                        }
                        $return_string .= '<input class="form-control" '.$val.' type="text" name="cpf_'.$f->shortname.'">';
                        $return_string .= '<small>'.$helpBlock.'</small>';
                        break;
                }
                if (!empty($f->description)) {
                    $return_string .= '<small><em">'.standard_text_escape($f->description);
                    if (isset($req_label)) {
                        $return_string .= $req_label;
                    }
                    $return_string .= '</em></small>';
                } elseif (isset($req_label)) {
                    $return_string .= '<small><em>'.$req_label.'</em></small>';
                }
                $return_string .= $help_block.'</div></div>';
                unset($req_label);
            }
        }
    }
    return $return_string;
}

/**
 * Process custom profile fields values after submit
 * @param array $context
 * @return boolean $updated
 */
function process_profile_fields_data($context) {
    $updated = false;
    if (isset($context['pending']) && $context['pending']) { //pending teacher registration
        $user_request_id = $context['user_request_id'];
        foreach ($_POST as $key => $value) {
            if (substr($key, 0, 4) == 'cpf_' && $value != '') { //custom profile fields input names start with cpf_
                $field_name = substr($key, 4);
                $field = Database::get()->querySingle("SELECT id, datatype FROM custom_profile_fields WHERE shortname = ?s", $field_name);
                if ($field->datatype == CPF_TEXTAREA) {
                    $value = purify($value);
                }
                Database::get()->query("INSERT INTO custom_profile_fields_data_pending (user_request_id, field_id, data) VALUES (?d,?d,?s)", $user_request_id, $field->id, $value);
                $updated = true;
            }
        }
    } else { //normal registration process
        $uid = $context['uid'];
        foreach ($_POST as $key => $value) {
            if (substr($key, 0, 4) == 'cpf_') { //custom profile fields input names start with cpf_
                $field_name = substr($key, 4);
                $result = Database::get()->querySingle("SELECT id, required, datatype FROM custom_profile_fields WHERE shortname = ?s", $field_name);
                $field_id = $result->id;
                $required = $result->required;
                if (isset($context['origin']) && ($context['origin'] == 'edit_profile' || $context['origin'] == 'admin_edit_profile')) { //delete old values if exist
                    if ($required == 1 && empty($value)) {
                        continue;
                    } else {
                        Database::get()->query("DELETE FROM custom_profile_fields_data WHERE field_id = ?d AND user_id = ?d", $field_id, $uid);
                    }
                }
                if (!empty($value)) {
                    if ($result->datatype == CPF_TEXTAREA) {
                        $value = purify($value);
                    }
                    Database::get()->query("INSERT INTO custom_profile_fields_data (user_id, field_id, data) VALUES (?d,?d,?s)", $uid, $field_id, $value);
                }
                $updated = true;
            }
        }
    }
    return $updated;
}

/**
 * Add to the array passed to register_posted_variables required custom profile fields for validation
 * @param array $arr
 * @param boolean $valitron
 */
function augment_registered_posted_variables_arr(&$arr, $valitron = false) {
    foreach ($_POST as $key => $value) {
        if (substr($key, 0, 4) == 'cpf_') { //custom profile fields input names start with cpf_
            $field_name = substr($key, 4);
            $required = Database::get()->querySingle("SELECT required FROM custom_profile_fields WHERE shortname = ?s", $field_name)->required;
            if ($required == 1) {
                if ($valitron) {
                    $arr[] = $key;
                } else {
                    $arr[$key] = true;
                }
            }
        }
    }
}

/**
 * admin/edituser.php uses a different approach for validating required fields
 * so a specific function is needed
 * @return boolean $success
 */
function cpf_validate_required_edituser() {
    $success = true;
    foreach ($_POST as $key => $value) {
        if (substr($key, 0, 4) == 'cpf_') { //custom profile fields input names start with cpf_
            $field_name = substr($key, 4);
            $required = Database::get()->querySingle("SELECT required FROM custom_profile_fields WHERE shortname = ?s", $field_name)->required;
            if ($required == 1 && $value == '') {
                $success = false;
            }
        }
    }
    return $success;
}

/**
 * Render custom profile fields content when viewing profile
 * @param array $context
 * @return string
 */
function render_profile_fields_content($context) {
    global $uid, $langProfileNotAvailable, $langNoInfoAvailable;

    $return_str = '';

    $result = Database::get()->queryArray("SELECT id, name FROM custom_profile_fields_category ORDER BY sortorder DESC");

    if(count($result) > 0){

        $return_str .= "<div class='col-12 mt-4'>
                            <div class='row row-cols-1 row-cols-md-2 g-4'>";

                                foreach ($result as $cat) {
                    $return_str .= "<div class='col'>
                                        <div class='card panelCard border-card-left-default px-3 py-2 h-100'>";
                                            $args = array();

                                            $ref_user_type = Database::get()->querySingle("SELECT status FROM user WHERE id = ?d", $context['user_id'])->status;

                                            if ($ref_user_type == USER_TEACHER) {
                                                $user_type = '(user_type = ?d OR user_type = ?d)';
                                                $args[] = 1;
                                                $args[] = 10;
                                            } elseif ($ref_user_type == USER_STUDENT) {
                                                $user_type = '(user_type = ?d OR user_type = ?d)';
                                                $args[]= 5;
                                                $args[] = 10;
                                            }

                                            if ($context['user_id'] == $uid) { //viewing own profile
                                                $args[] = $cat->id;
                                                $res = Database::get()->queryArray("SELECT id, name, datatype, data FROM custom_profile_fields
                                                                                    WHERE $user_type AND categoryid = ?d
                                                                                    ORDER BY sortorder DESC", $args);
                                            } else { //viewing other user's profile
                                                if ($_SESSION['status'] == USER_STUDENT) {
                                                    $visibility = '(visibility = ?d OR visibility = ?d)';
                                                    $args[]= 5;
                                                    $args[] = 10;
                                                } elseif ($_SESSION['status'] == USER_TEACHER) {
                                                    $visibility = '(visibility = ?d OR visibility = ?d)';
                                                    $args[] = 1;
                                                    $args[] = 10;
                                                }
                                                $args[] = $cat->id;
                                                $res = Database::get()->queryArray("SELECT id, name, datatype, data FROM custom_profile_fields
                                                                                    WHERE $user_type AND $visibility AND categoryid = ?d
                                                                                    ORDER BY sortorder DESC", $args);
                                            }

                                            $return_str .= "<div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                                                <h3>".$cat->name."</h3>
                                                            </div>

                                                            <div class='card-body'>";

                                                                if (count($res) > 0) { //category start
                                                    $return_str .= "<ul class='list-group list-group-flush'> ";
                                                                    foreach ($res as $f) { //get user data for each field

                                                                        $return_str .= "
                                                                        <li class='list-group-item element'>
                                                                            <div class='row row-cols-1 row-cols-lg-2 g-1'>";

                                                                            $fdata_res = Database::get()->querySingle("SELECT data FROM custom_profile_fields_data
                                                                                                                    WHERE user_id = ?d AND field_id = ?d", $context['user_id'], $f->id);

                                                                            $return_str .= "
                                                                                                <div class='col-lg-4 col-12'>
                                                                                                    <div class='title-default'>".$f->name."</div>
                                                                                                </div>

                                                                                                <div class='col-lg-8 col-12 title-default-line-height'>
                                                                                            ";

                                                                                                if (!$fdata_res || $fdata_res->data == '') {
                                                                                                    $return_str .= " <p class='title-default-line-height'> $langProfileNotAvailable </p>";
                                                                                                } else {
                                                                                                    $return_str .= "";
                                                                                                    switch ($f->datatype) {
                                                                                                        case CPF_TEXTBOX:
                                                                                                            $return_str .= "<p class='title-default-line-height'>".q($fdata_res->data)."</p>";
                                                                                                            break;
                                                                                                        case CPF_TEXTAREA:
                                                                                                            $return_str .= "<p class='title-default-line-height'>".standard_text_escape($fdata_res->data)."</p>";
                                                                                                            break;
                                                                                                        case CPF_DATE:
                                                                                                            $return_str .= "<p class='title-default-line-height'>".q($fdata_res->data)."</p>";
                                                                                                            break;
                                                                                                        case CPF_MENU:
                                                                                                            $options = unserialize($f->data);
                                                                                                            $options = array_combine(range(1, count($options)), array_values($options));
                                                                                                            $options[0] = "";
                                                                                                            ksort($options);
                                                                                                            $return_str .= "<p class='title-default-line-height'>".q($options[$fdata_res->data])."</p>";
                                                                                                            break;
                                                                                                        case CPF_LINK:
                                                                                                            $return_str .= "<p class='title-default-line-height'><a href='".q($fdata_res->data)."'>".q($fdata_res->data)."</a></p>";
                                                                                                            break;
                                                                                                    }
                                                                                                }
                                                                            $return_str .= "    </div>
                                                                            </div>
                                                                        </li>";

                                                                    }
                                                    $return_str .= "</ul>";  
                                                                }else{
                                                                    $return_str .= "<p class='card-text'>$langNoInfoAvailable</p>";
                                                                }

                                            $return_str .= "</div>
                                        </div>
                                    </div>";//end panel-col

                                }

            //category end col-row
            $return_str .= "</div>
                        </div>";
    }

    return $return_str;
}

function augment_url_refill_custom_profile_fields_registr() {
    $ret_str = '';
    foreach ($_POST as $key => $value) {
        if (substr($key, 0, 4) == 'cpf_' && $value != '') {
            $ret_str .= '&amp;' . $key . '=' . urldecode($value);
        }
    }
    return $ret_str;
}

function cpf_validate_format() {
    global $langCPFLinkValidFail, $langCPFDateValidFail;
    $ret = array(0 => true);
    foreach ($_POST as $key => $value) {
        if (substr($key, 0, 4) == 'cpf_' && $value != '') { //custom profile fields input names start with cpf_
            $field_name = substr($key, 4);
            $result = Database::get()->querySingle("SELECT name, datatype FROM custom_profile_fields WHERE shortname = ?s", $field_name);
            $datatype = $result->datatype;
            $field_name = $result->name;
            if ($datatype == CPF_LINK) {
                if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$value)) {
                    $ret[0] = false;
                    $ret[] .= sprintf($langCPFLinkValidFail, q($field_name));
                }
            } elseif ($datatype == CPF_DATE) {
                $d = explode("-", $value);
                if (sizeof($d) == 3) {
                    if (!checkdate($d[1], $d[0], $d[2])) {
                        $ret[0] = false;
                        $ret[] .= sprintf($langCPFDateValidFail, q($field_name));
                    }
                } else {
                    $ret[0] = false;
                    $ret[] .= sprintf($langCPFDateValidFail, q($field_name));
                }
            }
        }
    }
    return $ret;
}

function cpf_validate_format_valitron(&$valitron_object) {
    global $langCPFLinkValidFail, $langCPFDateValidFail, $langTheFieldIsRequired;
    foreach ($_POST as $key => $value) {
        if (substr($key, 0, 4) == 'cpf_' && $value != '') { //custom profile fields input names start with cpf_
            $field_name = substr($key, 4);
            $result = Database::get()->querySingle("SELECT name, datatype, required FROM custom_profile_fields WHERE shortname = ?s", $field_name);
            $datatype = $result->datatype;
            $field_name = $result->name;
            if ($datatype == CPF_LINK) {
                $valitron_object->rule('url', $key)->message(sprintf($langCPFLinkValidFail, q($field_name)))->label($field_name);
            } elseif ($datatype == CPF_DATE) {
                $valitron_object->rule('date', $key)->message(sprintf($langCPFDateValidFail, q($field_name)))->label($field_name);
            } elseif ($datatype == CPF_MENU) {
                if ($result->required == 1) {
                    $valitron_object->rule('notIn', $key, array(0))->message($langTheFieldIsRequired)->label($field_name);
                }
            }
        }
    }
}
