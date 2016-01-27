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
 * Render custom profile fields content when viewing profile
 * @param array $context
 * @return string
 */
function render_eportfolio_fields_content($uid) {
    global $langProfileNotAvailable;
    
    $return_str = '';

    $result = Database::get()->queryArray("SELECT id, name FROM eportfolio_fields_category ORDER BY sortorder DESC");

    foreach ($result as $cat) {
        $res = Database::get()->queryArray("SELECT id, name, datatype, data FROM eportfolio_fields
               WHERE categoryid = ?d ORDER BY sortorder DESC", $cat->id);

        if (count($res) > 0) { //category start
        $return_str .= "<div class='row'>
        <div class='col-xs-12 col-md-10 col-md-offset-2 profile-pers-info'>
        <h4>".$cat->name."</h4>";
        }

        foreach ($res as $f) { //get user data for each field
            $return_str .= "<div class='profile-pers-info-data'>";

            $fdata_res = Database::get()->querySingle("SELECT data FROM eportfolio_fields_data
            WHERE user_id = ?d AND field_id = ?d", $uid, $f->id);

            $return_str .= "<span class='tag'>".$f->name." : </span>";

            if (!$fdata_res || $fdata_res->data == '') {
                $return_str .= " <span class='tag-value not_visible'> - $langProfileNotAvailable - </span>";
            } else {
                $return_str .= "";
                switch ($f->datatype) {
                    case EPF_TEXTBOX:
                        $return_str .= "<span class='tag-value'>".q($fdata_res->data)."</span>";
                        break;
                    case EPF_TEXTAREA:
                        $return_str .= "<span class='tag-value'>".standard_text_escape($fdata_res->data)."</span>";
                        break;
                    case EPF_DATE:
                        $return_str .= "<span class='tag-value'>".q($fdata_res->data)."</span>";
                        break;
                    case EPF_MENU:
                        $options = unserialize($f->data);
                        $options = array_combine(range(1, count($options)), array_values($options));
                        $options[0] = "";
                        ksort($options);
                        $return_str .= "<span class='tag-value'>".q($options[$fdata_res->data])."</span>";
                        break;
                    case EPF_LINK:
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
