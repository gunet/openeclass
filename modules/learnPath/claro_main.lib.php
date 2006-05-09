<?php # $Id$
/**
 * CLAROLINE
 *
 * @version 1.7 $Revision$
 *
 * @copyright (c) 2001, 2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author see 'credits' file
 *
 * @package KERNEL
 *
 */
//////////////////////////////////////////////////////////////////////////////
//                              DISPLAY OPTIONS
//                            student    view, title, ...
//////////////////////////////////////////////////////////////////////////////


/**
 * Displays the title of a tool. Optionally, there can be a subtitle below
 * the normal title, and / or a supra title above the normal title.
 *
 * e.g. supra title:
 * group
 * GROUP PROPERTIES
 *
 * e.g. subtitle:
 * AGENDA
 * calender & events tool
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param  mixed $titleElement - it could either be a string or an array
 *                               containing 'supraTitle', 'mainTitle',
 *                               'subTitle'
 * @return void
 */

function claro_disp_tool_title($titlePart, $helpUrl = false)
{
    // if titleElement is simply a string transform it into an array

    if ( is_array($titlePart) )
    {
        $titleElement = $titlePart;
    }
    else
    {
        $titleElement['mainTitle'] = $titlePart;
    }


    $string = "\n" . '<h3 class="claroToolTitle">' . "\n";

    if ($helpUrl)
    {
        global $clarolineRepositoryWeb, $imgRepositoryWeb,$langHelp;

    $string .= "<a href='#' onClick=\"MyWindow=window.open('". $clarolineRepositoryWeb . "help/" .$helpUrl
            ."','MyWindow','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=350,height=450,left=300,top=10'); return false;\">"

            .'<img src="'.$imgRepositoryWeb.'/help.gif" '
            .' alt ="'.$langHelp.'"'
            .' align="right"'
            .' hspace="30">'
            .'</a>' . "\n"
            ;
    }


    if ( isset($titleElement['supraTitle']) )
    {
        $string .= '<small>' . $titleElement['supraTitle'] . '</small><br />' . "\n";
    }

    if ( isset($titleElement['mainTitle']) )
    {
        $string .= $titleElement['mainTitle'] . "\n";
    }

    if ( isset($titleElement['subTitle']) )
    {
        $string .= '<br /><small>' . $titleElement['subTitle'] . '</small>' . "\n";
    }

    $string .= '</h3>'."\n\n";

    return $string;
}


/**
 * Prepare display of the message box appearing on the top of the window,
 * just    below the tool title. It is recommended to use this function
 * to display any confirmation or error messages, or to ask to the user
 * to enter simple parameters.
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param string $message - include your self any additionnal html
 *                          tag if you need them
 * @return $string - the
 */

function claro_disp_message_box($message)
{
    return "\n".'<table class="claroMessageBox" border="0" cellspacing="0" cellpadding="10">'
    .      '<tr>'
    .      '<td>'
    .      $message
    .      '</td>'
    .      '</tr>'
    .      '</table>' . "\n\n"
    ;
}

/**
 * Terminate the script and display message
 *
 * @param string message
 */

function claro_die($message)
{
    global $includePath, $clarolineRepositoryWeb, $claro_stylesheet, $rootWeb,
           $siteName, $text_dir, $uid, $_cid, $administrator_name, $administrator_email,
           $is_platformAdmin, $_course, $_user, $_courseToolList, $coursesRepositoryWeb,
           $is_courseAllowed, $imgRepositoryWeb, $lang_footer_p_CourseManager,
           $lang_p_platformManager, $langPoweredBy, $langModifyProfile,
           $langLogout, $langOtherCourses, $langModifyProfile, $langMyCourses,
           $langMyAgenda, $langLogin, $langCourseHome, $_tid;

    if ( ! headers_sent () )
    {
    // display header
        require $includePath . '/claro_init_header.inc.php';
    }

    echo '<table align="center">'
    .    '<tr><td>'
    .    claro_disp_message_box($message)
    .    '</td></tr>'
    .    '</table>'
    ;

    require $includePath . '/claro_init_footer.inc.php' ;

    die(); // necessary to prevent any continuation of the application
}


/**
 * Prepare the display of a clikcable button
 *
 * This function is needed because claroline buttons rely on javascript.
 * The function return an optionnal behavior fo browser where javascript
 * isn't  available.
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 *
 * @param string $url url inserted into the 'href' part of the tag
 * @param string $text text inserted between the two <a>...</a> tags (note : it
 *        could also be an image ...)
 * @param string $confirmMessage (optionnal) introduce a javascript confirmation popup
 * @return string the button
 */

