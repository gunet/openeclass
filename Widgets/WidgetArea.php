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


class WidgetArea {
    public $id;
    public $widgets = array();
     
    public function __construct($id) {
        $this->id = $id;
        $counter = 0;
        \Database::get()->queryFunc("SELECT a.id AS widget_widget_area_id, b.id AS widget_id, b.class AS class "
                . "FROM widget_widget_area a, widget b "
                . "WHERE a.widget_area_id = ?d AND b.id = a.widget_id ORDER BY a.position", function($widget) use (&$counter) {
            $widget_obj = new $widget->class;
            $this->widgets[$widget->widget_widget_area_id] = $widget_obj;
        }, $this->id);
    }
    public function getWidgets()
    {
        return $this->widgets;
    }   
}
