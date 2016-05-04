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


$require_admin = TRUE;
require_once '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';

$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$toolName = $langAdminCreateFaq;
$pageName = $langAdminCreateFaq;

$tool_content = action_bar(array(
                            array('title' => 'Expand All',
                                  'url' => "#",
                                  'icon' => 'fa-plus-circle',
                                  'level' => 'primary-label'),
                            array('title' => $langBack,
                                  'url' => "adminannouncements.php",
                                  'icon' => 'fa-reply',
                                  'level' => 'primary-label')),false);

if (isset($_POST['submitFaq'])) {
    $question = $_POST['question'];
    $answer = $_POST['answer'];

    $top = Database::get()->querySingle("SELECT MAX(`order`) as max FROM `faq`")->max;

    Database::get()->query("INSERT INTO faq (title, body, `order`) VALUES (?s, ?s, ?d)", $question, $answer, $top + 1);
}

$faqs = Database::get()->queryArray("SELECT * FROM faq ORDER BY `order` DESC");

$faqCounter = 0;

$tool_content .= "
  <div class='row'>
      <div class='col-xs-12'>
          <div class='panel-group faq-section' id='accordion' role='tablist' aria-multiselectable='true'>";

            foreach ($faqs as $faq) {
              $faqCounter++;
              $tool_content .= "

              <div class='panel'>
                <div class='panel-heading' role='tab' id='headingOne'>
                  <h4 class='panel-title'>
                    <a role='button' data-toggle='collapse' data-parent='#accordion' href='#faq-$faq->id' aria-expanded='true' aria-controls='#$faq->id'>
                        $faqCounter) $faq->title
                    </a>
                  </h4>
                </div>
                <div id='faq-$faq->id' class='panel-collapse collapse' role='tabpanel' aria-labelledby='headingOne'>
                  <div class='panel-body'>
                    <p><strong><u>$langFaqAnswer:</u></strong></p>
                    $faq->body
                  </div>
                </div>
              </div>
              ";
            }
                
$tool_content .= "
          </div>
      </div>
  </div>
";

$tool_content .= "
    <hr style='padding-bottom: 20px; margin: 5px 0;'>

    <div class='row'>
      <div class='col-xs-12'>
        <div class='panel-group faq-section' id='accordion-create' role='tablist' aria-multiselectable='true'>
          <div class='panel panel-success'>
            <div class='panel-heading text-center' role='tab' id='headingOne'>
              <h4 class='panel-title'>
                <a role='button' data-toggle='collapse' data-parent='#accordion-create' href='#newfaq' aria-expanded='true' aria-controls='#newfaq'>
                    <span class='fa fa-plus-circle'></span> $langFaqAdd
                </a>
              </h4>
            </div>
            <div id='newfaq' class='panel-collapse collapse' role='tabpanel' aria-labelledby='headingOne'>
              <div class='panel-body'>
                <form role='form' class='form-horizontal' method='post' action='$_SERVER[SCRIPT_NAME]'>
                    <div class='form-group'>
                        <label for='question' class='col-sm-2 control-label'>$langFaqQuestion <sup><small>(<span class='text-danger'>*</span>)</small></sup>:</label>
                        <div class='col-sm-10'>
                            <input class='form-control' type='text' name='question' value='' />
                        </div>
                    </div>
                    <div class='form-group'>
                        <label for='answer' class='col-sm-2 control-label'>$langFaqAnswer <sup><small>(<span class='text-danger'>*</span>)</small></sup>:</label>
                        <div class='col-sm-10'>" . rich_text_editor('answer', 5, 40, '') . "</div>
                    </div>
                    <div class='form-group'>
                        <div class='col-sm-offset-2 col-sm-10'>
                            <sup><small>(<span class='text-danger'>*</span>)</small></sup> <small class='text-muted'>$langCPFFieldRequired</small>
                        </div>
                    </div>
                    <div class='form-group'>
                        <div class='col-sm-offset-2 col-sm-10'>
                            <input id='submitFaq' class='btn btn-primary' type='submit' name='submitFaq' value='$langSubmit'>
                        </div>
                    </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

";

draw($tool_content, 3, null, $head_content);