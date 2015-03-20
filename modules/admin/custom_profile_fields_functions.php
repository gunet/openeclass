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
 * @return string
 */
function render_profile_fields_form($context) {
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
                
                $return_string .= '<div class="form-group">';
                $return_string .= '<label class="col-sm-2 control-label" for="'.$f->shortname.'">'.$f->name.'</label>';
                $return_string .= '<div class="col-sm-10">';
                
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
                
                $val = '';
                $placeholder = '';
                
                switch ($f->datatype) {
                    case CPF_TEXTBOX:
                        if (isset($fdata) && $fdata != '') {
                            $val = 'value="'.q($fdata).'"';
                        } elseif (isset($_REQUEST['cpf_'.$f->shortname]) && isset($_REQUEST['cpf_'.$f->shortname]) != '') {
                            $val = 'value="'.q($_REQUEST['cpf_'.$f->shortname]).'"';
                        }
                        if ($f->required == 0) {
                            $placeholder = 'placeholder="'.$langOptional.'"';
                        } else {
                            $placeholder = 'placeholder="'.$langCompulsory.'"';
                        }
                        $return_string .= '<input class="form-control" '.$val.' type="text" '.$placeholder.' name="cpf_'.$f->shortname.'">';
                        break;
                    case CPF_TEXTAREA:
                        if (isset($fdata) && $fdata != '') {
                            $val = $fdata;
                        } elseif (isset($_REQUEST['cpf_'.$f->shortname]) && isset($_REQUEST['cpf_'.$f->shortname]) != '') {
                            $val = $_REQUEST['cpf_'.$f->shortname];
                        }
                        $return_string .= rich_text_editor('cpf_'.$f->shortname, 8, 20, $val);
                        break;
                    case CPF_DATE:
                        if (isset($fdata) && $fdata != '') {
                            $val = 'value="'.q($fdata).'"';
                        } elseif (isset($_REQUEST['cpf_'.$f->shortname]) && isset($_REQUEST['cpf_'.$f->shortname]) != '') {
                            $val = 'value="'.q($_REQUEST['cpf_'.$f->shortname]).'"';
                        }
                        if ($f->required == 0) {
                            $placeholder = 'placeholder="'.$langOptional.'"';
                        } else {
                            $placeholder = 'placeholder="'.$langCompulsory.'"';
                        }
                        load_js('bootstrap-datepicker');
                        $return_string .= '<input class="form-control" '.$val.' type="text" '.$placeholder.' name="cpf_'.$f->shortname.'" data-provide="datepicker" data-date-format="dd-mm-yyyy">';
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
                        $return_string .= selection($options, 'cpf_'.$f->shortname, $def_selection);
                        break;
                    case CPF_LINK:
                        if (isset($fdata) && $fdata != '') {
                            $val = 'value="'.q($fdata).'"';
                        } elseif (isset($_REQUEST['cpf_'.$f->shortname]) && isset($_REQUEST['cpf_'.$f->shortname]) != '') {
                            $val = 'value="'.q($_REQUEST['cpf_'.$f->shortname]).'"';
                        }
                        if ($f->required == 0) {
                            $placeholder = 'placeholder="'.$langOptional.'"';
                        } else {
                            $placeholder = 'placeholder="'.$langCompulsory.'"';
                        }
                        $return_string .= '<input class="form-control" '.$val.' type="text" '.$placeholder.' name="cpf_'.$f->shortname.'">';
                        break;
                }
                if (!empty($f->description)) {
                    $return_string .= '<small><em>'.standard_text_escape($f->description).'</em></small>';
                }
                $return_string .= '</div></div>';
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
                $field_id = Database::get()->querySingle("SELECT id FROM custom_profile_fields WHERE shortname = ?s", $field_name)->id;
                Database::get()->query("INSERT INTO custom_profile_fields_data_pending (user_request_id, field_id, data) VALUES (?d,?d,?s)", $user_request_id, $field_id, $value);
                $updated = true;
            }
        }
    } else { //normal registration process
        $uid = $context['uid'];
        foreach ($_POST as $key => $value) {
            if (substr($key, 0, 4) == 'cpf_') { //custom profile fields input names start with cpf_
                $field_name = substr($key, 4);
                $result = Database::get()->querySingle("SELECT id, required FROM custom_profile_fields WHERE shortname = ?s", $field_name);
                $field_id = $result->id;
                $required = $result->id;
                if (isset($context['origin']) && ($context['origin'] == 'edit_profile' || $context['origin'] == 'admin_edit_profile')) { //delete old values if exist
                    if ($required == 1 && empty($value)) {
                        continue;
                    } else {
                        Database::get()->query("DELETE FROM custom_profile_fields_data WHERE field_id = ?d AND user_id = ?d", $field_id, $uid);
                    }
                }
                if (!empty($value)) {
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
 */
function augment_registered_posted_variables_arr(&$arr) {
    foreach ($_POST as $key => $value) {
        if (substr($key, 0, 4) == 'cpf_') { //custom profile fields input names start with cpf_
            $field_name = substr($key, 4);
            $required = Database::get()->querySingle("SELECT required FROM custom_profile_fields WHERE shortname = ?s", $field_name)->required;
            if ($required == 1) {
                $arr[$key] = true;
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
    global $uid, $langProfileNotAvailable;
    
    $return_str = '';
    
    $result = Database::get()->queryArray("SELECT id, name FROM custom_profile_fields_category ORDER BY sortorder DESC");
    
    foreach ($result as $cat) {
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
        
        if (count($res) > 0) { //category start
            $return_str .= "<div class='row'>
                                <div class='col-xs-12 col-md-10 col-md-offset-2 profile-pers-info'>
                                    <h4>".$cat->name."</h4>";
        }
        
        foreach ($res as $f) { //get user data for each field
            $return_str .= "<div class='profile-pers-info-data'>";
            
            $fdata_res = Database::get()->querySingle("SELECT data FROM custom_profile_fields_data
                                                       WHERE user_id = ?d AND field_id = ?d", $context['user_id'], $f->id);
            
            $return_str .= "<span class='tag'>".$f->name." : </span>";
            
            if (!$fdata_res || $fdata_res->data == '') {
                $return_str .= " <span class='tag-value not_visible'> - $langProfileNotAvailable - </span>";
            } else {
                $return_str .= "";
                switch ($f->datatype) {
                    case CPF_TEXTBOX:
                        $return_str .= "<span class='tag-value'>".q($fdata_res->data)."</span>";
                        break;
                    case CPF_TEXTAREA:
                        $return_str .= "<span class='tag-value'>".standard_text_escape($fdata_res->data)."</span>";
                        break;
                    case CPF_DATE:
                        $return_str .= "<span class='tag-value'>".q($fdata_res->data)."</span>";
                        break;
                    case CPF_MENU:
                        $options = unserialize($f->data);
                        $return_str .= "<span class='tag-value'>".q($options[$fdata_res->data])."</span>";
                        break;
                    case CPF_LINK:
                        $return_str .= "<span class='tag-value'><a href='".q($fdata_res->data)."'>".q($fdata_res->data)."</a></span>";
                        break;
                }
            }
            $return_str .= "</div>";
        }
        
        if (count($res) > 0) { //category end
            $return_str .= "</div></div>";
        }
        
    }
    return $return_str;
}

function augment_url_refill_custom_profile_fields_registr() {
    $ret_str = '';
    foreach ($_POST as $key => $value) {
        if (substr($key, 0, 4) == 'cpf_' && $value != '')
        $ret_str .= '&amp;'.$key.'='.urldecode($value);
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
