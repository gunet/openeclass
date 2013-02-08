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

require_once 'include/lib/modalboxhelper.class.php';

class MultimediaHelper {

    /**
     * Construct a proper a href html tag because each modal box requires a
     * specific calling method.
     *
     * @param  string $mediaDL   - force download url
     * @param  string $mediaPath - http full file path
     * @param  string $mediaPlay - media playback url
     * @param  string $title
     * @param  string $filename
     * @param  string $linkExtra
     * @return string
     */
    public static function chooseMediaAhref($mediaDL, $mediaPath, $mediaPlay, $title, $filename) {
        $ahref = "<a href='$mediaDL' class='fileURL' target='_blank' title='$title'>". $title ."</a>";

        if (self::isSupportedFile($filename)) {
            if (file_exists( ModalBoxHelper::getShadowboxDir() )) {
                $ahref = "<a href='$mediaPath' class='shadowbox fileURL' rel='shadowbox;width=". 
                        ModalBoxHelper::getShadowboxWidth()
                        .";height=". 
                        ModalBoxHelper::getShadowboxHeight() . 
                        ModalBoxHelper::getShadowboxPlayer($filename) 
                        ."' title='$title'>".$title."</a>";
                if (self::isSupportedImage($filename))
                    $ahref = "<a href='$mediaPath' class='shadowbox fileURL' rel='shadowbox' title='$title'>".$title."</a>";

            } else if (file_exists( ModalBoxHelper::getFancybox2Dir() )) {
                $ahref = "<a href='$mediaPlay' class='fancybox iframe fileURL' title='$title'>".$title."</a>";
                if (self::isSupportedImage($filename))
                    $ahref = "<a href='$mediaPath' class='fancybox fileURL' title='$title'>".$title."</a>";
            } else if (file_exists( ModalBoxHelper::getColorboxDir() )) {
                $ahref = "<a href='$mediaPlay' class='colorboxframe fileURL' title='$title'>".$title."</a>";
                if (self::isSupportedImage($filename))
                    $ahref = "<a href='$mediaPath' class='colorbox fileURL' title='$title'>".$title."</a>";
            }
        }

        return $ahref;
    }

    /**
     * Construct a proper a href html tag for medialinks
     *
     * @global string $userServer
     * @global string $course_code
     * @param  string $mediaURL - should be already urlencoded if possible
     * @param  string $title
     * @return string
     */
    public static function chooseMedialinkAhref($mediaURL, $title) {
        global $urlServer, $course_code;
        $ahref = "<a href='$mediaURL' class='fileURL' target='_blank' title='$title'>". $title ."</a>";

        if (self::isEmbeddableMedialink($mediaURL)) {
            $linkPlay = $urlServer ."modules/video/index.php?course=$course_code&amp;action=playlink&amp;id=". self::makeEmbeddableMedialink($mediaURL);

            if (file_exists( ModalBoxHelper::getShadowboxDir() ))
                $ahref = "<a href='". self::makeEmbeddableMedialink($mediaURL) ."' class='shadowbox fileURL' rel='shadowbox;width=". 
                    ModalBoxHelper::getShadowboxWidth() 
                    .";height=". 
                    ModalBoxHelper::getShadowboxHeight() 
                    ."' title='$title'>$title</a>";
            else if (file_exists(ModalBoxHelper::getFancybox2Dir() ))
                $ahref = "<a href='".$linkPlay."' class='fancybox iframe fileURL' title='$title'>$title</a>";
            else if (file_exists( ModalBoxHelper::getColorboxDir() ))
                $ahref = "<a href='".$linkPlay."' class='colorboxframe fileURL' title='$title'>$title</a>";
        }

        return $ahref;
    }


