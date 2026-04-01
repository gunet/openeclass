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

    $showAll = false;

    $return_string = array();
    $return_string['panels'] = "";
    $return_string['right_menu'] = "<div class='d-none col-sm-3 hidden-xs' id='affixedSideNav'>
    <nav id='navbar-exampleIndexPortfolio' class='navbar navbar-light mt-4 bg-light flex-column align-items-stretch p-3 sticky-top shadow-lg' style='z-index:1;'>
        <nav class='nav nav-pills flex-column'>";

    $result = Database::get()->queryArray("SELECT id, name FROM eportfolio_fields_category ORDER BY sortorder DESC");

    $j = 0;

    foreach ($result as $c) {

        $showCat = false;
        $cat_return_string = array();
        $cat_return_string['panels'] = "";
        $cat_return_string['right_menu'] = "";

        $res = Database::get()->queryArray("SELECT id, shortname, name, datatype, data FROM eportfolio_fields WHERE categoryid = ?d ORDER BY sortorder DESC", $c->id);

        if (count($res) > 0) {
            $cat_return_string['panels'] .= '
            <div class="col">
            <div class="card panelCard border-card-left-default px-3 py-2 h-100" id="IndexPortfolio'.$c->id.'">
                                                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                                                    <h3>'. q($c->name) .'</h3>
                                                </div>
                                                 <div class="card-body">
                                                     
                                                     <ul class="list-group list-group-flush">';

            if ($j == 0) {
                $active = " class='active'";
            } else {
                $active = "";
            }

            $j++;

            $cat_return_string['right_menu'] .= "<a class='nav-link nav-link-adminTools Neutral-900-cl' href='#IndexPortfolio$c->id'>" . q($c->name) . "</a>";

            foreach ($res as $f) {

                if (isset($fdata)) {
                    unset($fdata);
                }

                if (!isset($_SESSION['uid'])) {
                    $visibility_query = "=".EPF_VISIBLE_PUBLIC;
                } else {
                    if ($_SESSION['uid'] == $uid) {
                        $visibility_query = "<=".EPF_VISIBLE_PRIVATE;
                        if (isset($_GET['view'])) { //preview mode
                            if ($_GET['view']=='public') {
                                $visibility_query = "=".EPF_VISIBLE_PUBLIC;
                            } elseif ($_GET['view']=='registered') {
                                $visibility_query = "<=".EPF_VISIBLE_USERS;
                            }
                        }
                    } else {
                        $visibility_query = "<=".EPF_VISIBLE_USERS;
                    }
                }

                //get data to prefill fields
                $fdata_res = Database::get()->querySingle("SELECT data FROM eportfolio_fields_data
                                 WHERE user_id = ?d AND field_id = ?d AND visibility ".$visibility_query, $uid, $f->id);
                if ($fdata_res AND (($f->datatype != EPF_MENU AND $fdata_res->data != '') OR ($f->datatype == EPF_MENU AND $fdata_res->data != 0))) {
                    $showCat = true;
                    $showAll = true;

                    $cat_return_string['panels'] .= '<li class="list-group-item element">';
                    $cat_return_string['panels'] .= '<div class="row row-cols-1 row-cols-md-2 g-1">
                                                        <div class="col-md-3 col-12">
                                                            <div class="title-default">'.q($f->name).': </div>
                                                        </div>';
                    $cat_return_string['panels'] .= '   <div class="col-md-9 col-12 title-default-line-height">';


                    switch ($f->datatype) {
                        case EPF_DATE:
                        case EPF_TEXTBOX:
                            if ($f->shortname == 'scopus') {
                                $cat_return_string['panels'] .= "<a href='https://www.scopus.com/authid/detail.uri?authorId=".q($fdata_res->data)."'>".q($fdata_res->data)."</a>";
                            } else {
                                $cat_return_string['panels'] .= q($fdata_res->data);
                            }
                            break;
                        case EPF_TEXTAREA:
                            $cat_return_string['panels'] .= "".standard_text_escape($fdata_res->data)."";
                            break;
                        case EPF_MENU:
                            $options = unserialize($f->data);
                            $options = array_combine(range(1, count($options)), array_values($options));
                            $options[0] = "";
                            ksort($options);
                            $cat_return_string['panels'] .= "".q($options[$fdata_res->data])."";
                            break;
                        case EPF_LINK:
                            $cat_return_string['panels'] .= "<a href='".q($fdata_res->data)."'>".q($fdata_res->data)."</a>";
                            break;
                    }
                    $cat_return_string['panels'] .= "  </div>
                                                     </div>
                                                     </li>";
                }
            }
            $cat_return_string['panels'] .= '</ul>
                       </div>
                   </div>
                </div>';


        }

        if ($showCat) {
            $return_string['panels'] .= $cat_return_string['panels'];
            $return_string['right_menu'] .= $cat_return_string['right_menu'];
        } else {
            $j--;
        }

    }

    $return_string['right_menu'] .= '</nav></nav>
                                 </div>';

    if (!$showAll) {
        $return_string['panels'] = "";
        $return_string['right_menu'] = "";
    }

    return $return_string;
}

