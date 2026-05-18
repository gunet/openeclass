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

    global $langEduEmpl, $langAchievements, $langGoalsSkills, $langContactInfo;

    // These fields are displayed in the profile card — skip them from category cards
    $profile_shortnames = ['birth_date', 'birth_place', 'gender', 'about_me', 'personal_website'];

    $showAll = false;

    $return_string = array();
    $return_string['panels'] = "";
    $return_string['right_menu'] = "<div class='col-sm-3 hidden-xs' id='affixedSideNav'>
    <nav id='navbar-exampleIndexPortfolio' class='card-affixed flex-column align-items-stretch px-3 pb-3 sticky-top' style='z-index:0; top:70px; max-height:calc(100vh - 80px); overflow-y:auto;'>
        <nav class='nav nav-pills flex-column'>";

    $result = Database::get()->queryArray("SELECT id, name FROM eportfolio_fields_category ORDER BY sortorder DESC");

    $category_icons = [
        $langEduEmpl      => 'fa-solid fa-graduation-cap',
        $langAchievements => 'fa-solid fa-award',
        $langGoalsSkills  => 'fa-solid fa-bullseye',
        $langContactInfo  => 'fa-regular fa-address-book',
    ];

    $j = 0;

    foreach ($result as $c) {

        $showCat = false;
        $cat_rows = [];
        $cat_return_string = array();
        $cat_return_string['panels'] = "";
        $cat_return_string['right_menu'] = "";

        $cat_icon = isset($category_icons[$c->name]) ? $category_icons[$c->name] : 'fa-solid fa-circle-dot';

        $res = Database::get()->queryArray("SELECT id, shortname, name, datatype, data FROM eportfolio_fields WHERE categoryid = ?d ORDER BY sortorder DESC", $c->id);

        if (count($res) > 0) {
            $cat_return_string['panels'] .= '
            <div class="card panelCard card-default rounded-3" style="border: 1px solid #dee2e6; scroll-margin-top: 70px;" id="IndexPortfolio'.$c->id.'">
                <div class="card-body px-3 py-0">
                <div class="d-flex align-items-center gap-2 border-bottom py-3">
                    <i class="'.$cat_icon.' Primary-500-cl"></i>
                    <h2 class="text-heading-h3 mb-0">'. q($c->name) .'</h2>
                </div>';

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

                if (in_array($f->shortname, $profile_shortnames)) {
                    continue;
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

                    $row  = '<div class="d-flex align-items-start border-bottom py-3">';
                    $row .= '<div class="col-4">'.q($f->name).'</div>';
                    $row .= '<div class="col-8">';

                    switch ($f->datatype) {
                        case EPF_DATE:
                        case EPF_TEXTBOX:
                            if ($f->shortname == 'scopus') {
                                $row .= "<a href='https://www.scopus.com/authid/detail.uri?authorId=".q($fdata_res->data)."'>".q($fdata_res->data)."</a>";
                            } else {
                                $row .= q($fdata_res->data);
                            }
                            break;
                        case EPF_TEXTAREA:
                            $row .= standard_text_escape($fdata_res->data);
                            break;
                        case EPF_MENU:
                            $options = unserialize($f->data);
                            $options = array_combine(range(1, count($options)), array_values($options));
                            $options[0] = "";
                            ksort($options);
                            $row .= q($options[$fdata_res->data]);
                            break;
                        case EPF_LINK:
                            $row .= "<a href='".q($fdata_res->data)."'>".q($fdata_res->data)."</a>";
                            break;
                    }
                    $row .= "</div></div>";
                    $cat_rows[] = $row;
                }
            }

           
            if (!empty($cat_rows)) {
                $last = array_pop($cat_rows);
                $cat_rows[] = str_replace('border-bottom py-3', 'py-3', $last);
            }
            $cat_return_string['panels'] .= implode('', $cat_rows);
            $cat_return_string['panels'] .= '</div></div>';


        }

        if ($showCat) {
            $return_string['panels'] .= $cat_return_string['panels'];
            $return_string['right_menu'] .= $cat_return_string['right_menu'];
        } else {
            $j--;
        }

    }

    $return_string['right_menu'] .= '</nav></nav></div>';

    if (!$showAll) {
        $return_string['panels'] = "";
        $return_string['right_menu'] = "";
    }

    return $return_string;
}

/**
 * Render the e-portfolio top profile card (photo, name, demographics, bio, link)
 * @param int $uid
 * @return string
 */
