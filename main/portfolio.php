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
<div class='row'>
        <div class='col-md-10'>
            <h1 class='page-title'>Prosopiko Xartofylakio</h1>
        </div>

        <div class ='col-md-2'>
                <div class='toolbox'>
                        <a href='../../modules/contact/index.php?course=TMAPOST100' id=''>
                                <fe-ringbutton class='button hover-blue' title='' >
                                        <i class='fa fa-life-ring'></i>
                                </button>
                        </a> 
                </div>
        </div>    
</div>

<div class='row'>
        <div class='col-md-7'>
                <h5 class='content-title'>{%LANG_MY_PERSO_LESSONS%}</h5>
                <div class='panel'>
                        {%LESSON_CONTENT%}
                        <br/><br/><br/><br/><br/><br/><br/><br/>
                        <br/><br/><br/><br/><br/><br/><br/><br/>
                        <br/><br/><br/><br/><br/><br/><br/><br/>
                </div>
        </div>



        <div class='col-md-5'>
                <h5 class='content-title'>{%LANG_MY_PERSONAL_CALENDAR%}</h5>
                <div class='panel'>
                        {%PERSONAL_CALENDAR_CONTENT%}
                </div>
        </div>



        <div class='col-md-5'>
                <h5 class='content-title'>{%LANG_MY_PERSO_ANNOUNCEMENTS%}</h5>
                <div class='panel'>

                        <ul class='tablelist panel'>
                
                <li class='list-item'>
                        <span class='item-title'>.......... 1</span>
                        <div class='item-right-cols'>
                        <span class='item-date'><span class='item-content'>13/2/2019</span></span>
                        </div>
                </li>

                <li class='list-item'>
                        <span class='item-title'>.......... 2</span>
                        <div class='item-right-cols'>
                        <span class='item-date'><span class='item-content'>13/2/2019</span></span>
                        </div>
                </li>

                <li class='list-item'>
                        <span class='item-title'>.......... 3</span>
                        <div class='item-right-cols'>
                        <span class='item-date'><span class='item-content'>13/2/2019</span></span>
                        </div>
                </li>
        
                        </ul>


                </div>
        </div>


</div>

<div class='row'>
        <div class='col-md-12'>
                <div class='panel'>
                        <div class='col-sm-3'>
                                <div><img src='../../ifeneris/template/bootstrap/img/user.jpg' class='img-circle' alt='Circular Image' ></div>
                        </div>
                        <div class='col-sm-9'>
                                <div> <canvas id='canvas' height='150' width='600'></canvas></div>
                        </div> 

                </div>
        </div>
</div>
";

draw($tool_content, 1, null, $head_content, null, null, $perso_tool_content);