    /**
     * Construct a proper object html tag for each type of media
     *
     * @global string $urlAppend
     * @param  string $mediaPath
     * @param  string $mediaURL
     * @param  string $bgcolor
     * @param  string $color
     * @return string
     */
    public static function mediaHtmlObject($mediaPath, $mediaURL, $bgcolor = '#000000', $color = '#ffffff') {
        global $urlAppend;

        $extension = get_file_extension($mediaPath);

        $ret = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
                <html><head>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';

        $startdiv = '</head><body style="background-color: '.$bgcolor.'; color: '.$color.'; font-weight: bold"><div align="center">';
        $enddiv = '</div></body>';

        switch($extension) {
            case "asf":
            case "avi":
            case "wm":
            case "wmv":
            case "wma":
                $ret .= $startdiv;
                if (self::isUsingIE())
                    $ret .= '<object width="'. self::getObjectWidth() .'" height="'. self::getObjectHeight() .'"
                                classid="clsid:6BF52A52-394A-11d3-B153-00C04F79FAA6">
                                <param name="url" value="'.$mediaPath.'">
                                <param name="autostart" value="1">
                                <param name="uimode" value="full">
                                <param name="wmode" value="transparent">
                            </object>';
                else
                    $ret .= '<object width="'. self::getObjectWidth() .'" height="'. self::getObjectHeight() .'"
                                type="video/x-ms-wmv"
                                data="'.$mediaPath.'">
                                <param name="autostart" value="1">
                                <param name="showcontrols" value="1">
                                <param name="wmode" value="transparent">
                            </object>';
                $ret .= $enddiv;
                break;
            case "dv":
            case "mov":
            case "moov":
            case "movie":
            case "mp4":
            case "mpg":
            case "mpeg":
            case "3gp":
            case "3g2":
            case "m2v":
            case "aac":
            case "m4a":
                $ret .= $startdiv;
                if (self::isUsingIE())
                    $ret .= '<object width="'. self::getObjectWidth() .'" height="'. self::getObjectHeight() .'" kioskmode="true"
                                classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B"
                                codebase="http://www.apple.com/qtactivex/qtplugin.cab#version=6,0,2,0">
                                <param name="src" value="'.$mediaPath.'">
                                <param name="scale" value="aspect">
                                <param name="controller" value="true">
                                <param name="autoplay" value="true">
                                <param name="wmode" value="transparent">
                            </object>';
                else
                    $ret .= '<object width="'. self::getObjectWidth() .'" height="'. self::getObjectHeight() .'" kioskmode="true"
                                type="video/quicktime"
                                data="'.$mediaPath.'">
                                <param name="src" value="'.$mediaPath.'">
                                <param name="scale" value="aspect">
                                <param name="controller" value="true">
                                <param name="autoplay" value="true">
                                <param name="wmode" value="transparent">
                            </object>';
                $ret .= $enddiv;
                break;
            case "flv":
            case "f4v":
            case "m4v":
            case "mp3":
                $ret .= "<script type='text/javascript' src='{$urlAppend}js/flowplayer/flowplayer-3.2.6.min.js'></script>";
                $ret .= $startdiv;
                $ret .= '<div id="flowplayer" style="display: block; width: '. self::getObjectWidth() .'px; height: '. self::getObjectHeight() .'px;"></div>
                         <script type="text/javascript">
                             flowplayer("flowplayer", {
                                 src: "'.$urlAppend.'/js/flowplayer/flowplayer-3.2.7.swf",
                                 wmode: "transparent"
                                 }, {
                                 clip: {
                                     url: "'.$mediaPath.'",
                                     scaling: "fit"
                                 },
                                 canvas: {
                                     backgroundColor: "#000000",
                                     backgroundGradient: "none"
                                 }
                             });
                         </script>';
                $ret .= $enddiv;
                break;
            case "swf":
                $ret .= $startdiv;
                if (self::isUsingIE())
                    $ret .= '<object width="'. self::getObjectWidth() .'" height="'. self::getObjectHeight() .'"
                                 classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000">
                                 <param name="movie" value="'.$mediaPath.'"/>
                                 <param name="bgcolor" value="#000000">
                                 <param name="allowfullscreen" value="true">
                                 <param name="wmode" value="transparent">
                                 <a href="http://www.adobe.com/go/getflash">
                                    <img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player"/>
                                 </a>
                             </object>';
                else
                    $ret .= '<object width="'. self::getObjectWidth() .'" height="'. self::getObjectHeight() .'"
                                 data="'.$mediaPath.'"
                                 type="application/x-shockwave-flash">
                                 <param name="bgcolor" value="#000000">
                                 <param name="allowfullscreen" value="true">
                                 <param name="wmode" value="transparent">
                             </object>';
                $ret .= $enddiv;
                break;
            case "webm":
            case "ogv":
            case "ogg":
                $ret .= $startdiv;
                if (self::isUsingIE())
                    $ret .= '<a href="'.$mediaURL.'">Download media</a>';
                else
                    $ret .= '<video controls="" autoplay="" width="'. self::getObjectWidth() .'" height="'. self::getObjectHeight() .'"
                                 style="margin: auto; position: absolute; top: 0; right: 0; bottom: 0; left: 0;"
                                 name="media"
                                 src="'.$mediaPath.'">
                             </video>';
                $ret .= $enddiv;
                break;
            default:
                $ret .= $startdiv;
                $ret .= '<a href="'.$mediaURL.'">Download media</a>';
                $ret .= $enddiv;
                break;
        }

        $ret .= '</html>';

        return $ret;
    }

