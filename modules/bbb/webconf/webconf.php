<?php

echo '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- saved from url=(0014)about:internet -->
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en"> 
    <!-- 
    Smart developers always View Source. 
    
    This application was built using Adobe Flex, an open source framework
    for building rich Internet applications that get delivered via the
    Flash Player or to desktops via Adobe AIR. 
    
    Learn more about Flex at http://flex.org 
    // -->
    <head>
        <title></title>
        <meta name="google" value="notranslate" />         
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style type="text/css" media="screen"> 
            html, body  { height:100%; }
            body { margin:0; padding:0; overflow:auto; text-align:center; 
                   background-color: #ffffff; }   
            object:focus { outline:none; }
            #flashContent { display:none; }
        </style>
        
        <!-- Enable Browser History by replacing useBrowserHistory tokens with two hyphens -->
        <!-- BEGIN Browser History required section -->
        <link rel="stylesheet" type="text/css" href="history/history.css" />
        <script type="text/javascript" src="history/history.js"></script>
        <!-- END Browser History required section -->  
            
        <script type="text/javascript" src="swfobject.js"></script>
        <script type="text/javascript">
            // For version detection, set to min. required Flash Player version, or 0 (or 0.0.0), for no version detection. 
            var swfVersionStr = "11.1.0";
            // To use express install, set to playerProductInstall.swf, otherwise the empty string. 
            var xiSwfUrlStr = "playerProductInstall.swf";
            var flashvars = {};
			flashvars.videoconf_url = "rtmpe://webcast.gunet.gr/avchat/'.$_GET['meeting_id'].'"
			flashvars.chat_url = "rtmpe://webcast.gunet.gr/textchat/'.$_GET['meeting_id'].'"
			flashvars.jnlp_url = "http://localhost/openeclass/modules/bbb/webconf/rooms/'.$_GET['meeting_id'].'.jnlp"
			flashvars.base_screen_url = "rtmp://195.130.123.149/screenshare"
			flashvars.screen_streamname = "'.$_GET['meeting_id'].'"
			flashvars.username = "'. $_GET['user'] . '"
            var params = {};
            params.quality = "high";
            params.bgcolor = "#ffffff";
            params.allowscriptaccess = "sameDomain";
			params.allowFullScreenInteractive = "true";
           
            var attributes = {};
            attributes.id = "VC2_beta_UI_Grey";
            attributes.name = "VC2_beta_UI_Grey";
            attributes.align = "middle";
            swfobject.embedSWF(
                "webconf.swf", "flashContent", 
                "100%", "100%", 
                swfVersionStr, xiSwfUrlStr, 
                flashvars, params, attributes);
            // JavaScript enabled so display the flashContent div in case it is not replaced with a swf object.
            swfobject.createCSS("#flashContent", "display:block;text-align:left;");
        </script>
    </head>
    <body>
        <div id="flashContent">
            <p>
                To view this page ensure that Adobe Flash Player version 
                11.1.0 or greater is installed. 
            </p>
            <script type="text/javascript"> 
                var pageHost = ((document.location.protocol == "https:") ? "https://" : "http://"); 
                document.write(\"<a href=\'http://www.adobe.com/go/getflashplayer\'><img src=\'\" 
                                + pageHost + "www.adobe.com/images/shared/download_buttons/get_flash_player.gif\' alt=\'Get Adobe Flash player\' /></a>" ); 
            </script> 
        </div>
        
        <noscript>
            <object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" width=\"100%\" height=\"100%\" id=\"VC2_beta_UI_Grey\">
                <param name="movie" value="webconf.swf" />
                <param name="quality" value="high" />
                <param name="bgcolor" value="#ffffff" />
                <param name="allowScriptAccess" value="sameDomain" />
                <param name="allowFullScreenInteractive" value="true" />
                <!--[if !IE]>-->
                <object type="application/x-shockwave-flash" data="VC2_beta_UI_Grey.swf" width="100%" height="100%">
                    <param name="quality" value="high" />
                    <param name="bgcolor" value="#ffffff" />
                    <param name="allowScriptAccess" value="sameDomain" />
                    <param name="allowFullScreenInteractive" value="true" />
                <!--<![endif]-->
                <!--[if gte IE 6]>-->
                    <p> 
                        Either scripts and active content are not permitted to run or Adobe Flash Player version
                        11.1.0 or greater is not installed.
                    </p>
                <!--<![endif]-->
                    <a href="http://www.adobe.com/go/getflashplayer">
                        <img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash Player" />
                    </a>
                <!--[if !IE]>-->
                </object>
                <!--<![endif]-->
            </object>
        </noscript>     
   </body>
</html>
';
?>