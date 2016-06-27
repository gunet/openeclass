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


require_once '../include/baseTheme.php';
$pageName = $langFaq;

$head_content .= "<script type='text/javascript'>
    $(document).ready(function() {
      $(document).on('click', '.expand:not(.revert)', function(e) {
        e.preventDefault();
        $('.faq-section .panel-collapse:not(.in)').collapse('show');
        $(this).toggleClass('revert');
        $(this).children().eq(0).toggleClass('fa-plus-circle').toggleClass('fa-minus-circle');
        $(this).children().eq(1).html('$langFaqCloseAll');
      });
      $(document).on('click', '.expand.revert', function(e) {
        e.preventDefault();
        $('.faq-section .panel-collapse.in').collapse('hide');
        $(this).toggleClass('revert');
        $(this).children().eq(0).toggleClass('fa-minus-circle').toggleClass('fa-plus-circle');
        $(this).children().eq(1).html('$langFaqExpandAll');
      });
    });
  </script>";

$data['faqs'] = Database::get()->queryArray("SELECT * FROM faq ORDER BY `order` ASC");

$data['action_bar'] = action_bar(array(
                                array('title' => $langFaqExpandAll,
                                      'url' => "#",
                                      'class' => 'expand',
                                      'icon' => 'fa-plus-circle',
                                      'level' => 'primary-label'),
                                array('title' => $langBack,
                                      'url' => $urlServer,
                                      'icon' => 'fa-reply',
                                      'level' => 'primary-label',
                                      'button-class' => 'btn-default')
                            ),false);

$data['menuTypeID'] = isset($uid) && $uid ? 1 : 0;

view('info.faq', $data);