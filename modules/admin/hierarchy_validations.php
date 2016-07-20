<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

/* ==============================================================================
  @Description: Helper Functions for validations specific to the Hierarchy Tree

  Used for validating the department manager admin role

  ============================================================================== */

/**
 * Validate a tree node's existence (and proper integer) based on its id value.
 * Optionally, validate that the current user has proper access for this node
 * (the node must be within the nodes subtree that this user belongs to).
 *
 * @param int     $id       - The node's id value
 * @param boolean $checkOwn - Optional validation (if true) of current user's node access
 */
function validateNode($id, $checkOwn) {
    global $head_content, $tree, $user, $uid,
    $langBack, $langNotAllowed;

    $notallowed = "$langNotAllowed";

    if ($id <= 0) {
        exitWithError($notallowed);
    }

    if (!Database::get()->querySingle("SELECT * FROM hierarchy WHERE id = ?d", $id)) {
        exitWithError($notallowed);
    }

    if ($checkOwn) {
        $subtrees = $tree->buildSubtrees($user->getDepartmentIds($uid));

        if (!in_array($id, $subtrees)) {
            exitWithError($notallowed);
        }
    }
}

/**
 * Validate a tree node's existence (and proper integer) based on its id value.
 * Optionally, validate that the current user has proper access for this node 
 * (the node must be within the nodes subtree that this user belongs to).
 * 
 * @param int     $id       - The node's id value
 * @param boolean $checkOwn - Optional validation (if true) of current user's node access
 */
function validateParentId($id, $checkOwn) {
    global $head_content, $tree, $user, $uid, $langBack, $langNotAllowed;
    $notallowed = "$langNotAllowed";

    // special parent check
    if ((!$checkOwn && $id < 0) || ($checkOwn && $id <= 0)) {
        exitWithError($notallowed);
    }

    if ($id > 0 && !Database::get()->querySingle("SELECT * FROM hierarchy WHERE id = ?d", $id)) {
        exitWithError($notallowed);
    }

    if ($id > 0 && $checkOwn) {
        $subtrees = $tree->buildSubtrees($user->getDepartmentIds($uid));

        if (!in_array($id, $subtrees)) {
            exitWithError($notallowed);
        }
    }
}

/**
 * Validate a user's existence (and proper integer) based on its userId value.
 * Optionally, validate that the current user has proper access for this given user
 * (the given user must be within the nodes subtree that the current user belongs to).
 *
 * @param int     $userId   - The user's id
 * @param boolean $checkOwn - Optional validation (if true) of current user's node access
 */
function validateUserNodes($userId, $checkOwn) {
    global $head_content, $tree, $user, $uid,
    $langBack, $langNotAllowed;

    $notallowed = "$langNotAllowed";

    if ($userId <= 0) {
        exitWithError($notallowed);
    }

    $deps = $user->getDepartmentIds(intval($userId));

    if (empty($deps)) {
        exitWithError($notallowed);
    }

    if ($checkOwn) {
        $atleastone = false;
        $subtrees = $tree->buildSubtrees($user->getDepartmentIds($uid));

        foreach ($deps as $depId) {
            if (in_array($depId, $subtrees)) {
                $atleastone = true;
            }
        }

        if (!$atleastone) {
            exitWithError($notallowed);
        }
    }
}

/**
 * Validate a course's existence (and proper integer) based on its courseId value.
 * Optionally, validate that the current user has proper access for this given course
 * (the given course must be within the nodes subtree that the current user belongs to).
 *
 * @param int     $courseId - The course's id
 * @param boolean $checkOwn - Optional validation (if true) of current user's node access
 */
function validateCourseNodes($courseId, $checkOwn) {
    global $head_content, $tree, $course, $user, $uid, $langBack, $langNotAllowed;

    $notallowed = "$langNotAllowed";

    if ($courseId <= 0) {
        exitWithError($notallowed);
    }

    $deps = $course->getDepartmentIds(intval($courseId));

    if (empty($deps)) {
        exitWithError($notallowed);
    }

    if ($checkOwn) {
        $atleastone = false;
        $subtrees = $tree->buildSubtrees($user->getDepartmentIds($uid));

        foreach ($deps as $depId) {
            if (in_array($depId, $subtrees)) {
                $atleastone = true;
            }
        }

        if (!$atleastone) {
            exitWithError($notallowed);
        }
    }
}

/**
 * Terminate execution and display an (optional) error message.
 * 
 * @param string $message - The optional error message to display 
 */
function exitWithError($message) {    
    Session::Messages($message, 'alert-danger');
}

/**
 * Checks if the current user's role is Department Admin.
 * The role is defined by having specific permissions (dep/user manage)
 * and lacking specific permissions (power and admin).
 * 
 * @return boolean $checkOwn
 */
function isDepartmentAdmin() {
    global $is_departmentmanage_user, $is_usermanage_user, $is_power_user, $is_admin;

    $checkOwn = false;

    // check if department manager
    if ($is_departmentmanage_user && $is_usermanage_user && !$is_power_user && !$is_admin) {
        $checkOwn = true;
    }

    return $checkOwn;
}
