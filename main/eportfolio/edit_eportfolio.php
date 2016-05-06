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

$require_login = true;
$require_valid_uid = true;

require_once '../../include/baseTheme.php';
require_once 'main/eportfolio/eportfolio_functions.php';

check_uid();
check_guest();

$toolName = $langMyePortfolio;
$pageName = $langEditePortfolio;
$navigation[] = array('url' => 'eportfolio.php', 'name' => $langMyePortfolio);

if (!get_config('eportfolio_enable')) {
    $tool_content = "<div class='alert alert-danger'>$langePortfolioDisabled</div>";
    draw($tool_content, 1);
    exit;
}

load_js('tools.js');

if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

    //check for validation errors in e-portfolio fields
    $v = new Valitron\Validator($_POST);
    epf_validate($v);
    if (!$v->validate()) {
        Session::flashPost();
        Session::flashPost()->Messages($langFormErrors, 'alert-danger')->Errors($v->errors());
        redirect_to_home_page("main/eportfolio/edit_eportfolio.php");
    } else {

        process_eportfolio_fields_data();
    
        Session::Messages($langePortfolioChangeSucc, 'alert-success');
        redirect_to_home_page("main/eportfolio/eportfolio.php");
    }
}

$head_content .= "<script type='text/javascript'>
    $(document).ready(function() {
        /* Check if we are in safari and fix Bootstrap Affix*/
        if (navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1) {
        var stickywidget = $('#floatMenu');
        var explicitlySetAffixPosition = function() {
            stickywidget.css('left',stickywidget.offset().left+'px');
        };
        /* Before the element becomes affixed, add left CSS that is equal to the distance of the element from the left of the screen */
        stickywidget.on('affix.bs.affix',function(){
            stickywidget.removeAttr('style');
            explicitlySetAffixPosition();
        });
        stickywidget.on('affixed-bottom.bs.affix',function(){
            stickywidget.css('left', 'auto');
        });
        /* On resize of window, un-affix affixed widget to measure where it should be located, set the left CSS accordingly, re-affix it */
        $(window).resize(function(){
            if(stickywidget.hasClass('affix')) {
                stickywidget.removeClass('affix');
                explicitlySetAffixPosition();
                stickywidget.addClass('affix');
            }
        });
    }
    </script>";

$head_content .= "
        <script>
        $(function() {
            $('body').scrollspy({ target: '#affixedSideNav' });
        });
        </script>
    ";

$head_content .= "
        <script>
        $(function() {
            $('#floatMenu').affix({
              offset: {
                top: 230,
                bottom: function () {
                  return (this.bottom = $('.footer').outerHeight(true))
                }
              }
            })
        });
        </script>";

$sec = $urlServer . 'main/eportfolio/edit_eportfolio.php';

$tool_content .=
        action_bar(array(
            array('title' => $langBack,
                'url' => "eportfolio.php",
                'icon' => 'fa-reply',
                'level' => 'primary-label')));
        $tool_content .=
            "<div class='row'>
                <div class='col-sm-9'>
                    <form class='form-horizontal' role='form' action='$sec' method='post'>";
                    


//add custom profile fields
$ret_str = render_eportfolio_fields_form();
$tool_content .= $ret_str['panels'];

$tool_content .= "<div class='form-group'>
                      <div class='col-sm-12'>
                          <input class='btn btn-primary' type='submit' name='submit' value='$langSubmit'>
                          <a href='display_eportfolio.php' class='btn btn-default'>$langCancel</a>
                      </div>
                  </div>
      ". generate_csrf_token_form_field() ."  
      </form>
      </div>
      ".$ret_str['right_menu']."
      </div>";

draw($tool_content, 1, null, $head_content);
