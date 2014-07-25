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
</script>";

require_once 'perso.php';




$tool_content = "
<div class='a-wrapper'>

  <div class='column-first column-one-half'>";

  // Contentbox: Course list
  $tool_content.= "
    <h5 class='content-title'>{%LANG_MY_PERSO_LESSONS%}</h5>
    <div class='contentbox padding'>
      {%LESSON_CONTENT%}
    </div>";

  $tool_content.= "
  </div>

  <div class='column-one-half'>";

    // Contentbox: Calendar
    $tool_content.= "
    <h5 class='content-title'>Ημερολογιο</h5>
    <div class='contentbox padding'>
      <img src='http://users.auth.gr/panchara/eclass/project/img/calendar.png' style='margin:1em auto;display:block; max-width:100%;''>
    </div>";

    // Contentbox: Calendar
    $tool_content.= "
    <h5 class='content-title'>Ανακοινωσεις</h5>
    <ul class='tablelist contentbox'>
      <li class='list-item'>
        <span class='item-title'>Ανακοίνωση 1</span>
        <div class='item-right-cols'>
          <span class='item-date'><span class='item-content'>13/2/2019</span></span>
        </div>
      </li>

      <li class='list-item'>
        <span class='item-title'>Ανακοίνωση 2</span>
        <div class='item-right-cols'>
          <span class='item-date'><span class='item-content'>13/2/2019</span></span>
        </div>
      </li>

      <li class='list-item'>
        <span class='item-title'>Ανακοίνωση 3</span>
        <div class='item-right-cols'>
          <span class='item-date'><span class='item-content'>13/2/2019</span></span>
        </div>
      </li>
                
            
    </ul>";

$tool_content.= "
  </div>


</div>

<div style='clear: both'></div>

<div class='a-wrapper'>
  <div class='contentbox padding'>
      test 1<br/><br/><br/><br/><br/>
  </div>
</div>

";




$tool_content.= "
<br /><br /><br />
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
