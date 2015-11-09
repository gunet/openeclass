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

abstract class Widget {
    public $id;
    public $name;
    public $description;
    public $folder;
    public $view_data = [];
    
    public function __construct() {
        $widget = \Database::get()->querySingle("SELECT id FROM widget WHERE class = ?s", get_called_class());
        $this->id = $widget ? $widget->id : null;
        $this->folder = str_replace('\\', '/', substr(get_called_class(), 0, strrpos(get_called_class(), "\\")));
    }
    
    protected static function register_widget() {
        return \Database::get()->query("INSERT INTO widget (class) VALUES (?s)", get_called_class())->lastInsertID;
    }
    
    protected static function unregister_widget() {
        return  \Database::get()->query("DELETE FROM widget WHERE class = ?s", get_called_class());
    }
    public function getName()
    {
        global $language;
        $name = isset($this->name[$language]) ? $this->name[$language] : $this->name['en'];
        return $name;
    }  
    public function getDesc()
    {
        global $language;
        $description = isset($this->description[$language]) ? $this->description[$language] : $this->description['en'];
        return $description;
    }      
    protected function get_options($widget_widget_area_id) {
        $options = unserialize(\Database::get()->querySingle("SELECT options FROM widget_widget_area WHERE id = ?d", $widget_widget_area_id)->options);
        if ($options) {
            foreach ($options as $key => $value) {
                $data[$key] = $value;
            }
        }
        return $data;
    }
    protected function initialize_widget_data($widget_widget_area_id) {
        $widget_widget_area = new WidgetWidgetArea($widget_widget_area_id);
        $this->view_data = $widget_widget_area->getOptions();
        $this->view_data['widget_folder'] = $this->folder; 
        $this->view_data['widget_widget_area_id'] = $widget_widget_area_id;
    }
    protected static function widget_tbl_name($tbl_name = '') {
        $lc_namespace = strtolower(substr(get_called_class(), 0, strrpos(get_called_class(), "\\")));
        $namespace_parts = explode('\\', $lc_namespace);
        if (empty($tbl_name)) {
            $wdgt_table_name = "`wdgt_".$namespace_parts[1]."_".$namespace_parts[2]."`";
        } else {
            $wdgt_table_name = "`wdgt_".$namespace_parts[1]."_".$tbl_name."`";
        }
        return $wdgt_table_name;
    }
}
