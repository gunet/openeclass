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
namespace Widgets;

class WidgetArea {
    public $id;
    public $is_user_widget;
    public $is_course_admin_widget;
    public $user_id;
    public $widgets = array();

    public function __construct($id) {
        $this->id = $id;
    }
    public function getWidgets()
    {
        \Database::get()->queryFunc("SELECT a.id AS widget_widget_area_id, b.id AS widget_id, b.class AS class "
                . "FROM widget_widget_area a, widget b "
                . "WHERE a.widget_area_id = ?d AND a.user_id IS NULL AND a.course_id IS NULL AND b.id = a.widget_id ORDER BY a.position", function($widget) {
            $widget_obj = new $widget->class;
            $widget_obj->is_user_widget = false;
            $widget_obj->is_course_admin_widget = false;
            $this->widgets[$widget->widget_widget_area_id] = $widget_obj;
        }, $this->id);
        return $this->widgets;
    }
    public function getUserAndAdminWidgets($uid)
    {
        \Database::get()->queryFunc("SELECT a.id AS widget_widget_area_id, a.user_id AS user_id, b.id AS widget_id, b.class AS class "
                . "FROM widget_widget_area a, widget b "
                . "WHERE a.widget_area_id = ?d AND (a.user_id = ?d OR a.user_id IS NULL) AND b.id = a.widget_id ORDER BY a.position", function($widget) use ($uid){
            $widget_obj = new $widget->class;
            $widget_obj->is_user_widget = $widget->user_id == $uid;
            $this->widgets[$widget->widget_widget_area_id] = $widget_obj;
        }, $this->id, $uid);

        return $this->widgets;
    }
    public function getCourseAndAdminWidgets($course_id)
    {
        \Database::get()->queryFunc("SELECT a.id AS widget_widget_area_id, a.course_id AS course_id, b.id AS widget_id, b.class AS class "
                . "FROM widget_widget_area a, widget b "
                . "WHERE a.widget_area_id = ?d AND (a.course_id = ?d OR a.course_id IS NULL) AND b.id = a.widget_id ORDER BY a.position", function($widget) use ($course_id){
            $widget_obj = new $widget->class;
            $widget_obj->is_course_admin_widget = $widget->course_id == $course_id;
            $this->widgets[$widget->widget_widget_area_id] = $widget_obj;
        }, $this->id, $course_id);

        return $this->widgets;
    }
}
