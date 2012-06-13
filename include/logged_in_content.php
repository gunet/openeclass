<?php
/* ========================================================================
 * Open eClass 2.4
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

/*
 * Logged In Component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract This component creates the content of the start page when the
 * user is logged in
 *
 */
if (!defined('INDEX_START')) {
	die('Action not allowed!');
}
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

require_once 'include/perso.php';

$tool_content = "
<div class='panel_left'>
<p class='panel_title'>{%LANG_MY_PERSO_LESSONS%}</p>
<div class='panel_content_open'>{%LESSON_CONTENT%}</div>
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
