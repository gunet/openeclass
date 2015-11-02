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
    public $id;
    public $widget_id;
    public $widget_area_id;
    public $options = null;
     
    public function __construct($id) {
        $widget_widget_area = \Database::get()->querySingle("SELECT * FROM widget_widget_area WHERE id = ?d", $id);
        if ($widget_widget_area) {
            $this->id = $id;
            $this->widget_id = $widget_widget_area->widget_id;
            $this->widget_area_id = $widget_widget_area->widget_area_id;
            $this->options = unserialize($widget_widget_area->options);            
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
}