function claro_disp_button($url, $text, $confirmMessage = '')
{

    if (   claro_is_javascript_enabled()
        && ! preg_match('~^Mozilla/4\.[1234567]~', $_SERVER['HTTP_USER_AGENT']))
    {
        if ($confirmMessage != '')
        {
            $onClickCommand = "if(confirm('" . clean_str_for_javascript($confirmMessage) . "')){document.location='" . $url . "';return false}";
        }
        else
        {
            $onClickCommand = "document.location='".$url."';return false";
        }

        return '<button class="claroButton" onclick="' . $onClickCommand . '">'
        .      $text
        .      '</button>&nbsp;' . "\n"
        ;
    }
    else
    {
        return '<nobr>[ <a href="' . $url . '">' . $text . '</a> ]</nobr>';
    }
}

/**
 * Function used to draw a progression bar
 *
 * @author Piraux Sï¿½astien <pir@cerdecam.be>
 *
 * @param integer $progress progression in pourcent
 * @param integer $factor will be multiply by 100 to have the full size of the bar
 * (i.e. 1 will give a 100 pixel wide bar)
 */

function claro_disp_progress_bar ($progress, $factor)
{
    $maxSize  = $factor * 100; //pixels
    $barwidth = $factor * $progress ;

    // display progress bar
    // origin of the bar
    $progressBar = '<img src="../../images/bar_1.gif" width="1" height="12" alt="">';

    if($progress != 0)
            $progressBar .= '<img src="../../images/bar_1u.gif" width="' . $barwidth . '" height="12" alt="">';
    // display 100% bar

    if($progress!= 100 && $progress != 0)
            $progressBar .= '<img src="../../images/bar_1m.gif" width="1" height="12" alt="">';

    if($progress != 100)
            $progressBar .= '<img src="../../images/bar_1r.gif" width="' . ($maxSize - $barwidth) . '" height="12" alt="">';
    // end of the bar
    $progressBar .=  '<img src="../../images/bar_1.gif" width="1" height="12" alt="">';

    return $progressBar;
}


/**
 * Insert a    sort of    HTML Wysiwyg textarea inside a FORM
 * the html area currently implemented is HTMLArea 3.0. To work correctly,
 * the area    needs a    specific stylesheet
 * previously loaded in the html header.
 * For that, use the claroline $htmlHeadXtra[] array at
 * the top of the script
 * just before including claro_init_header.inc.php
 *
 * @param string $name content for name attribute in textarea tag
 * @param string $content optional content previously inserted into    the    area
 * @param int     $rows optional    textarea rows
 * @param int     $cols optional    textarea columns
 * @param string $optAttrib    optional - additionnal tag attributes
 *                                       (wrap, class, ...)
 * @return void
 *
 * @global strin urlAppend from    claro_main.conf.php
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 */
// Example : $htmlHeadXtra[] = '<style type="text/css">
//                               @import url('.$urlAppend.'/claroline/inc/htmlarea'.'/htmlarea.css);
//                              </style>';

function claro_disp_html_area($name, $content = '',
                              $rows=20,    $cols=80, $optAttrib='')
{
    global $urlAppend, $iso639_1_code, $langTextEditorDisable, $langTextEditorEnable,$langSwitchEditorToTextConfirm;
    $incPath = $urlAppend.'/claroline/inc/htmlarea';

    ob_start();

    if( ! isset( $_SESSION['htmlArea'] ) )
    {
        // TODO use a config variable instead of hardcoded value
        $_SESSION['htmlArea'] = 'enabled';
    }

    if (isset($_REQUEST['areaContent'])) $content = stripslashes($_REQUEST['areaContent']);

    if (claro_is_javascript_enabled())
    {
        if ( isset($_SESSION['htmlArea']) && $_SESSION['htmlArea'] != 'disabled' )
        {
            $switchState = 'off';
            $message     = $langTextEditorDisable;
            $areaContent = 'editor.getHTML()';
            $confirmCommand = "if(!confirm('".clean_str_for_javascript($langSwitchEditorToTextConfirm)."'))return(false);";
        }
        else
        {
            $switchState = 'on';
            $message     = $langTextEditorEnable;
            $areaContent = 'document.getElementById(\''.$name.'\').value';
            $confirmCommand = '';
        }

        $location = '\''
        .           $incPath.'/editorswitcher.php?'
        .           'switch='.$switchState
        .           '&sourceUrl=' . urlencode($_SERVER['REQUEST_URI'])
        .           '&areaContent='
        .           '\''
        .           '+escape('.$areaContent.')'
        ;



        echo "\n".'<div align="right">'
        .    '<small>'
        .    '<b>'
        .    '<a href="/" onClick ="' . $confirmCommand . 'window.location='
        .    $location . ';return(false);">'
        .    $message
        .    '</a>'
        .    '</b>'
        .    '</small>'
        .    '</div>'."\n"
        ;

    } // end if claro_is_javascript_enabled()


echo '<textarea '
        .'id="'.$name.'" '
        .'name="'.$name.'" '
        .'style="width:100%" '
        .'rows="'.$rows.'" '
        .'cols="'.$cols.'" '
        .$optAttrib.' >'
        ."\n".$content."\n"
        .'</textarea>'."\n";

    if ( isset($_SESSION['htmlArea']) && $_SESSION['htmlArea'] != 'disabled' )
    {

?>

<script type="text/javascript">_editor_url = "<?php echo  $incPath?>";</script>
<script type="text/javascript" src="<?php echo $incPath; ?>/htmlarea.js"></script>
<script type="text/javascript" src="<?php echo $incPath; ?>/lang/<?php echo $iso639_1_code; ?>.js"></script>
<script type="text/javascript" src="<?php echo $incPath; ?>/dialog.js"></script>

<script type="text/javascript">
var    editor = null;
function initEditor() {
  editor = new HTMLArea("<?php echo $name ?>");

  // comment the following two lines to    see    how    customization works
  editor.generate();
  return false;
}
<?php
// there is no link or button to use these functions, so do not output them
/*
function insertHTML() {
 var html =    prompt("Enter some HTML    code here");
 if    (html) {editor.insertHTML(html);}
}
function highlight() {
  editor.surroundHTML('<span style="background-color: yellow">', '</span>');
}
*/
?>
</script>

<script type="text/javascript">
initEditor();
</script>
<?php
    } // end if  $_SESSION['htmlArea'] != 'disabled'
    else
    {
        // noop
    }

    $returnString = ob_get_contents();
    ob_end_clean();
    return $returnString;
}

