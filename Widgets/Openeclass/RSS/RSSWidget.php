<?php
// Widget's namespace should always follow folder structure (according to PSR-2 autoloading)
namespace Widgets\Openeclass\RSS; 
// These declarations are mandatory 
use Widgets\Widget;
use Widgets\WidgetWidgetArea;
use Widgets\WidgetInterface;

// A widget's class should always extend class "Widget" and implement WidgetInterface
class RSSWidget extends Widget implements WidgetInterface {

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
            'en' => 'RSS Widget ',
            'el' => 'Ροή RSS'     
        );
        $this->description = array(
            'en' => 'View an RSS feed',
            'el' => 'Προβολή ροής RSS'     
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

        $url = isset($this->view_data['feed_url']) ? $this->view_data['feed_url'] : "https://www.auth.gr/rss.xml";
        $max = isset($this->view_data['feed_items']) && $this->view_data['feed_items'] ? $this->view_data['feed_items'] : 3;
        //$url = "http://www.developphp.com/feed_all_vids.php";
        $xml = simplexml_load_file($url);
        $arr = array();
        for($i = 0; $i < $max; $i++){
            array_push($arr, array(
                                            'title' => (string)$xml->channel->item[$i]->title,
                                            'link' => (string)$xml->channel->item[$i]->link, 
                                            'description' => (string)$xml->channel->item[$i]->description, 
                                            'pubDate' => (string)$xml->channel->item[$i]->pubDate)
                    );
        }
       $this->view_data['feed_items'] = (array)$arr;
       
       //var_dump($this->view_data['feed_items']);die();

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