    /**
     * Construct a proper iframe html tag for each type of medialink
     *
     * @param  string $mediaURL - should be already urldecoded if possible
     * @param  string $bgcolor
     * @param  string $color
     * @return string
     */
    public static function medialinkIframeObject($mediaURL, $bgcolor = '#000000', $color = '#ffffff') {
        $ret = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
                <html><head>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                </head>
                <body style="background-color: '.$bgcolor.'; color: '.$color.'; font-weight: bold">
                <div align="center">';

        $needEmbed = array_merge(self::getGooglePatterns(), self::getMetacafePatterns(), self::getMyspacePatterns());

        $gotEmbed = false;
        foreach ($needEmbed as $pattern) {
            if (preg_match($pattern, $mediaURL)) {
                $ret .= '<object width="'. self::getObjectWidth() .'" height="'. self::getObjectHeight() .'">
                             <param name="allowFullScreen" value="true"/>
                             <param name="wmode" value="transparent"/>
                             <param name="movie" value="'.$mediaURL.'"/>
                             <embed flashVars="playerVars=autoPlay=yes"
                                 src="'.$mediaURL.'"
                                 width="'. self::getObjectWidth() .'" height="'. self::getObjectHeight() .'"
                                 allowFullScreen="true"
                                 allowScriptAccess="always"
                                 type="application/x-shockwave-flash"
                                 wmode="transparent">
                             </embed>
                         </object>';
                $gotEmbed = true;
            }
        }

        if (!$gotEmbed)
            $ret .='<iframe width="'. self::getObjectWidth() .'" height="'. self::getObjectHeight() .'"
                        src="'.$mediaURL.'" frameborder="0" allowfullscreen></iframe>';

        $ret .='</div></body></html>';

        return $ret;
    }

    /**
     * Whether the client uses Internet Explorer or not
     *
     * @return boolean
     */
    public static function isUsingIE() {
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        return (preg_match('/MSIE/i', $u_agent)) ? true : false;
    }

    /**
     * Whether the image is supported or not
     *
     * @param  string  $filename
     * @return boolean
     */
    public static function isSupportedImage($filename) {
        return in_array(get_file_extension($filename), self::getSupportedImages());
    }

    /**
     * Whether the media (video or audio) is supported or not
     *
     * @param  string  $filename
     * @return boolean
     */
    public static function isSupportedMedia($filename) {
        return in_array(get_file_extension($filename), self::getSupportedMedia());
    }
    
    /**
     * Whether the file (video or audio or image) is supported or not
     * 
     * @param  string  $filename
     * @return boolean
     */
    public static function isSupportedFile($filename) {
        return (self::isSupportedMedia($filename) || self::isSupportedImage($filename));
    }

    /**
     * Whether the medialink can be embedded in a modal box
     *
     * @param  string $medialink
     * @return boolean
     */
    public static function isEmbeddableMedialink($medialink) {
        $supported = array_merge(self::getYoutubePatterns(), self::getVimeoPatterns(),
                                 self::getGooglePatterns(), self::getMetacafePatterns(),
                                 self::getMyspacePatterns(), self::getDailymotionPatterns());
        $ret = false;

        foreach ($supported as $pattern) {
            if (preg_match($pattern, $medialink))
                $ret = true;
        }

        return $ret;
    }

