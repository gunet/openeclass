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


class Access {
    public $isValid = false;
    public $allCourses = true;
    public $courseIDs = null;
    public $courseCodes = null;
    public $allowedDepartments = null;
    public $token;


    /**
     * Get all descendant department IDs
     * @param int|null $departmentId
     * @return array|null
     */
    public static function getDepartmentDescendants($departmentId) {
        if ($departmentId === null) {
            return null;
        }
        
        // Get the lft and rgt values of the parent department
        $parent = Database::get()->querySingle("SELECT lft, rgt FROM hierarchy WHERE id = ?d", $departmentId);
        
        if (!$parent) {
            return [];
        }
        
        // Get all descendants where their lft is between parent's lft and rgt
        $descendants = Database::get()->queryArray("
            SELECT id 
            FROM hierarchy 
            WHERE lft BETWEEN ?d AND ?d
        ", $parent->lft, $parent->rgt);
        
        return array_map(function($dept) {
            return $dept->id;
        }, $descendants);
    }


    /**
     * Check if a user belongs to allowed departments
     * @param int $user_id
     * @param array|null $allowedDepartments
     * @return bool
     */
    public static function checkUserDepartmentAccess($user_id, $allowedDepartments) {
        if ($allowedDepartments === null) {
            return true;
        }
        
        $placeholders = implode(',', array_fill(0, count($allowedDepartments), '?d'));
        $allowed = Database::get()->querySingle("
            SELECT COUNT(*) as count
            FROM user_department 
            WHERE user = ?d 
            AND department IN ($placeholders)
        ", $user_id, ...$allowedDepartments);
        
        return $allowed && $allowed->count > 0;
    }


    /**
     * Check if a course belongs to allowed departments
     * @param int $course_id
     * @param array|null $allowedDepartments
     * @return bool
     */
    public static function checkCourseDepartmentAccess($course_id, $allowedDepartments) {
        if ($allowedDepartments === null) {
            return true;
        }
        
        $placeholders = implode(',', array_fill(0, count($allowedDepartments), '?d'));
        $allowed = Database::get()->querySingle("
            SELECT COUNT(*) as count
            FROM course_department 
            WHERE course = ?d 
            AND department IN ($placeholders)
        ", $course_id, ...$allowedDepartments);
        
        return $allowed && $allowed->count > 0;
    }


    /**
     * Build user department filter clause and parameters for SQL queries
     * @param array|null $allowedDepartments
     * @return array [filter_clause, parameters_array]
     */
    public static function buildUserDepartmentFilter($allowedDepartments) {
        if ($allowedDepartments === null) {
            return ['', []];
        }
        
        $placeholders = implode(',', array_fill(0, count($allowedDepartments), '?d'));
        $filter = " AND EXISTS (
            SELECT 1 FROM user_department ud 
            WHERE ud.user = user.id 
            AND ud.department IN ($placeholders)
        )";
        
        return [$filter, $allowedDepartments];
    }


    /**
     * Try to determine if a token was set in the request and if so return it
     * @return string|null
     */
    public static function getToken() {

        if (isset($_GET['token']) and $_GET['token'] !== '') {
            return $_GET['token'];
        } elseif (isset($_POST['token']) and $_POST['token'] !== '') {
            return $_POST['token'];
        } elseif (isset($_SERVER['Authorization'])) {
            $header = trim($_SERVER['Authorization']);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $header = trim($_SERVER['HTTP_AUTHORIZATION']);
        } else {
            foreach (getallheaders() as $name => $value) {
                if (strtolower($name) == 'authorization') {
                    $header = trim($value);
                    break;
                }
            }
        }
        if (isset($header) and preg_match('/Bearer\s+(\S+)/', $header, $m)) {
            return $m[1];
        }
        return null;
    }

    /**
     * Check a token for validity and return the corresponding Access object
     * @param string $token
     * @return Access
     */
    public static function fromToken($token) {
        $access = new Access();
        $access->token = $token;

        $ip = Log::get_client_ip();
        $result = Database::get()->querySingle('SELECT *, expired < NOW() AS token_expired
            FROM api_token WHERE token = ?s', $token);
        if ($result
                and $result->enabled
                and !$result->token_expired
                and (!$result->ip
                     or match_ip_to_ip_or_cidr($ip, explode(' ', canonicalize_whitespace($result->ip))))) {
            $access->isValid = true;

            if ($result->department_id !== null) {
                $access->allowedDepartments = self::getDepartmentDescendants($result->department_id);
            }

            if ($result->all_courses) {
                $access->allCourses = true;
            } else {
                $access->allCourses = false;
                $courses = Database::get()->queryArray('SELECT course_id, course.code
                    FROM api_token_course JOIN course
                        ON course_id = course.id AND token_id = ?d', $result->id);
                $access->courseCodes = array_map(function ($course) {
                    return $course->code;
                }, $courses);
                $access->courseIDs = array_map(function ($course) {
                    return $course->course_id;
                }, $courses);
            }
        }
        return $access;
    }

    /**
     * End the request with an error
     * @param int $code Error code
     * @param string $message Error message
     * @param int $http_response HTTP response status code
     */
    public static function error($code, $message, $http_response = 400) {
        http_response_code($http_response);
        header('Content-Type: application/json');
        echo json_encode([
            'errorcode' => $code,
            'errormessage' => $message,
        ], JSON_UNESCAPED_UNICODE), "\n";
        exit;
    }
}
