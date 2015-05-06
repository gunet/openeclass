<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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
 * ======================================================================== */

class ModalBoxHelper {

    private static $modalWidth = 660;
    private static $modalHeight = 410;
    private static $shadowBoxWidth = 700;
    private static $shadowBoxHeight = 350;

    /**
     * Load necessary javascript initialization.
     * Decides which modal box to use based on whether it's installed under js/
     * directory.
     *
     * The priority for choosing is:
     * 1. Shadowbox
     * 2. Fancybox2
     * 3. Colorbox
     *
     * @global string  $langColorboxCurrent
     * @param  boolean $gallery
     */
    public static function loadModalBox($gallery = false) {
        global $langColorboxCurrent;

        $shadowbox_gallery = ($gallery) ? 'gallery: "gallery"' : '';
        $shadowbox_init = '<script type="text/javascript">
                           Shadowbox.init({
                               overlayOpacity : 0.8,
                               modal          : false,
                               continuous     : true
                           });

                           window.onload = function() {
                               Shadowbox.setup(".shadowbox", {
                               ' . $shadowbox_gallery . '
                               });
                           };
                           </script>';

        $fancybox2_init = '<script type="text/javascript">
                           $(document).ready(function() {
                               $(".fancybox").fancybox({
                                       width     : ' . self::$modalWidth . ',
                                       height    : ' . self::$modalHeight . ',
                                       padding   : 0,
                                       margin    : 0,
                                       scrolling : "no"
                              });
                           });
                           </script>';

        $colorbox_gallery = ($gallery) ? 'rel: "gallery",' : '';
        $colorbox_init = '<script type="text/javascript">
                          $(document).ready(function() {
                              $(".colorboxframe").colorbox({
                                      innerWidth  : ' . self::$modalWidth . ',
                                      innerHeight : ' . self::$modalHeight . ',
                                      maxWidth    : "100%",
                                      maxHeight   : "100%",
                                      iframe      : true,
                                      scrolling   : false,
                                      opacity     : 0.8,
                                      ' . $colorbox_gallery . '
                                      current     : "' . $langColorboxCurrent . '"
                             });
                             $(".colorbox").colorbox({
                                      minWidth    : 300,
                                      minHeight   : 200,
                                      maxWidth    : "100%",
                                      maxHeight   : "100%",
                                      scrolling   : false,
                                      opacity     : 0.8,
                                      photo       : true,
                                      ' . $colorbox_gallery . '
                                      current     : "' . $langColorboxCurrent . '"
                             });
                          });
                          </script>';

        if (file_exists(self::getShadowboxDir())) {
            load_js('shadowbox', $shadowbox_init);
        } else if (file_exists(self::getFancybox2Dir())) {
            load_js('fancybox2', $fancybox2_init);
        } else if (file_exists(self::getColorboxDir())) {
            load_js('colorbox', $colorbox_init);
        }
    }

    /**
     * For some file types shadowbox fails to autodetect the necessary player to
     * use, that's why we are helping it a bit.
     *
     * @param  string $filename
     * @return string
     */
    public static function getShadowboxPlayer($filename) {
        $extension = get_file_extension($filename);
        $ret = "";

        switch ($extension) {
            case "flv":
            case "m4v":
            case "mp3":
                $ret = ";player=flv";
                break;
            case "swf":
                $ret = ";player=swf";
                break;
            default:
                break;
        }

        return $ret;
    }

    /**
     * 
     * @return int
     */
    public static function getModalWidth() {
        return self::$modalWidth;
    }

    /**
     * 
     * @return int
     */
    public static function getModalHeight() {
        return self::$modalHeight;
    }

    /**
     * 
     * @global string $webDir
     * @return string
     */
    public static function getShadowboxDir() {
        global $webDir;
        return $webDir . "/js/shadowbox";
    }

    /**
     * 
     * @global string $webDir
     * @return string
     */
    public static function getFancybox2Dir() {
        global $webDir;
        return $webDir . "/js/fancybox2";
    }

    /**
     * 
     * @global string $webDir
     * @return string
     */
    public static function getColorboxDir() {
        global $webDir;
        return $webDir . "/js/colorbox";
    }

    /**
     * 
     * @return int
     */
    public static function getShadowboxWidth() {
        return self::$shadowBoxWidth;
    }

    /**
     * 
     * @return int
     */
    public static function getShadowboxHeight() {
        return self::$shadowBoxHeight;
    }

}
