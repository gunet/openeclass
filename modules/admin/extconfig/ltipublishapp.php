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

require_once 'genericrequiredparam.php';

class LtiPublishApp extends ExtApp {

    const FRAMEANCESTORS = "frameancestors";
    const ENABLEDCOURSES = "enabledcourses";

    public function __construct() {
        parent::__construct();
        $this->registerParam(new GenericRequiredParam($this->getName(), "Frame ancestors", LtiPublishApp::FRAMEANCESTORS));
        $this->registerParam(new GenericRequiredParam($this->getName(), "Enabled courses", LtiPublishApp::ENABLEDCOURSES, "0"));
    }

    public function getDisplayName(): string {
        return "LTI Publish";
    }

    public function getShortDescription() {
        return $GLOBALS['langLtiPublishShortDescription'];
    }

    public function getLongDescription() {
        return $GLOBALS['langLtiPublishLongDescription'];
    }

    public function getConfigUrl(): string {
        return 'modules/admin/ltipublishconf.php';
    }

    public function isEnabledForCurrentCourse(): bool {
        global $course_id;
        if ($this->isEnabled() && isset($course_id)) {
            $ltienabledcourses = explode(",", get_config('ext_ltipublish_enabledcourses'));
            if (in_array(0, $ltienabledcourses) || in_array($course_id, $ltienabledcourses)) {
                return true;
            }
        }
        return false;
    }

    public function isEnabledForCourse($course_id): bool {
        if ($this->isEnabled() && isset($course_id)) {
            $ltienabledcourses = explode(",", get_config('ext_ltipublish_enabledcourses'));
            if (in_array(0, $ltienabledcourses) || in_array($course_id, $ltienabledcourses)) {
                return true;
            }
        }
        return false;
    }

    public function getFramebustHeader(): string {
        global $lti_allow_framing;
        $framebustheader = 'X-Frame-Options: SAMEORIGIN';
        if ($this->isEnabledForCurrentCourse() or $lti_allow_framing ?? false) {
            $frameancestors = get_config('ext_ltipublish_frameancestors');
            $sources = explode(",", $frameancestors);
            if ($sources !== false && count($sources) > 0) {
                $framebustheader = "Content-Security-Policy: frame-ancestors 'self'";
                foreach ($sources as $source) {
                    $framebustheader .= " " . $source;
                }
                $framebustheader .= ";";
            }
        }
        return $framebustheader;
    }

}
