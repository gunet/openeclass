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

if(!empty($langLanguageCode)){
    load_js('bootstrap-calendar-master/js/language/'.$langLanguageCode.'.js');
}
load_js('bootstrap-calendar-master/js/calendar.js');
load_js('bootstrap-calendar-master/components/underscore/underscore-min.js');

// jquery is already loaded via index.php and modal box
$head_content .= "
<link rel='stylesheet' type='text/css' href='{$urlAppend}js/bootstrap-calendar-master/css/calendar_small.css' />"
."<script type='text/javascript'>
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
  });"
  .'var calendar = $("#bootstrapcalendar").calendar({
                    tmpl_path: "'.$urlAppend.'js/bootstrap-calendar-master/tmpls/",
                    events_source: "'.$urlAppend.'main/calendar_data.php",
                    language: "'.$langLanguageCode.'",
                    views: {year:{enable: 0}, week:{enable: 0}, day:{enable: 0}},
                    onAfterViewLoad: function(view) {
                                $("#current-month").text(this.getTitle());
                                $(".btn-group button").removeClass("active");
                                $("button[data-calendar-view=\'" + view + "\']").addClass("active");
                                }
        });
        
        $(".btn-group button[data-calendar-nav]").each(function() {
            var $this = $(this);
            $this.click(function() {
                calendar.navigate($this.data("calendar-nav"));
            });
        });

        $(".btn-group button[data-calendar-view]").each(function() {
            var $this = $(this);
            $this.click(function() {
                calendar.view($this.data("calendar-view"));
            });
        });'
."});
".
'function show_month(day,month,year){
    $.get("calendar_data.php",{caltype:"small", day:day, month: month, year: year}, function(data){$("#smallcal").html(data);});    
}
</script>';

require_once 'perso.php';

$tool_content = "
<div class='row margin-top-thin'>

        <div class ='col-md-12'>
                <div class='toolbox pull-right'>
                        <a href='../../modules/contact/index.php?course=TMAPOST100' id=''>
                                <button class='btn-default-eclass' title='' >
                                        <i class='fa fa-life-ring'></i>
                                </button>
                        </a> 
                
                        <button class='btn-default-eclass'>{%HELP_LINK_ICON%}</button>
                        <button class='btn-default-eclass'>{%RSS_LINK_ICON%}</button>
                        <small class='actmod'>&nbsp;{%ACTIVATE_MODULE%}</small>
                </div>
        </div>    
</div>

<div class='row margin-top-thin'>
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
            <div class='panel-body'>
                <div class='row'>
                    <div class='col-sm-3'>
                            <div><img src='" . user_icon($uid, IMAGESIZE_LARGE) . "' style='width:150px;' class='img-circle' alt='Circular Image'></div>
                    </div>
                    <div class='col-sm-9'>
                        <div> <canvas id='canvas' height='150' width='600'></canvas></div>
                    </div> 
                </div>
            </div>
        </div>
    </div>
</div>
";
draw($tool_content, 1, null, $head_content, null, null, $perso_tool_content);