function render_eportfolio_profile_card($uid) {
    global $urlServer, $langCopy, $langCopiedSucc, $langCopiedErr;

    // Same visibility logic as render_eportfolio_fields_content
    if (!isset($_SESSION['uid'])) {
        $visibility_query = '=' . EPF_VISIBLE_PUBLIC;
    } elseif ($_SESSION['uid'] == $uid) {
        $visibility_query = '<=' . EPF_VISIBLE_PRIVATE;
        if (isset($_GET['view'])) {
            if ($_GET['view'] == 'public') {
                $visibility_query = '=' . EPF_VISIBLE_PUBLIC;
            } elseif ($_GET['view'] == 'registered') {
                $visibility_query = '<=' . EPF_VISIBLE_USERS;
            }
        }
    } else {
        $visibility_query = '<=' . EPF_VISIBLE_USERS;
    }

    $user = Database::get()->querySingle(
        "SELECT surname, givenname, eportfolio_enable, eportfolio_token FROM user WHERE id = ?d", $uid
    );
    if (!$user) return '';

    $name    = q($user->givenname) . ' ' . q($user->surname);

    // Always show the photo on eportfolio regardless of pic_public setting
    global $webDir, $urlAppend, $themeimg;
    $hash = profile_image_hash($uid);
    $hashed_file = "courses/userimg/{$uid}_{$hash}_" . IMAGESIZE_LARGE . ".jpg";
    if (file_exists($webDir . '/' . $hashed_file)) {
        $photo = $urlAppend . $hashed_file;
    } elseif (file_exists($webDir . "/courses/userimg/{$uid}_" . IMAGESIZE_LARGE . ".jpg")) {
        $photo = $urlAppend . "courses/userimg/{$uid}_" . IMAGESIZE_LARGE . ".jpg";
    } else {
        $photo = "$themeimg/default_" . IMAGESIZE_LARGE . ".png";
    }

    $demographics  = [];
    $about_me_html = '';

    $fields = Database::get()->queryArray(
        "SELECT f.id, f.shortname, f.datatype, f.data
         FROM eportfolio_fields f
         WHERE f.shortname IN ('birth_date', 'birth_place', 'gender', 'about_me')
         ORDER BY f.sortorder DESC"
    );

    foreach ($fields as $f) {
        $fdata_res = Database::get()->querySingle(
            "SELECT data FROM eportfolio_fields_data
             WHERE user_id = ?d AND field_id = ?d AND visibility " . $visibility_query,
            $uid, $f->id
        );
        if (!$fdata_res || $fdata_res->data === '' || ($f->datatype == EPF_MENU && $fdata_res->data == 0)) {
            continue;
        }
        switch ($f->shortname) {
            case 'birth_place':
            case 'birth_date':
                $demographics[] = q($fdata_res->data);
                break;
            case 'gender':
                $options = unserialize($f->data);
                $options = array_combine(range(1, count($options)), array_values($options));
                if (!empty($options[$fdata_res->data])) {
                    $demographics[] = q($options[$fdata_res->data]);
                }
                break;
            case 'about_me':
                $about_me_html = standard_text_escape($fdata_res->data);
                break;
        }
    }

    $demographics_html = !empty($demographics)
        ? "<p class='small Neutral-900-cl mb-1 mt-1'>" . implode(' &nbsp;|&nbsp; ', $demographics) . "</p>"
        : '';
    $about_html = $about_me_html ? "<p class='small mb-0 mt-1'>$about_me_html</p>" : '';

    $link_html = '';
    if ($user->eportfolio_token) {
        $public_url = $urlServer . 'main/eportfolio/index.php?token=' . $user->eportfolio_token;
        $link_html = "
            <div class='me-4' style='margin-top:10px;'>
                <div style='border-top: 1px solid #dee2e6; margin-bottom: 10px;'></div>
                <div class='d-flex align-items-center gap-2'>
                    <div class='d-flex align-items-center border rounded-2 flex-grow-1 px-3' style='height:32px;'>
                        <i class='fa-solid fa-link Primary-500-cl me-2 flex-shrink-0' style='line-height:1;'></i>
                        <input id='page-link-card' type='text'
                               class='form-control border-0 shadow-none px-0 bg-transparent'
                               style='font-size:0.85rem; padding-top:2px; padding-bottom:0; line-height:1;'
                               value='" . q($public_url) . "' readonly>
                    </div>
                    <button class='btn btn-light border rounded-2 flex-shrink-0 p-0' id='copy-btn-card'
                            data-bs-toggle='tooltip' data-bs-placement='bottom' title='" . q($langCopy) . "'
                            style='width:32px;height:32px;'>
                        <i class='fa-regular fa-copy Neutral-500-cl'></i>
                    </button>
                </div>
            </div>
            <script>
            $(function() {
                if (typeof Clipboard !== 'undefined') {
                    var cbCard = new Clipboard('#copy-btn-card', {
                        target: function() { return document.getElementById('page-link-card'); }
                    });
                    cbCard.on('success', function(e) {
                        e.clearSelection();
                        \$('#copy-btn-card').attr('title', '" . js_escape($langCopiedSucc) . "').tooltip('fixTitle').tooltip('show');
                    });
                    cbCard.on('error', function(e) {
                        \$('#copy-btn-card').attr('title', '" . js_escape($langCopiedErr) . "').tooltip('fixTitle').tooltip('show');
                    });
                }
            });
            </script>";
    }

    return "
        <div class='card panelCard card-default rounded-3' style='border: 1px solid #dee2e6;'>
            <div class='card-body px-3 py-3'>
                <div class='d-flex align-items-start gap-4'>
                    <img class='rounded-circle flex-shrink-0 ms-4 mt-2' style='width:120px;height:120px;object-fit:cover;'
                         src='" . q($photo) . "' alt='" . $name . "'>
                    <div class='flex-grow-1'>
                        <div class='fs-6 fw-bold'>$name</div>
                        $demographics_html
                        $about_html
                        $link_html
                    </div>
                </div>
            </div>
        </div>";
}

