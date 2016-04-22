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

define('EPF_TEXTBOX', 1);
define('EPF_TEXTAREA', 2);
define('EPF_DATE', 3);
define('EPF_MENU', 4);
define('EPF_LINK', 5);


/**
 * Render e-portfolio fields content when viewing e-portfolio
 * @param $uid
 * @return string
 */
function render_eportfolio_fields_content($uid) {
    global $langProfileNotAvailable;
    
    $pills_items = '<div class="tab-content">';

    $result = Database::get()->queryArray("SELECT id, name FROM eportfolio_fields_category ORDER BY sortorder DESC");
    
    $pills_menu = '<ul class="nav nav-pills">';
    $i = 0;
    
    foreach ($result as $cat) {
        if ($i==0) {
            $active_class = ' class="active"';
            $active = ' active';
            $i++;
        } else {
            $active_class = $active = '';
            $i++;
        }
        
        $pills_menu .= '<li'.$active_class.'><a href="#cat'.$cat->id.'" data-toggle="tab">'.$cat->name.'</a></li>';
        
        $res = Database::get()->queryArray("SELECT id, name, datatype, data FROM eportfolio_fields
               WHERE categoryid = ?d ORDER BY sortorder DESC", $cat->id);
        
        if (count($res) > 0) { //category start
        $pills_items .= "<div class='tab-pane$active' id='cat".$cat->id."'><div class='row'>
        <div class='col-xs-12 col-md-10 col-md-offset-2 profile-pers-info'>
        <h4>".$cat->name."</h4>";
        }

        foreach ($res as $f) { //get user data for each field
            $pills_items .= "<div class='profile-pers-info-data'>";

            $fdata_res = Database::get()->querySingle("SELECT data FROM eportfolio_fields_data
            WHERE user_id = ?d AND field_id = ?d", $uid, $f->id);

            $pills_items .= "<span class='tag'>".$f->name." : </span>";

            if (!$fdata_res || $fdata_res->data == '') {
                $pills_items .= " <span class='tag-value not_visible'> - $langProfileNotAvailable - </span>";
            } else {
                switch ($f->datatype) {
                    case EPF_TEXTBOX:
                        $pills_items .= "<span class='tag-value'>".q($fdata_res->data)."</span>";
                        break;
                    case EPF_TEXTAREA:
                        $pills_items .= "<span class='tag-value'>".standard_text_escape($fdata_res->data)."</span>";
                        break;
                    case EPF_DATE:
                        $pills_items .= "<span class='tag-value'>".q($fdata_res->data)."</span>";
                        break;
                    case EPF_MENU:
                        $options = unserialize($f->data);
                        $options = array_combine(range(1, count($options)), array_values($options));
                        $options[0] = "";
                        ksort($options);
                        $pills_items .= "<span class='tag-value'>".q($options[$fdata_res->data])."</span>";
                        break;
                    case EPF_LINK:
                        $pills_items .= "<span class='tag-value'><a href='".q($fdata_res->data)."'>".q($fdata_res->data)."</a></span>";
                        break;
                }
            }
            $pills_items .= "</div>";
        }

        if (count($res) > 0) { //category end
            $pills_items .= "</div></div></div>";
        }

    }
    $pills_items .= "</div>";
    $pills_menu .= "</ul>";
    
    return $pills_menu.$pills_items;
}

/**
 * Render e-portfolio fields in e-portfolio form
 * @return string
 */
function render_eportfolio_fields_form() {
    global $uid, $langOptional, $langCompulsory, $head_content;

    $return_string = array();
    $return_string['panels'] = "";
    $return_string['right_menu'] = "<div class='col-sm-3 hidden-xs' id='affixedSideNav'>
            <ul id='floatMenu' class='nav nav-pills nav-stacked well well-sm' role='tablist'>";

    $result = Database::get()->queryArray("SELECT id, name FROM eportfolio_fields_category ORDER BY sortorder DESC");

    $j = 0;
    
    foreach ($result as $c) {

        $res = Database::get()->queryArray("SELECT id, name, shortname, description, required, datatype, data
                                            FROM eportfolio_fields WHERE categoryid = ?d ORDER BY sortorder DESC", $c->id);

        if (count($res) > 0) {
            
            $return_string['panels'] .= '<div class="panel panel-default" id="'.$c->id.'">
                                       <div class="panel-heading">
                                           <h2 class="panel-title">'.$c->name.'</h2>
                                       </div>
                                       <div class="panel-body">
                                           <fieldset>';
            if ($j == 0) {
                $active = " class='active'";
            } else {
                $active = "";
            }
            
            $j++;
            
            $return_string['right_menu'] .= "<li$active><a href='#$c->id'>$c->name</a></li>";
            
            foreach ($res as $f) {

                if (isset($fdata)) {
                    unset($fdata);
                }

                if (Session::hasError('epf_'.$f->shortname)) {
                    $form_class = 'form-group has-error';
                    $help_block = '<span class="help-block">' . Session::getError('epf_'.$f->shortname) . '</span>';
                } else {
                    $form_class = 'form-group';
                    $help_block = '';
                }
                
                $return_string['panels'] .= '<div class="'.$form_class.'">';
                $return_string['panels'] .= '<label class="col-sm-2 control-label" for="'.$f->shortname.'">'.q($f->name).'</label>';
                $return_string['panels'] .= '<div class="col-sm-10">';

                //get data to prefill fields
                $data_res = Database::get()->querySingle("SELECT data FROM eportfolio_fields_data
                                                      WHERE field_id = ?d AND user_id = ?d", $f->id, $uid);
                if ($data_res) {
                    $fdata = $data_res->data;
                }

                if (Session::has('epf_'.$f->shortname)) {
                    $fdata = Session::get('epf_'.$f->shortname);
                }

                $val = '';
                $placeholder = '';

                switch ($f->datatype) {
                    case EPF_TEXTBOX:
                        if (isset($fdata) && $fdata != '') {
                            $val = 'value="'.q($fdata).'"';
                        } elseif (isset($_REQUEST['epf_'.$f->shortname]) && isset($_REQUEST['epf_'.$f->shortname]) != '') {
                            $val = 'value="'.q($_REQUEST['epf_'.$f->shortname]).'"';
                        }
                        if ($f->required == 0) {
                            $placeholder = 'placeholder="'.$langOptional.'"';
                        } else {
                            $placeholder = 'placeholder="'.$langCompulsory.'"';
                        }
                        $return_string['panels'] .= '<input class="form-control" '.$val.' type="text" '.$placeholder.' name="epf_'.$f->shortname.'">';
                        break;
                    case EPF_TEXTAREA:
                        if (isset($fdata) && $fdata != '') {
                            $val = $fdata;
                        } elseif (isset($_REQUEST['epf_'.$f->shortname]) && isset($_REQUEST['epf_'.$f->shortname]) != '') {
                            $val = $_REQUEST['epf_'.$f->shortname];
                        }
                        $return_string['panels'] .= rich_text_editor('epf_'.$f->shortname, 8, 20, $val);
                        if ($f->required == 0) {
                            $req_label = $langOptional;
                        } else {
                            $req_label = $langCompulsory;
                        }
                        break;
                    case EPF_DATE:
                        if (isset($fdata) && $fdata != '') {
                            $val = 'value="'.q($fdata).'"';
                        } elseif (isset($_REQUEST['epf_'.$f->shortname]) && isset($_REQUEST['epf_'.$f->shortname]) != '') {
                            $val = 'value="'.q($_REQUEST['epf_'.$f->shortname]).'"';
                        }
                        if ($f->required == 0) {
                            $placeholder = 'placeholder="'.$langOptional.'"';
                        } else {
                            $placeholder = 'placeholder="'.$langCompulsory.'"';
                        }
                        load_js('bootstrap-datepicker');
                        $return_string['panels'] .= '<input class="form-control" '.$val.' type="text" '.$placeholder.' name="epf_'.$f->shortname.'" data-provide="datepicker" data-date-format="dd-mm-yyyy">';
                        break;
                    case EPF_MENU:
                        if (isset($fdata) && $fdata != '') {
                            $def_selection = intval($fdata);
                        } elseif (isset($_REQUEST['epf_'.$f->shortname]) && isset($_REQUEST['epf_'.$f->shortname]) != '') {
                            $def_selection = intval($_REQUEST['epf_'.$f->shortname]);
                        } else {
                            $def_selection = 0;
                        }
                        $options = unserialize($f->data);
                        $options = array_combine(range(1, count($options)), array_values($options));
                        $options[0] = "";
                        ksort($options);
                        $return_string['panels'] .= selection($options, 'epf_'.$f->shortname, $def_selection);
                        if ($f->required == 0) {
                            $req_label = $langOptional;
                        } else {
                            $req_label = $langCompulsory;
                        }
                        break;
                    case EPF_LINK:
                        if (isset($fdata) && $fdata != '') {
                            $val = 'value="'.q($fdata).'"';
                        } elseif (isset($_REQUEST['epf_'.$f->shortname]) && isset($_REQUEST['epf_'.$f->shortname]) != '') {
                            $val = 'value="'.q($_REQUEST['epf_'.$f->shortname]).'"';
                        }
                        if ($f->required == 0) {
                            $placeholder = 'placeholder="'.$langOptional.'"';
                        } else {
                            $placeholder = 'placeholder="'.$langCompulsory.'"';
                        }
                        $return_string['panels'] .= '<input class="form-control" '.$val.' type="text" '.$placeholder.' name="epf_'.$f->shortname.'">';
                        break;
                }
                if (!empty($f->description)) {
                    $return_string['panels'] .= '<small><em>'.standard_text_escape($f->description);
                    if (isset($req_label)) {
                        $return_string['panels'] .= $req_label;
                    }
                    $return_string['panels'] .= '</em></small>';
                } elseif (isset($req_label)) {
                    $return_string['panels'] .= '<small><em>'.$req_label.'</em></small>';
                }
                $return_string['panels'] .= $help_block.'</div></div>';
                unset($req_label);
            }
            
            $return_string['panels'] .= '</fieldset>
                       </div>
                   </div>';
            
        }
    }
    
    $return_string['right_menu'] .= '</ul>
                                 </div>';
    
    return $return_string;
}

/**
 * Process e-portfolio fields values after submit
 * @return boolean $updated
 */
function process_eportfolio_fields_data() {
    global $uid;
    
    $updated = false;
    
    foreach ($_POST as $key => $value) {
        if (substr($key, 0, 4) == 'epf_') { //e-portfolio fields input names start with epf_
            $field_name = substr($key, 4);
            $result = Database::get()->querySingle("SELECT id, required FROM eportfolio_fields WHERE shortname = ?s", $field_name);
            $field_id = $result->id;
            $required = $result->required;
            //delete old values if exist
            if ($required == 1 && empty($value)) {
                continue;
            } else {
                Database::get()->query("DELETE FROM eportfolio_fields_data WHERE field_id = ?d AND user_id = ?d", $field_id, $uid);
            }
            
            if (!empty($value)) {
                Database::get()->query("INSERT INTO eportfolio_fields_data (user_id, field_id, data) VALUES (?d,?d,?s)", $uid, $field_id, $value);
            }
            $updated = true;
        }
    }
    return $updated;
}

function epf_validate(&$valitron_object) {
    global $langCPFLinkValidFail, $langCPFDateValidFail, $langTheFieldIsRequired;
    foreach ($_POST as $key => $value) {
        if (substr($key, 0, 4) == 'epf_') { //e-portfolio fields input names start with epf_
            $field_name = substr($key, 4);
            $result = Database::get()->querySingle("SELECT name, datatype, required FROM eportfolio_fields WHERE shortname = ?s", $field_name);
            $datatype = $result->datatype;
            $field_name = $result->name;
            $required = $result->required;
            if ($datatype != EPF_MENU) {
                if ($required == 1) {
                    $valitron_object->rule('required', $key)->message($langTheFieldIsRequired)->label($field_name);
                }
            } else {
                if ($required == 1) {
                    $valitron_object->rule('notIn', $key, array(0))->message($langTheFieldIsRequired)->label($field_name);
                }
            }
            
            if ($datatype == EPF_LINK) {
                $valitron_object->rule('url', $key)->message(sprintf($langCPFLinkValidFail, q($field_name)))->label($field_name);
            } elseif ($datatype == EPF_DATE) {
                $valitron_object->rule('date', $key)->message(sprintf($langCPFDateValidFail, q($field_name)))->label($field_name);
            }
        }
    }
}
