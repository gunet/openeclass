<?php
// Widget's namespace should always follow folder structure (according to PSR-2 autoloading)
namespace Widgets\Openeclass\HelloWorld; 
// These declarations are mandatory 
use Widgets\Widget;
use Widgets\WidgetWidgetArea;
use Widgets\WidgetInterface;

// A widget's class should always extend class "Widget" and implement WidgetInterface
class HelloWorldWidget extends Widget implements WidgetInterface {

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
            'en' => 'Hello World',
            'el' => 'Γεια σου κόσμε'     
        );
        $this->description = array(
            'en' => 'This is a widget simply for widget creation reference',
            'el' => 'Ένα widget ως αναφορά για την δημιουργία άλλων widgets από τους προγραμματιστές'     
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
        $this->initialize_widget_data($widget_widget_area_id);
        /* START CUSTOM CODE */

        /* END CUSTOM CODE */
        return widget_view("run", $this->view_data);

    }
    public function getOptionsForm($widget_widget_area_id)
    {
        $this->initialize_widget_data($widget_widget_area_id);
        //START CUSTOM CODE

        //END CUSTOM CODE
        return widget_view("options", $this->view_data);
    }

}