/**
 * Render e-portfolio fields in e-portfolio form
 * @return string
 */
function render_eportfolio_fields_form() {
    global $uid, $langOptional, $langCompulsory, $langForm, $langProfileInfoPrivate, $langPublicePortfolioField, $langOpenToRegisteredUsers, 
        $langePortfolioFieldsVisibilitySettings, $langClose;

    $return_string = array();
    $return_string['panels'] = "";
    $return_string['right_menu'] = "<div class='col-sm-3 hidden-xs' id='affixedSideNav' style='margin-top:-23px;'>
    <nav id='navbar-examplePortfolioEdit' class='card-affixed mt-4 flex-column align-items-stretch p-3 sticky-top' style='z-index:0;'>
        <nav class='nav nav-pills flex-column'>";

    $result = Database::get()->queryArray("SELECT id, name FROM eportfolio_fields_category ORDER BY sortorder DESC");

    $j = 0;

    foreach ($result as $c) {

        $res = Database::get()->queryArray("SELECT id, name, shortname, description, required, datatype, data
                                            FROM eportfolio_fields WHERE categoryid = ?d ORDER BY sortorder DESC", $c->id);

        if (count($res) > 0) {


            $return_string['panels'] .= '
           
            <div class="card panelCard card-default px-lg-4 py-lg-3 mb-4" id="EditPortfolio'.$c->id.'">
                                       <div class="card-header border-0 d-flex justify-content-between align-items-center">
                                           <h3>' . q($c->name) .'</h3>
                                       </div>
                                       <div class="card-body">
                                           <fieldset><legend class="mb-0" aria-label="'.$langForm.'"></legend>';
            if ($j == 0) {
                $active = " class='active'";
            } else {
                $active = "";
            }

            $j++;

            $return_string['right_menu'] .= "<a class='nav-link nav-link-adminTools Neutral-900-cl' href='#EditPortfolio$c->id'>" . q($c->name) . "</a>";

            foreach ($res as $f) {

                if (isset($fdata)) {
                    unset($fdata);
                }

                if (Session::hasError('epf_'.$f->shortname)) {
                    $form_class = 'form-group has-error';
                    $help_block = '<span class="help-block Accent-200-cl">' . Session::getError('epf_'.$f->shortname) . '</span>';
                } else {
                    $form_class = 'form-group mb-4';
                    $help_block = '';
                }

                //get data to prefill fields
                $data_res = Database::get()->querySingle("SELECT data, visibility FROM eportfolio_fields_data
                                                      WHERE field_id = ?d AND user_id = ?d", $f->id, $uid);
                if ($data_res) {
                    $fdata = $data_res->data;
                }

                if (Session::has('epf_'.$f->shortname)) {
                    $fdata = Session::get('epf_'.$f->shortname);
                }

                if (isset($fdata) && $fdata != '') {
                    $visibility = $data_res->visibility;
                } else {
                    $visibility = EPF_VISIBLE_PUBLIC;
                }

                if ($visibility == EPF_VISIBLE_USERS) {
                    $visibility_fa_icon = '<i class="fa fa-users"></i>';
                    $fa_icon_title = $langOpenToRegisteredUsers;
                    $users_selected = "selected";
                    $public_selected = $private_selected = "";
                    $hidden_visibility_element = '<input type="hidden" id="visibility_epf_'.$f->shortname.'_hidden" name="visibility_epf_'.$f->shortname.'_hidden" value="'.EPF_VISIBLE_USERS.'">';
                } elseif ($visibility == EPF_VISIBLE_PRIVATE) {
                    $visibility_fa_icon = '<i class="fa fa-lock"></i>';
                    $fa_icon_title = $langProfileInfoPrivate;
                    $private_selected = "selected";
                    $public_selected = $users_selected = "";
                    $hidden_visibility_element = '<input type="hidden" id="visibility_epf_'.$f->shortname.'_hidden" name="visibility_epf_'.$f->shortname.'_hidden" value="'.EPF_VISIBLE_PRIVATE.'">';
                } else { //$visibility == EPF_VISIBLE_PUBLIC
                    $visibility_fa_icon = '<i class="fa fa-globe"></i>';
                    $fa_icon_title = $langPublicePortfolioField;
                    $public_selected = "selected";
                    $private_selected = $users_selected = "";
                    $hidden_visibility_element = '<input type="hidden" id="visibility_epf_'.$f->shortname.'_hidden" name="visibility_epf_'.$f->shortname.'_hidden" value="'.EPF_VISIBLE_PUBLIC.'">';
                }

                $return_string['panels'] .= '<div class="'.$form_class.'">';
                $return_string['panels'] .= '<div class="d-flex align-items-center"><label class="mb-0 title-default" for="epf_'.$f->shortname.'">'.q($f->name).'</label><button type="button" id="visibility_epf_'.$f->shortname.'_button" class="btn btn-link p-0 ms-2" data-bs-toggle="modal" data-bs-target="#visibilityModal-epf_'.$f->shortname.'" title="'.$fa_icon_title.'">'.$visibility_fa_icon.'</button></div>';
                $return_string['panels'] .= '<div class="col-sm-12">';
                $return_string['panels'] .= $hidden_visibility_element;

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
                        $return_string['panels'] .= '<input id="epf_'.$f->shortname.'" class="form-control" '.$val.' type="text" '.$placeholder.' name="epf_'.$f->shortname.'">';
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
                        $return_string['panels'] .= '<input id="epf_'.$f->shortname.'" class="form-control" '.$val.' type="text" '.$placeholder.' name="epf_'.$f->shortname.'" data-provide="datepicker" data-date-format="dd-mm-yyyy">';
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
                        $id_field = "id=epf_" . $f->shortname;
                        $return_string['panels'] .= selection($options, 'epf_'.$f->shortname, $def_selection, $id_field);
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
                        $return_string['panels'] .= '<input id="epf_'.$f->shortname.'" class="form-control" '.$val.' type="text" '.$placeholder.' name="epf_'.$f->shortname.'">';
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

                $return_string['panels'] .= '<div class="modal fade" backdrop="static" id="visibilityModal-epf_'.$f->shortname.'" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">'.$langePortfolioFieldsVisibilitySettings.' — '.$f->name.'</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                      <select class="form-select visibility_select" name="visibility_epf_'.$f->shortname.'">
                        <option value="'.EPF_VISIBLE_PUBLIC.'" '.$public_selected.'>'.$langPublicePortfolioField.'</option>
                        <option value="'.EPF_VISIBLE_USERS.'" '.$users_selected.'>'.$langOpenToRegisteredUsers.'</option>
                        <option value="'.EPF_VISIBLE_PRIVATE.'" '.$private_selected.'>'.$langProfileInfoPrivate.'</option>
                      </select>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-primary" data-bs-dismiss="modal">'.$langClose.'</button>
                    </div>
                  </div>
                </div>
              </div>';
            }

            $return_string['panels'] .= '</fieldset>
                       </div>
                   </div>';

        }
    }

    $return_string['right_menu'] .= '</nav></nav>
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
            $result = Database::get()->querySingle("SELECT id, required, datatype FROM eportfolio_fields WHERE shortname = ?s", $field_name);
            $field_id = $result->id;
            $required = $result->required;
            //delete old values if exist
            if ($required == 1 && empty($value)) {
                continue;
            } else {
                Database::get()->query("DELETE FROM eportfolio_fields_data WHERE field_id = ?d AND user_id = ?d", $field_id, $uid);
            }

            if (!empty($value)) {
                if ($result->datatype == EPF_TEXTAREA) {
                    $value = purify($value);
                }

                if (isset($_POST['visibility_epf_'.$field_name.'_hidden']) && in_array($_POST['visibility_epf_'.$field_name.'_hidden'], [EPF_VISIBLE_PUBLIC, EPF_VISIBLE_USERS, EPF_VISIBLE_PRIVATE])) {
                    $visibility = intval($_POST['visibility_epf_'.$field_name.'_hidden']);
                } else {
                    $visibility = EPF_VISIBLE_PUBLIC;
                }

                Database::get()->query("INSERT INTO eportfolio_fields_data (user_id, field_id, data, visibility) VALUES (?d,?d,?s,?d)", $uid, $field_id, $value, $visibility);
                
            }
            $updated = true;
        }
    }
    return $updated;
}

