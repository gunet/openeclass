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

/**
 * Render custom profile fields in profile forms
 * @param array $context
 * @return string
 */
function render_profile_fields_form($context) {
    global $uid;
    $return_string = "";
    
    $result = Database::get()->queryArray("SELECT id, name FROM custom_profile_fields_category ORDER BY sortorder DESC");
    
    foreach ($result as $c) {
        
        $args = array();
        $args[0] = $c->id;
        
        $registr = '';
        
        if ($context['origin'] == 'student_register') { //student registration form
            $registr = 'AND registration = ?d ';
            $args[] = 1;
            $args[] = 1;
        } elseif ($context['origin'] == 'teacher_register') { //teacher registration form
            $registr = 'AND registration = ?d ';
            $args[] = 1;
            $args[] = 5;
        } elseif ($context['origin'] == 'edit_profile') { //edit profile form
            if ($_SESSION['status'] == 1) { //teacher
                $args[] = 5;
            } elseif ($_SESSION['status'] == 5) { //student
                $args[] = 1;
            }
        }
        
        $res = Database::get()->queryArray("SELECT id, name, shortname, description, required, datatype 
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
                if ($context['origin'] == 'edit_profile') {
                    $data_res = Database::get()->querySingle("SELECT data FROM custom_profile_fields_data 
                                                          WHERE field_id = ?d AND user_id = ?d", $f->id, $uid);
                    if ($data_res) {
                        $fdata = $data_res->data;
                    }
                }
                
                $val = '';
                
                switch ($f->datatype) {
                    case 1: //text box
                        if (isset($fdata)) {
                            $val = 'value="'.q($fdata).'"';
                        }
                        $return_string .= '<input class="form-control" '.$val.' type="text" name="cpf_'.$f->shortname.'">';
                        break;
                    case 2: //textarea
                        $return_string .= rich_text_editor('cpf_'.$f->shortname, 8, 20, $val);
                        break;
                    case 3: //date
                        break;
                    case 4: //menu
                        break;
                    case 5: //link
                        if (isset($fdata)) {
                            $val = 'value="'.q($fdata).'"';
                        }
                        $return_string .= '<input class="form-control" '.$val.' type="text" name="cpf_'.$f->shortname.'">';
                        break;
                }
                $return_string .= '</div></div>';
            }
        }
    }
    return $return_string;
}

/**
 * Process custom profile fields values after submit
 * @param array $post_array
 * @param array $context
 */
function process_profile_fields_data($post_array, $context) {
    $uid = $context['uid'];
    foreach ($post_array as $key => $value) {
        if (substr($key, 0, 4) == 'cpf_') { //custom profile field case
            $field_name = substr($key, 4);
            $field_id = Database::get()->querySingle("SELECT id FROM custom_profile_fields WHERE shortname = ?s", $field_name)->id;
            if ($context['origin'] == 'edit_profile') { //delete old values if exist
                Database::get()->query("DELETE FROM custom_profile_fields_data WHERE field_id = ?d AND user_id = ?d", $field_id, $uid);
            }
            Database::get()->query("INSERT INTO custom_profile_fields_data (user_id, field_id, data) VALUES (?d,?d,?s)", $uid, $field_id, $value);
        }
    }    
}

/**
 * Render custom profile fields content when viewing profile
 * @param array $context
 * @param int $user_id
 * @return string
 */
function render_profile_fields_content($context, $user_id) {
    
}