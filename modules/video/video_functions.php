<?php
/* ========================================================================
 * Open eClass 2.5
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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

/**
 * Load necessary javascript initialization.
 * Decides which modal box to use based on whether it's installed under js/ 
 * directory.
 * 
 * The priority for choosing is:
 * 1. Shadowbox
 * 2. Fancybox2
 */
function load_modal_box()
{
    $shadowbox_init = '<script type="text/javascript">
                       Shadowbox.init({
                           overlayOpacity: 0.8,
                           modal: true
                       });
                       </script>';

    $fancybox2_init = '<script type="text/javascript">
                       $(document).ready(function() {
                           $(".fancybox").fancybox({
                                   width     : '.get_modal_width().',
                                   height    : '.get_modal_height().',
                                   padding   : 0,
                                   margin    : 0,
                                   scrolling : "no"
                          });
                       });
                       </script>';
    
    $colorbox_init = '<script type="text/javascript">
                      $(document).ready(function() {
                          $(".colorbox").colorbox({
                                  innerWidth  : '.get_modal_width().',
                                  innerHeight : '.get_modal_height().',
                                  iframe      : "true",
                                  scrolling   : "false",
                                  opacity     : 0.8
                         });
                      });
                      </script>';
    
    if (file_exists(get_shadowbox_dir()))
        load_js('shadowbox', $shadowbox_init);
    else if (file_exists(get_fancybox2_dir())) {
        load_js('jquery');
        load_js('fancybox2', $fancybox2_init);
    } else if (file_exists(get_colorbox_dir())) {
        load_js('jquery');
        load_js('colorbox', $colorbox_init);
    }
}

/**
 * Construct a proper a href html tag because each modal box requires a 
 * specific calling method.
 * 
 * @param  string $videoURL
 * @param  string $videoPath
 * @param  string $videoPlay
 * @param  string $title
 * @param  string $filename
 * @return string 
 */
function choose_modal_ahref($videoURL, $videoPath, $videoPlay, $title, $filename)
{
    $ahref = "<a href='$videoURL'>". $title ."</a>";
    
    if (is_supported_movie($filename))
    {
        if (file_exists(get_shadowbox_dir()))
            $ahref = "<a href='$videoPath' rel='shadowbox;width=".get_shadowbox_width().";height=".get_shadowbox_height().get_shadowbox_player($filename)."' title='$title'>$title</a>";
        else if (file_exists(get_fancybox2_dir()))
            $ahref = "<a href='$videoPlay' class='fancybox fancybox.iframe' title='$title'>$title</a>";
        else if (file_exists(get_colorbox_dir()))
            $ahref = "<a href='$videoPlay' class='colorbox' title='$title'>$title</a>";
    }
    
    return $ahref;
}

/**
 * Construct a proper a href html tag for videolinks
 * 
 * @param  string $videoURL
 * @param  string $title
 * @return string 
 */
function choose_videolink_ahref($videoURL, $title)
{
    $ahref = "<a href='$videoURL' target='_blank'>". $title ."</a>";
    
    if (is_embeddable_videolink($videoURL))
    {
        if (file_exists(get_shadowbox_dir()))
            $ahref = "<a href='".make_embeddable_videolink($videoURL)."' rel='shadowbox;width=".get_shadowbox_width().";height=".get_shadowbox_height()."' title='$title'>$title</a>";
        else if (file_exists(get_fancybox2_dir()))
            $ahref = "<a href='".make_embeddable_videolink($videoURL)."' class='fancybox fancybox.iframe' title='$title'>$title</a>";
        else if (file_exists(get_colorbox_dir()))
            $ahref = "<a href='".make_embeddable_videolink($videoURL)."' class='colorbox' title='$title'>$title</a>";
    }
    
    return $ahref;
}

/**
 * For some file types shadowbox fails to autodetect the necessary player to 
 * use, that's why we are helping it a bit.
 * 
 * @param  string $filename
 * @return string 
 */