/**
 * Render e-portfolio fields in e-portfolio form
 * @return string
 */
function render_eportfolio_fields_form() {
    global $uid, $langOptional, $langCompulsory, $langForm, $langProfileInfoPrivate, $langPublicePortfolioField, $langOpenToRegisteredUsers,
        $langePortfolioFieldsVisibilitySettings, $langClose,
        $langPersInfo, $langAddPicture, $langReplacePicture, $langDeletePicture;

    $return_string = array();
    $return_string['panels'] = "";
    $return_string['right_menu'] = "<div class='col-sm-3 hidden-xs' id='affixedSideNav'>
    <nav id='navbar-examplePortfolioEdit' class='card-affixed flex-column align-items-stretch px-3 pb-3 sticky-top' style='z-index:0; top:70px; max-height:calc(100vh - 80px); overflow-y:auto;'>
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
                                           <h2 class="text-heading-h3">' . q($c->name) .'</h2>
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

            // Photo upload field in personal info category
            if ($c->name == $langPersInfo) {
                $user_has_icon = Database::get()->querySingle("SELECT has_icon FROM user WHERE id = ?d", $uid)->has_icon;
                $photo_url = user_icon($uid, IMAGESIZE_LARGE);
                $pic_label = $user_has_icon ? $langReplacePicture : $langAddPicture;
                $delete_display = $user_has_icon ? '' : ' style="display:none;"';
                $return_string['panels'] .= '
                <div class="form-group mb-4 d-flex align-items-center gap-3">
                    <img id="profile-img-preview" class="rounded-circle flex-shrink-0" style="width:80px;height:80px;object-fit:cover;" src="' . q($photo_url) . '" alt="">
                    <div class="flex-grow-1">
                        <label class="pic-label control-label-notes mb-2">' . $pic_label . '</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="file" name="userimage" class="form-control" accept="image/*">
                            <button type="button" id="delete-profile-img" class="btn deleteAdminBtn flex-shrink-0"' . $delete_display . '>
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>';
            }

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
                $return_string['panels'] .= '<div class="d-flex align-items-center"><label class="mb-0 title-default" for="epf_'.$f->shortname.'">'.q($f->name).'</label><button type="button" id="visibility_epf_'.$f->shortname.'_button" class="btn p-0 ms-2" style="color:#adb5bd;" data-bs-toggle="modal" data-bs-target="#visibilityModal-epf_'.$f->shortname.'" title="'.$fa_icon_title.'">'.$visibility_fa_icon.'</button></div>';
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
                        $return_string['panels'] .= rich_text_editor('epf_'.$f->shortname, 8, 20, $val, options: array('id' => 'epf_'.$f->shortname));
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
                      <h5 class="modal-title">'.$langePortfolioFieldsVisibilitySettings.' — '.q($f->name).'</h5>
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


