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

    public function __construct() {
        if (isset($_REQUEST['localize'])) {
            $this->language = $_SESSION['langswitch'] = validate_language_code($_REQUEST['localize']);
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
            $this->status = $_SESSION['status'];
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

}