function get_shadowbox_player($filename)
{
    $extension = get_file_extension($filename);
    $ret = "";
    
    switch($extension)
    {
        case "flv":
        case "m4v":
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
 * Construct a proper object html tag for each type of video media we want to 
 * present.
 * 
 * @global string $urlAppend
 * @param  string $videoPath
 * @return string 
 */
function video_html_object($videoPath)
{
    global $urlAppend;
    
    $extension = get_file_extension($videoPath);
    $ret = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
            <html><head>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
    
    $blackdiv = '</head><body style="background-color: #000000; font-weight: bold"><div align="center">';
    $enddiv = '</div></body>';
    
    switch($extension)
    {
        case "asf":
        case "avi":
        case "wm":
        case "wmv":
            $ret .= $blackdiv;
            if (using_ie())
                $ret .= '<object width="'.get_object_width().'" height="'.get_object_height().'"
                            classid="clsid:6BF52A52-394A-11d3-B153-00C04F79FAA6">
                            <param name="url" value="'.$videoPath.'">
                            <param name="autostart" value="1">
                            <param name="uimode" value="full">
                        </object>';
            else
                $ret .= '<object width="'.get_object_width().'" height="'.get_object_height().'"
                            type="video/x-ms-wmv"
                            data="'.$videoPath.'">
                            <param name="autostart" value="1">
                            <param name="showcontrols" value="1">
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
            $ret .= $blackdiv;
            if (using_ie())
                $ret .= '<object width="'.get_object_width().'" height="'.get_object_height().'" kioskmode="true"
                            classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B"
                            codebase="http://www.apple.com/qtactivex/qtplugin.cab#version=6,0,2,0">
                            <param name="src" value="'.$videoPath.'">
                            <param name="scale" value="aspect">
                            <param name="controller" value="true">
                            <param name="autoplay" value="true">
                        </object>';
            else
                $ret .= '<object width="'.get_object_width().'" height="'.get_object_height().'" kioskmode="true"
                            type="video/quicktime"
                            data="'.$videoPath.'">
                            <param name="src" value="'.$videoPath.'">
                            <param name="scale" value="aspect">
                            <param name="controller" value="true">
                            <param name="autoplay" value="true">
                        </object>';
            $ret .= $enddiv;
            break;
        case "flv":
        case "f4v":
        case "m4v":
        case "mp3":
            $ret .= "<script type='text/javascript' src='$urlAppend/js/flowplayer/flowplayer-3.2.6.min.js'></script>";
            $ret .= $blackdiv;
            $ret .= '<div id="flowplayer" style="display:block;width:'.get_object_width().'px;height:'.get_object_height().'px;"></div>
                    <script type="text/javascript">
                        flowplayer("flowplayer", "'.$urlAppend.'/js/flowplayer/flowplayer-3.2.7.swf", {
                            clip: {
                                url: "'.$videoPath.'",
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
            $ret .= $blackdiv;
            $ret .= '<object width="'.get_object_width().'" height="'.get_object_height().'"
                         data="'.$videoPath.'" 
                         type="application/x-shockwave-flash">
                         <param name="bgcolor" value="#000000">
                         <param name="allowfullscreen" value="true">
                     </object>';
            $ret .= $enddiv;
            break;
        case "webm":
        case "ogv":
        case "ogg":
            $ret .= $blackdiv;
            $ret .= '<video controls="" autoplay="" width="'.get_object_width().'" height="'.get_object_height().'"
                         style="margin: auto; position: absolute; top: 0; right: 0; bottom: 0; left: 0;" 
                         name="media" 
                         src="'.$videoPath.'">
                     </video>';
            $ret .= $enddiv;
            break;
        default:
            $ret .= $blackdiv;
            $ret .= '<p style="color: #ffffff">Unknown video type, please download it and play it manually.</p>';
            $ret .= $enddiv;
            break;
    }
    
    $ret .= '</html>';
    
    return $ret;
}

/**
 * Whether the client uses Internet Explorer or not
 * 
 * @return boolean 
 */
function using_ie()
{
    $u_agent = $_SERVER['HTTP_USER_AGENT'];
    $ub = false;
    
    if(preg_match('/MSIE/i', $u_agent))
    {
        $ub = true;
    }
   
    return $ub;
}

/**
 * Whether the movie is supported or not
 * 
 * @param  string  $filename
 * @return boolean 
 */
function is_supported_movie($filename)
{
    $supported = array("asf", "avi", "wm", "wmv",
                       "dv", "mov", "moov", "movie", "mp4", "mpg", "mpeg", 
                       "3gp", "3g2", "m2v", "aac", "m4a",
                       "flv", "f4v", "m4v", "mp3",
                       "swf", "webm", "ogv", "ogg");
    
    return in_array(get_file_extension($filename), $supported);
}


/**
 * Whether the videolink can be embedded in a modal box
 * 
 * @param  string $videolink
 * @return boolean 
 */
function is_embeddable_videolink($videolink)
{
    $supported = array_merge(get_youtube_patterns(), get_vimeo_patterns(), 
                             get_google_patterns(), get_metacafe_patterns(),
                             get_myspace_patterns());
    $ret = false;
    
    foreach ($supported as $pattern)
    {
        if (preg_match($pattern, $videolink))
        {
            $ret = true;
        }
    }
    
    return $ret;
}

/**
 * Convert known media link types to embeddable links
 * 
 * @param  string $videolink
 * @return string 
 */
function make_embeddable_videolink($videolink)
{
    foreach (get_youtube_patterns() as $pattern)
    {
        if (preg_match($pattern, $videolink, $matches))
        {
            $sanitized = urlencode(strip_tags($matches[1]));
            $videolink = 'http://www.youtube.com/v/'. $sanitized .'?hl=en&amp;fs=1&amp;rel=0&amp;autoplay=1';
        }
    }
    
    foreach (get_vimeo_patterns() as $pattern)
    {
        if (preg_match($pattern, $videolink, $matches))
        {
            $sanitized = urlencode(strip_tags($matches[1]));
            $videolink = 'http://vimeo.com/moogaloop.swf?clip_id='. $sanitized .'&amp;color=00ADEF&amp;fullscreen=1&amp;autoplay=1';
        }
    }
    
    foreach (get_google_patterns() as $pattern)
    {
        if (preg_match($pattern, $videolink, $matches))
        {
            $sanitized = urlencode(strip_tags($matches[1]));
            $videolink = 'http://video.google.com/googleplayer.swf?docid='. $sanitized .'&amp;hl=en&amp;fs=true&amp;autoplay=true';
        }
    }
    
    foreach (get_metacafe_patterns() as $pattern)
    {
        if (preg_match($pattern, $videolink, $matches))
        {
            $sanitized = urlencode(strip_tags($matches[1]));
            $videolink = 'http://www.metacafe.com/fplayer/'. $sanitized .'.swf?autoPlay=yes';
        }
    }
    
    foreach (get_myspace_patterns() as $pattern)
    {
        if (preg_match($pattern, $videolink, $matches))
        {
            $sanitized = urlencode(strip_tags($matches[1]));
            $videolink = 'http://lads.myspace.com/videos/MSVideoPlayer.swf?m='. $sanitized .'&amp;mt=video&amp;ap=1';
        }
    }
    
    return $videolink;
}


//--- Sizes, dimensions and "statics" ---//


function get_shadowbox_dir()
{
    global $webDir;
    return $webDir . "/js/shadowbox";
}

function get_fancybox2_dir()
{
    global $webDir;
    return $webDir . "/js/fancybox2";
}

function get_colorbox_dir()
{
    global $webDir;
    return $webDir . "/js/colorbox";
}

function get_shadowbox_width()
{
    return 700;
}

function get_shadowbox_height()
{
    return 350;
}

function get_modal_width()
{
    return 680;
}

function get_modal_height()
{
    return 380;
}

function get_object_width()
{
    return get_modal_width() - 20;
}

function get_object_height()
{
    return get_modal_height() - 20;
}

function get_youtube_patterns()
{
    $youtube = array('/youtube\.com\/v\/([^&^\?]+)/i',
                     '/youtube\.com\/watch\?v=([^&]+)/i',
                     '/youtube\.com\/embed\/([^&^\?]+)/i',
                     '/youtu\.be\/([^&^\?]+)/i');
    
    return $youtube;
}

function get_vimeo_patterns()
{
    $vimeo = array('/http:\/\/vimeo\.com\/([^&^\?]+)/i',
                   '/player\.vimeo\.com\/video\/([^&^\?]+)/i');
    
    return $vimeo;
}

function get_google_patterns()
{
    $google = array('/video\.google\.com\/googleplayer\.swf\?docid=([^&]+)/i',
                    '/video\.google\.com\/videoplay\?docid=([^&]+)/i');
    
    return $google;
}

function get_metacafe_patterns()
{
    $metacafe = array('/metacafe\.com\/watch\/([^\/]+\/[^\/]+)/i',
                      '/metacafe\.com\/fplayer\/([^\/]+\/[^\/]+)\.swf/i');
    
    return $metacafe;
}

function get_myspace_patterns()
{
    $myspace = array('/myspace\.com.*\/video.*\/([0-9]+)/i',
                     '/mediaservices\.myspace\.com\/services\/media\/embed\.aspx\/m=([0-9]+)/i',
                     '/lads\.myspace\.com\/videos\/MSVideoPlayer\.swf\?m=([0-9]+)/i');
    
    return $myspace;
}