/**
 * function claro_build_nested_select_menu($name, $elementList)
 * Build in a relevant way 'select' menu for an HTML form containing nested data
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 *
 * @param string $name, name of the select tag
 *
 * @param array nested data in a composite way
 *
 *  Exemple :
 *
 *  $elementList[1]['name'    ] = 'level1';
 *  $elementList[1]['value'   ] = 'level1';
 *
 *  $elementList[1]['children'][1]['name' ] = 'level2';
 *  $elementList[1]['children'][1]['value'] = 'level2';
 *
 *  $elementList[1]['children'][2]['name' ] = 'level2';
 *  $elementList[1]['children'][2]['value'] = 'level2';
 *
 *  $elementList[2]['name' ]  = 'level1';
 *  $elementList[2]['value']  = 'level1';
 *
 * @return string the HTML flow
 * @desc depends on prepare option tags
 *
 */

function claro_build_nested_select_menu($name, $elementList)
{
    return '<select name="' . $name . '">' . "\n"
    .      implode("\n", prepare_option_tags($elementList) )
    .      '</select>' .  "\n"
    ;
}

/**
 * prepare the 'option' html tag for the claro_disp_nested_select_menu()
 * fucntion
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param array $elementList
 * @param int  $deepness (optionnal, default is 0)
 * @return array of option tag list
 */


function prepare_option_tags($elementList, $deepness = 0)
{
    foreach($elementList as $thisElement)
    {
        $tab = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $deepness);

        $optionTagList[] = '<option value="'.$thisElement['value'].'">'
        .                  $tab.$thisElement['name']
        .                  '</option>'
        ;
        if (   isset( $thisElement['children'] )
            && sizeof($thisElement['children'] ) > 0)
        {
            $optionTagList = array_merge( $optionTagList,
                                          prepare_option_tags($thisElement['children'],
                                                              $deepness + 1 ) );
        }
    }

    return  $optionTagList;
}
//////////////////////////////////////////////////////////////////////////////
//                              INPUT HANDLING
//
//////////////////////////////////////////////////////////////////////////////

/**
 * checks if the javascript is enabled on the client browser
 * Actually a cookies is set on the header by a javascript code.
 * If this cookie isn't set, it means javascript isn't enabled.
 *
 * @return boolean enabling state of javascript
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 */

function claro_is_javascript_enabled()
{
    global $_COOKIE;

    if ( isset( $_COOKIE['javascriptEnabled'] ) && $_COOKIE['javascriptEnabled'] == true)
    {
        return true;
    }
    else
    {
        return false;
    }
}

/**
 * Parse the user text (e.g. stored in database)
 * before displaying it to the screen
 * For example it change new line charater to <br> tag etc.
 *
 * @param string $userText original user tex
 * @return string parsed user text
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 */

