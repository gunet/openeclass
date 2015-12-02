<?php namespace Widgets;

/* ========================================================================
 * Open eClass 
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
 * ======================================================================== 
 */


class WidgetWidgetArea {
    private $id;
    private $widget_id;
    private $widget_area_id;
    private $options = null;
    private $user_id = null;
     
    public function __construct($id) {
        $widget_widget_area = \Database::get()->querySingle("SELECT * FROM widget_widget_area WHERE id = ?d", $id);
        if ($widget_widget_area) {
            $this->id = $id;
            $this->widget_id = $widget_widget_area->widget_id;
            $this->widget_area_id = $widget_widget_area->widget_area_id;
            $this->options = unserialize($widget_widget_area->options);
            $this->user_id = $widget_widget_area->user_id;
        }
    }
    public function getOptions() {        
        $data = array();
        if ($this->options) {
            foreach ($this->options as $key => $value) {
                $data[$key] = $value;
            }
        }
        return $data;
    }
    public function getUserID() {        
        return $this->user_id;
    }
    public function isUserWidget($uid) {
        return isset($this->user_id) ? $this->user_id == $uid : false;
    }     
}
