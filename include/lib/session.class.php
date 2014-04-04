<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2013  Greek Universities Network - GUnet
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

/**
 * Eclass Session object
 *
 * Holds information about the current logged-in user
 */
class Session {

    public $user_id;
    public $username;
    public $givenname;
    public $surname;
    public $fullname;
    public $status;
    public $course_id;
    public $course_code;
    public $course_title;
    public $courses;
    public $language;
    public $active_ui_languages;
    public $native_language_names;

    public function __construct() {
        global $native_language_names_init;
        
        $this->active_ui_languages = explode(' ', get_config('active_ui_languages'));
        // Set active user interface languages
        $this->native_language_names = array();
        foreach ($this->active_ui_languages as $langcode) {
            if (isset($native_language_names_init[$langcode])) {
                $this->native_language_names[$langcode] = $native_language_names_init[$langcode];
            }
        }
        if (isset($_REQUEST['localize'])) {
            $this->language = $_SESSION['langswitch'] = $this->validate_language_code($_REQUEST['localize']);
        } elseif (isset($_SESSION['langswitch'])) {
            $this->language = $_SESSION['langswitch'];
        } else {
            $this->language = get_config('default_language');
        }

        if (isset($_SESSION['uid'])) {
            $this->user_id = $_SESSION['uid'];
        } else {
            $this->user_id = 0;
        }
        if (isset($_SESSION['status'])) {
            $this->status = intval($_SESSION['status']);
        } else {
            $this->status = 0;
        }
    }

    public function logout() {
        unset($this->user_id);
        unset($this->username);
        unset($this->givenname);
        unset($this->surname);
        unset($this->fullname);
        unset($this->status);
        unset($this->courses);
        unset($this->language);
    }
    public static function render_flashdata($item='default') {
        if (!isset($_SESSION['messages'][$item])) {
            return null;
        }
        $item_messages = $_SESSION['messages'][$item];
        unset($_SESSION['messages'][$item]);
        $msg_boxes = '';
        foreach($item_messages as $row => $value){
            $msg_boxes .= "<div class='$row'><ul><li>".implode('</li><li>', $value)."</li></ul></div>";
        }
        return $msg_boxes;
    }

    public static function set_flashdata($message, $class, $item='default') {
        if (!isset($_SESSION['messages'])) {
            $_SESSION['messages'] = array();
        }
        $_SESSION['messages'][$item][$class][] = $message;
    }
    
    // Make sure a language code is valid - if not, default language is Greek
    public function validate_language_code($langcode, $default = 'el') {     
        if (array_search($langcode, $this->active_ui_languages) === false) {
            return $default;
        } else {
            return $langcode;
        }
    }
}
