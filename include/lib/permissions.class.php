<?php

class Permissions
{

    public int $user_id;
    public int $course_id;
    public bool $course_admin;

    public function __construct()
    {
        $this->user_id = 0;
        $this->course_id = 0;
        $this->course_admin = false;

        if (isset($_SESSION['uid'])) {
            $this->user_id = $_SESSION['uid'];
        }
        if (isset($GLOBALS['course_id'])) {
            $this->course_id = $GLOBALS['course_id'];
        }
        if (isset($GLOBALS['is_course_admin']) and $GLOBALS['is_course_admin']) {
            $this->course_admin = true;
        }

    }

    /**
     * @brief if we want a specific user
     * @param int $user_id
     * @return void
     */
    public function set_user_id(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    /**
     * @brief if we want a specific course
     * @param int $course_id
     * @return void
     */
    public function set_course_id(int $course_id): void {
        $this->course_id = $course_id;
    }


    /**
     * @brief check if user has permission to manage modules
     * @return bool
     */
    public function has_course_modules_permission(): bool
    {

        if ($this->course_admin) {
            return true;
        }

        $q = Database::get()->querySingle("SELECT admin_modules FROM user_permissions
                                                WHERE user_id = ?d
                                                AND course_id = ?d",
                    $this->user_id, $this->course_id);

        if ($q and $q->admin_modules) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * @brief check if user has permission to manage users
     * @return bool
     */
    public function has_course_users_permission(): bool
    {

        if ($this->course_admin) {
            return true;
        }

        $q = Database::get()->querySingle("SELECT admin_users FROM user_permissions
                                            WHERE user_id = ?d
                                            AND course_id = ?d",
            $this->user_id, $this->course_id);
        if ($q and $q->admin_users) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @brief check if user has permission to clone course
     * @return bool
     */
    public function has_course_clone_permission(): bool
    {

        if (get_config('allow_teacher_clone_course') and $this->course_admin) {
            return true;
        }

        $q = Database::get()->querySingle("SELECT course_clone FROM user_permissions
                                            WHERE user_id = ?d
                                            AND course_id = ?d",
            $this->user_id, $this->course_id);

        if ($q and $q->course_clone) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @brief check if user has permission to backup course
     * @return bool
     */
    public function has_course_backup_permission(): bool
    {
        if ($this->course_admin) {
            return true;
        }
        $q = Database::get()->querySingle("SELECT course_backup FROM user_permissions
                                            WHERE user_id = ?d
                                            AND course_id = ?d",
            $this->user_id, $this->course_id);

        if ($q and $q->course_backup) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @brief get all user course permissions
     * @return array
     */
    public function get_course_permissions(): array {

        $arr_permissions = [];
        $q = Database::get()->querySingle("SELECT admin_modules, admin_users, course_backup, course_clone 
                                            FROM user_permissions
                                            WHERE user_id = ?d AND course_id = ?d",
            $this->user_id, $this->course_id);
        if ($q) {
            foreach ($q as $key => $value) {
                if ($value == 1) {
                    $arr_permissions[$key] = $value;
                }
            }
        }
        return $arr_permissions;
    }

    /**
     * @brief get the permission legend
     * @param $permission
     * @return string
     */
    public function get_permissions_legend($permission): string {

        global $langCourseAdminTools, $langAdminUsers, $langArchiveCourse, $langCloneCourse;

        switch ($permission) {
                case 'admin_modules':
                    $msg = $langCourseAdminTools;
                    break;
                case 'admin_users':
                    $msg = $langAdminUsers;
                    break;
                case'course_backup':
                    $msg = $langArchiveCourse;
                    break;
                case 'course_clone':
                    $msg = $langCloneCourse;
                    break;
                default:
                    $msg = '';
                    break;
        }
        return $msg;
    }
}
