<?php namespace Widgets\Openeclass\Clock;
use Widgets\Widget;
use Widgets\WidgetWidgetArea;
use Widgets\WidgetInterface;
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

/**
 * Description of TextWidget
 *
 * @author nikos
 */
class ClockWidget extends Widget implements WidgetInterface {
  
    public function __construct() {  
        parent::__construct();
        
        $this->name = array(
            'en' => 'Clock',
            'el' => 'Ρολόι'     
        );
        $this->description = array(
            'en' => 'This is a widget that simply displays an analog / digital clock',
            'el' => 'Ένα widget με το οποίο μπορείτε να εμφανίσετε ένα αναλογικό ή ψηφιακό ρολόι'     
        );               
    }
    
    public static function install()
    {     
        return self::register_widget();
    }
    
    public static function uninstall()
    {  
        return self::unregister_widget();
    }
    public function run($widget_widget_area_id)
    {
        $this->initialize_widget_data($widget_widget_area_id);

        $clock_type = isset($this->view_data['clock_type']) && $this->view_data['clock_type'] ? "digital" : "analog";
        widget_css_link($clock_type.'_clock.css', $this->folder);
        widget_js_link('moment.min.js', $this->folder);
        widget_js_link($clock_type.'_clock.js', $this->folder);

        return widget_view("run", $this->view_data);

    }
    public function getOptionsForm($widget_widget_area_id)
    {
        $this->initialize_widget_data($widget_widget_area_id);

        return widget_view("options", $this->view_data);
    }

}