    /**
     * Convert known media link types to embeddable links
     *
     * @param  string $medialink
     * @return string
     */
    public static function makeEmbeddableMedialink($medialink) {
        $matches = null;
        
        foreach (self::getYoutubePatterns() as $pattern) {
            if (preg_match($pattern, $medialink, $matches)) {
                $sanitized = strip_tags($matches[1]);
                $medialink = 'http://www.youtube.com/embed/'. $sanitized .'?hl=en&fs=1&rel=0&autoplay=1&wmode=transparent';
            }
        }

        foreach (self::getVimeoPatterns() as $pattern) {
            if (preg_match($pattern, $medialink, $matches)) {
                $sanitized = strip_tags($matches[1]);
                $medialink = 'http://player.vimeo.com/video/'. $sanitized .'?color=00ADEF&fullscreen=1&autoplay=1';
            }
        }

        foreach (self::getGooglePatterns() as $pattern) {
            if (preg_match($pattern, $medialink, $matches)) {
                $sanitized = strip_tags($matches[1]);
                $medialink = 'http://video.google.com/googleplayer.swf?docid='. $sanitized .'&hl=en&fs=true&autoplay=true';
            }
        }

        foreach (self::getMetacafePatterns() as $pattern) {
            if (preg_match($pattern, $medialink, $matches)) {
                $sanitized = strip_tags($matches[1]) ."/". urlencode(strip_tags($matches[2]));
                $medialink = 'http://www.metacafe.com/fplayer/'. $sanitized .'.swf';
            }
        }

        foreach (self::getMyspacePatterns() as $pattern) {
            if (preg_match($pattern, $medialink, $matches)) {
                $sanitized = strip_tags($matches[1]);
                $medialink = 'http://mediaservices.myspace.com/services/media/embed.aspx/m='. $sanitized .',t=1,mt=video,ap=1';
            }
        }

        foreach (self::getDailymotionPatterns() as $pattern) {
            if (preg_match($pattern, $medialink, $matches)) {
                $sanitized = strip_tags($matches[1]);
                $medialink = 'http://www.dailymotion.com/embed/video/'. $sanitized .'?autoPlay=1';
            }
        }

        return urlencode($medialink);
    }


    //--- Static properties ---//

    public static function getObjectWidth() {
        return ModalBoxHelper::getModalWidth() - 20;
    }

    public static function getObjectHeight() {
        return ModalBoxHelper::getModalHeight() - 20;
    }

    public static function getSupportedMedia() {
        return array("asf", "avi", "wm", "wmv", "wma",
                     "dv", "mov", "moov", "movie", "mp4", "mpg", "mpeg",
                     "3gp", "3g2", "m2v", "aac", "m4a",
                     "flv", "f4v", "m4v", "mp3",
                     "swf", "webm", "ogv", "ogg");
    }

    public static function getSupportedImages() {
        return array("jpg", "jpeg", "png", "gif", "bmp");
    }

    public static function getYoutubePatterns() {
        return array('/youtube\.com\/v\/([^&^\?]+)/i',
                     '/youtube\.com\/watch\?v=([^&]+)/i',
                     '/youtube\.com\/embed\/([^&^\?]+)/i',
                     '/youtu\.be\/([^&^\?]+)/i');
    }

    public static function getVimeoPatterns() {
        return array('/http:\/\/vimeo\.com\/([^&^\?]+)/i',
                     '/player\.vimeo\.com\/video\/([^&^\?]+)/i');
    }

    public static function getGooglePatterns() {
        return array('/video\.google\.com\/googleplayer\.swf\?docid=([^&]+)/i',
                     '/video\.google\.com\/videoplay\?docid=([^&]+)/i');
    }

    public static function getMetacafePatterns() {
        return array('/metacafe\.com\/watch\/([^\/]+)\/([^\/]+)/i',
                     '/metacafe\.com\/fplayer\/([^\/]+)\/([^\/]+)\.swf/i');
    }

    public static function getMyspacePatterns() {
        return array('/myspace\.com.*\/video.*\/([0-9]+)/i',
                     '/mediaservices\.myspace\.com\/services\/media\/embed\.aspx\/m=([0-9]+)/i',
                     '/lads\.myspace\.com\/videos\/MSVideoPlayer\.swf\?m=([0-9]+)/i');
    }

    public static function getDailymotionPatterns() {
        return array('/dailymotion\.com.*\/video\/(([^&^\?^_]+))/i');
    }

}