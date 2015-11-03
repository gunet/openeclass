<?php namespace Widgets\Openeclass;
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
class TextWidget extends Widget implements WidgetInterface {
   
    public function __construct() {  
        parent::__construct();
        
        /* Supported languages
         * [el] => Ελληνικά, [en] => English, [es] => Español, [cs] => Česky, [sq] => Shqip,
         * [bg] => Български, [ca] => Català, [da] => Dansk, [nl] => Nederlands, [fi] => Suomi,
         * [fr] => Français [de] => Deutsch [is] => Íslenska [it] => Italiano [jp] => 日本語 [pl] => Polski [ru] => Русский [tr] => Türkçe [sv] => Svenska
         * 
         * Fallback language is English
         */        
        $this->name = array(
            'en' => 'Text Widget',
            'el' => 'Widget Κειμένου'     
        );
        $this->description = array(
            'en' => 'This is a widget that simply displays some text or html',
            'el' => 'Ένα widget με το οποίο μπορείτε να εμφανίσετε απλό κείμενο ή html'     
        );               
    }
    
    public static function install()
    {
        /* START CUSTOM CODE */

        /* END CUSTOM CODE */
        return self::register_widget();
    }
    
    public static function uninstall()
    {
        /* START CUSTOM CODE */

        /* END CUSTOM CODE */        
        return self::unregister_widget();
    }
    public function run($widget_widget_area_id)
    {
        
        $widget_widget_area = new WidgetWidgetArea($widget_widget_area_id);
        $view_data = $widget_widget_area->getOptions();
        /* START CUSTOM CODE */
        global $language;
        $view_data['language'] = $language;
        /* END CUSTOM CODE */
        return view("widgets.text.run", $view_data);
    }
    public function getOptionsForm($widget_widget_area_id)
    {
        $widget_widget_area = new WidgetWidgetArea($widget_widget_area_id);
        $view_data = $widget_widget_area->getOptions();
        //START CUSTOM CODE
        global $native_language_names_init;

        $view_data['widget_widget_area_id'] = $widget_widget_area_id;
        $view_data['active_ui_languages'] = explode(' ', get_config('active_ui_languages'));
        $view_data['native_language_names_init'] = $native_language_names_init;
        //END CUSTOM CODE
        return view("widgets.text.options", $view_data);
    }

}
