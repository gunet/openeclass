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

        $q = Database::get()->querySingle("SELECT * FROM user_permissions JOIN permissions 
                                            ON user_permissions.permission_id = permissions.id 
                                            WHERE permissions.permission = 'admin_course_modules' 
                                          AND user_id = ?d AND course_id = ?d",
                                $this->user_id, $this->course_id);

        if ($q) {
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

        $q = Database::get()->querySingle("SELECT * FROM user_permissions JOIN permissions 
                                            ON user_permissions.permission_id = permissions.id 
                                            WHERE permissions.permission = 'admin_course_users' 
                                          AND user_id = ?d AND course_id = ?d",
                                $this->user_id, $this->course_id);

        if ($q) {
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

        $q = Database::get()->querySingle("SELECT * FROM user_permissions JOIN permissions 
                                            ON user_permissions.permission_id = permissions.id 
                                            WHERE permissions.permission = 'clone_course' 
                                          AND user_id = ?d AND course_id = ?d",
                                $this->user_id, $this->course_id);

        if ($q) {
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
        $q = Database::get()->querySingle("SELECT * FROM user_permissions JOIN permissions 
                                            ON user_permissions.permission_id = permissions.id 
                                            WHERE permissions.permission = 'backup_course' 
                                          AND user_id = ?d AND course_id = ?d",
                                $this->user_id, $this->course_id);

        if ($q) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @brief check if user has permission to upload document
     * @return bool
     */
    public function can_upload_document(): bool
    {
        if ($this->course_admin) {
            return true;
        }
        $q = Database::get()->querySingle("SELECT * FROM user_permissions JOIN permissions 
                                            ON user_permissions.permission_id = permissions.id 
                                            WHERE permissions.permission = 'can_upload_document' 
                                          AND user_id = ?d AND course_id = ?d",
            $this->user_id, $this->course_id);

        if ($q) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @brief check if user has permission to upload multimedia
     * @return bool
     */
    public function can_upload_multimedia(): bool
    {
        if ($this->course_admin) {
            return true;
        }
        $q = Database::get()->querySingle("SELECT * FROM user_permissions JOIN permissions 
                                            ON user_permissions.permission_id = permissions.id 
                                            WHERE permissions.permission = 'can_upload_multimedia' 
                                          AND user_id = ?d AND course_id = ?d",
            $this->user_id, $this->course_id);

        if ($q) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @brief get all user course permissions
     * @return array
     */
    public function get_course_permissions(): array
    {
        $arr_permissions = [];
        $q = Database::get()->queryArray("SELECT permission 
                                            FROM user_permissions JOIN permissions 
                                            ON user_permissions.permission_id = permissions.id 
                                        WHERE user_id = ?d AND course_id = ?d",
            $this->user_id, $this->course_id);
        if ($q) {
            foreach ($q as $data) {
                $arr_permissions[] = $data->permission;
            }
        }
        return $arr_permissions;
    }

    /**
     * @brif update user course permissions
     * @param $permissions
     * @return void
     */
    public function update_course_permissions($permissions) {

        Database::get()->query("DELETE FROM user_permissions WHERE user_id = ?d AND course_id = ?d", $this->user_id, $this->course_id);
        foreach ($permissions as $permission) {
            Database::get()->query("INSERT INTO user_permissions (user_id, course_id, permission_id) VALUES (?d, ?d, ?d)", $this->user_id, $this->course_id, $permission);
        }
    }


    /**
     * @brief get the permission legend
     * @param $permission
     * @return string
     */
    public function get_permissions_legend($permission): string {

        global $langCourseAdminTools, $langAdminUsers, $langArchiveCourse, $langCloneCourse;

        switch ($permission) {
                case 'admin_course_modules':
                    $msg = $langCourseAdminTools;
                    break;
                case 'admin_course_users':
                    $msg = $langAdminUsers;
                    break;
                case 'backup_course':
                    $msg = $langArchiveCourse;
                    break;
                case 'clone_course':
                    $msg = $langCloneCourse;
                    break;
                default:
                    $msg = '';
                    break;
        }
        return $msg;
    }

    /**
     * @brief get the permission names
     * @return string[]
     */
    public function get_permissions_names(): array
    {
        return [
            1 => 'admin_course_modules',
            2 => 'admin_course_users',
            3 => 'backup_course',
            4 => 'clone_course',
            5 => 'can_upload_document',
            6 => 'can_upload_multimedia'
        ];
    }


}
