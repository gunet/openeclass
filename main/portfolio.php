<?php

/* ========================================================================
 * Open eClass 3.0
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
 * ======================================================================== */

/**
 * @file portfolio.php
 * @brief This component creates the content of the start page when the user is logged in
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 */

$require_login = true;

$require_help = true;
$helpTopic = 'Portfolio';

include '../include/baseTheme.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/lib/fileUploadLib.inc.php';

$nameTools = $langWelcomeToPortfolio;

ModalBoxHelper::loadModalBox();

// jquery is already loaded via index.php and modal box
$head_content .= "<script type='text/javascript'>
jQuery(document).ready(function() {
  jQuery('.panel_content').hide();
   jQuery('.panel_content_open').show();
  jQuery('.panel_title').click(function()
  {
    var mypanel = $(this).next();
    mypanel.slideToggle(100);
    if($(this).hasClass('active')) {
    $(this).removeClass('active');
    } else {
    $(this).addClass('active');
    }
  });
});
".
'function show_month(day,month,year){
    $.get("calendar_data.php",{caltype:"small", day:day, month: month, year: year}, function(data){$("#smallcal").html(data);});    
}
</script>';

require_once 'perso.php';

$tool_content = "
<div class='panel_left'>
<p class='panel_title'>{%LANG_MY_PERSO_LESSONS%}</p>
<div class='panel_content_open'>{%LESSON_CONTENT%}</div>

<p class='panel_title'>{%LANG_MY_PERSONAL_CALENDAR%}</p>
<div id='smallcal' class='panel_content_open'>{%PERSONAL_CALENDAR_CONTENT%}</div>
</div>


<div class='panel_right'>
<p class='panel_title'>{%LANG_MY_PERSO_ANNOUNCEMENTS%}</p>
<div class='panel_content'>{%ANNOUNCE_CONTENT%}</div>

<p class='panel_title'>{%LANG_MY_PERSO_AGENDA%}</p>
<div class='panel_content'>{%AGENDA_CONTENT%}</div>

<p class='panel_title'>{%LANG_MY_PERSO_DEADLINES%}</p>
<div class='panel_content'>{%ASSIGN_CONTENT%}</div>

<p class='panel_title'>{%LANG_MY_PERSO_DOCS%}</p>
<div class='panel_content'>{%DOCS_CONTENT%}</div>

<p class='panel_title'>{%LANG_PERSO_FORUM%}</p>
<div class='panel_content'>{%FORUM_CONTENT%}</div>


</div>";

draw($tool_content, 1, null, $head_content, null, null, $perso_tool_content);