function epf_validate(&$valitron_object) {
    global $langCPFLinkValidFail, $langCPFDateValidFail, $langTheFieldIsRequired, $langGScholarURLValidFail, $langOrcidURLValidFail, 
        $langScopusIDValidFail, $langFacebookUrlValidFail, $langTwitterUrlValidFail, $langLinkedInUrlValidFail, $langInvalidEmail;

    $valitron_object->addRule('gscholarURL', function($field, $value, array $params, array $fields) {
        return preg_match('/^https?:\/\/scholar\.google\.[a-z.]+\/citations\?user=[a-zA-Z0-9_-]+$/', $value);
    });

    $valitron_object->addRule('orcid', function($field, $value, array $params, array $fields) {
        return preg_match('/^(https:\/\/orcid\.org\/)?\d{4}-\d{4}-\d{4}-\d{3}[\dX]$/', $value);
    });

    $valitron_object->addRule('facebook', function($field, $value, array $params, array $fields) {
        return preg_match('/^https:\/\/(www\.)?(facebook\.com|fb\.me)\/(p\/)?[a-zA-Z0-9\.]+\/?$/', $value);
    });

    $valitron_object->addRule('twitter', function($field, $value, array $params, array $fields) {
        return preg_match('/^https:\/\/(twitter\.com|x\.com)\/[A-Za-z0-9_]{1,15}\/?(\?[a-zA-Z0-9=&_]*)?$/', $value);
    });

    $valitron_object->addRule('linkedin', function($field, $value, array $params, array $fields) {
        return preg_match('/^https:\/\/(www\.)?linkedin\.com\/(in|pub|company)\/[a-zA-Z0-9\-_%]+\/?$/', $value);
    });

    foreach ($_POST as $key => $value) {
        if (substr($key, 0, 4) == 'epf_') { //e-portfolio fields input names start with epf_
            $shortname = substr($key, 4);
            $result = Database::get()->querySingle("SELECT name, datatype, required FROM eportfolio_fields WHERE shortname = ?s", $shortname);
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
                if ($shortname == 'gscholar') {
                    $valitron_object->rule('gscholarURL', $key)->message(sprintf($langGScholarURLValidFail, q($field_name)))->label($field_name);
                } elseif ($shortname == 'orcid') {
                    $valitron_object->rule('orcid', $key)->message(sprintf($langOrcidURLValidFail, q($field_name)))->label($field_name);
                } elseif ($shortname == 'fb') {
                    $valitron_object->rule('facebook', $key)->message(sprintf($langFacebookUrlValidFail, q($field_name)))->label($field_name);
                } elseif ($shortname == 'twitter') {
                    $valitron_object->rule('twitter', $key)->message(sprintf($langTwitterUrlValidFail, q($field_name)))->label($field_name);
                } elseif ($shortname == 'linkedin') {
                    $valitron_object->rule('linkedin', $key)->message(sprintf($langLinkedInUrlValidFail, q($field_name)))->label($field_name);
                } else {
                    $valitron_object->rule('url', $key)->message(sprintf($langCPFLinkValidFail, q($field_name)))->label($field_name);
                }
            } elseif ($datatype == EPF_DATE) {
                $valitron_object->rule('date', $key)->message(sprintf($langCPFDateValidFail, q($field_name)))->label($field_name);
            }

            if ($datatype == EPF_TEXTBOX) {
                if ($shortname == 'scopus') {
                    $valitron_object->rule('numeric', $key)->message(sprintf($langScopusIDValidFail, q($field_name)))->label($field_name);
                    $valitron_object->rule('lengthMin', $key, 9)->message(sprintf($langScopusIDValidFail, q($field_name)))->label($field_name);
                    $valitron_object->rule('lengthMax', $key, 11)->message(sprintf($langScopusIDValidFail, q($field_name)))->label($field_name);
                } elseif ($shortname == 'email') {
                    $valitron_object->rule('email', $key)->message(sprintf($langInvalidEmail, q($field_name)))->label($field_name);
                }
            }
        }
    }
}