function claro_parse_user_text($userText)
{
   global $claro_texRendererUrl; // see 'inc/conf/claro_main.conf.php'

   if ( !empty($claro_texRendererUrl) )
   {
       $userText = str_replace('[tex]',
                          '<img src="'.$claro_texRendererUrl.'?',
                          $userText);

       $userText = str_replace('[/tex]',
                           '" border="0" align="absmiddle">',
                           $userText);
   }
   else
   {
       $userText = str_replace('[tex]',
                              '<embed TYPE="application/x-techexplorer" texdata="',
                              $userText);

       $userText = str_replace('[/tex]',
                               '" width="100%" pluginspace="http://www.integretechpub.com/">',
                               $userText);
   }

   $userText = make_clickable($userText);

   if ( strpos($userText, '<!-- content: html -->') === false )
   {
        // only if the content isn't HTML change new line to <br>
        // Note the '<!-- content: html -->' is introduced by HTML Area
        $userText = nl2br($userText);
   }

    return $userText;
}

/**
 * Completes url contained in the text with "<a href ...".
 * However the function simply returns the submitted text without any
 * transformation if it already contains some "<a href:" or "<img src=".
 * @param  string $text text to be converted
 * @return string   text after conversion
 *
 * Actually this function is taken from the PHP BB 1.4 script
 * - Goes through the given string, and replaces xxxx://yyyy with an HTML <a> tag linking
 *  to that URL
 * - Goes through the given string, and replaces www.xxxx.yyyy[zzzz] with an HTML <a> tag linking
 *  to http://www.xxxx.yyyy[/zzzz]
 * - Goes through the given string, and replaces xxxx@yyyy with an HTML mailto: tag linking
 *      to that email address
 * - Only matches these 2 patterns either after a space, or at the beginning of a line
 *
 * Notes: the email one might get annoying - it's easy to make it more restrictive, though.. maybe
 * have it require something like xxxx@yyyy.zzzz or such. We'll see.
 *
 * @author Rewritten by Nathan Codding - Feb 6, 2001.
 *         completed by Hugues Peeters - July 22, 2002
 */

function make_clickable($text)
{

    // If the user has decided to deeply use html and manage himself hyperlink
    // cancel the make clickable() function and return the text untouched. HP

    if (preg_match ( "<(a|img)[[:space:]]*(href|src)[[:space:]]*=(.*)>", $text) )
    {
        return $text;
    }

    // pad it with a space so we can match things at the start of the 1st line.
    $ret = " " . $text;


    // matches an "xxxx://yyyy" URL at the start of a line, or after a space.
    // xxxx can only be alpha characters.
    // yyyy is anything up to the first space, newline, or comma.

    $ret = preg_replace("#([\n ])([a-z]+?)://([^, \n\r]+)#i",
                        "\\1<a href=\"\\2://\\3\" >\\2://\\3</a>",
                        $ret);

    // matches a "www.xxxx.yyyy[/zzzz]" kinda lazy URL thing
    // Must contain at least 2 dots. xxxx contains either alphanum, or "-"
    // yyyy contains either alphanum, "-", or "."
    // zzzz is optional.. will contain everything up to the first space, newline, or comma.
    // This is slightly restrictive - it's not going to match stuff like "forums.foo.com"
    // This is to keep it from getting annoying and matching stuff that's not meant to be a link.

    $ret = preg_replace("#([\n ])www\.([a-z0-9\-]+)\.([a-z0-9\-.\~]+)((?:/[^, \n\r]*)?)#i",
                        "\\1<a href=\"http://www.\\2.\\3\\4\" >www.\\2.\\3\\4</a>",
                        $ret);

    // matches an email@domain type address at the start of a line, or after a space.
    // Note: before the @ sign, the only valid characters are the alphanums and "-", "_", or ".".
    // After the @ sign, we accept anything up to the first space, linebreak, or comma.

    $ret = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([^, \n\r]+)#i",
                        "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>",
                        $ret);

    // Remove our padding..
    $ret = substr($ret, 1);

    return($ret);
}


/**
 * function that cleans php string for javascript
 *
 * This function is needed to clean strings used in javascript output
 * Newlines are prohibited in the script, specialchar  are prohibited
 * quotes must be addslashes
 *
 * @param $str string original string
 * @return string cleaned string
 *
 * @author Piraux Sébastien <pir@cerdecam.be>
 *
 */
function clean_str_for_javascript( $str )
{
    $output = $str;
    // 1. addslashes, prevent problems with quotes
    // must be before the str_replace to avoid double backslash for \n
    $output = addslashes($output);
    // 2. turn windows CR into *nix CR
    $output = str_replace("\r", '', $output);
    // 3. replace "\n" by uninterpreted '\n'
    $output = str_replace("\n",'\n', $output);
    // 4. convert special chars into html entities
    $output = htmlspecialchars($output);

    return $output;
}

?>