function calculate_eportfolio_completion($user_id) {
    // language shortnames
    $language_fields = ['el', 'en', 'sq', 'ar', 'fr', 'es', 'de', 'it', 'zh', 'ru', 'tr', 'other_languages']; //this group is considered as one field

    // get all fields
    $all_fields = Database::get()->queryArray("SELECT id, shortname FROM eportfolio_fields");
    $completed_fields = Database::get()->queryArray("SELECT field_id FROM eportfolio_fields_data WHERE user_id = ?s", $user_id);
    
    $total_fields = 0;
    $completed_fields_num = 0;
    $language_field_ids = [];
    $language_group_completed = false;

    foreach ($all_fields as $field) {
        $shortname = $field->shortname;
        $field_id = $field->id;

        if (in_array($shortname, $language_fields)) {
            $language_field_ids[] = $field_id;
        } else {
            $total_fields++;
        }
    }

    foreach ($completed_fields as $completed_field) {
        if (in_array($completed_field->field_id, $language_field_ids)) {
            $language_group_completed = true;
        } else {
            $completed_fields_num++;
        }
    }

    if (!empty($language_field_ids)) {
        $total_fields++;
        if ($language_group_completed) {
            $completed_fields_num++;
        }
    }

    if ($total_fields === 0) {
        return 0;
    }

    $percentage = ($completed_fields_num / $total_fields) * 100;
    return round($percentage, 2);
}


