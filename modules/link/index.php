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
 * @brief links module
 * partially based on code by Patrick Cool
 *
 */

$require_current_course = TRUE;
$require_help = true;
$helpTopic = 'Link';
$guest_allowed = true;

require_once '../../include/baseTheme.php';
require_once 'include/log.php';
require_once 'linkfunctions.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/course_settings.php';

require_once 'include/action.php';
$action_stats = new action();
$action_stats->record(MODULE_ID_LINKS);

$toolName = $langLinks;
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'addlink':
            $pageName = $langLinkAdd;
            break;
        case 'editlink':
            $pageName = $langLinkModify;
            break;
        case 'addcategory':
            $pageName = $langCategoryAdd;
            break;
        case 'editcategory':
            $pageName = $langCategoryMod;
            break;
        case 'settings':
            $pageName = $langLinkSettings;
            break;
    }
}

$is_in_tinymce = (isset($_REQUEST['embedtype']) && $_REQUEST['embedtype'] == 'tinymce') ? true : false;
$menuTypeID = ($is_in_tinymce) ? 5 : 2;
$tinymce_params = '';

if ($is_in_tinymce) {
    $_SESSION['embedonce'] = true; // necessary for baseTheme
    $docsfilter = (isset($_REQUEST['docsfilter'])) ? '&amp;docsfilter=' . $_REQUEST['docsfilter'] : '';
    $tinymce_params = '&amp;embedtype=tinymce' . $docsfilter;
    load_js('jquery-' . JQUERY_VERSION . '.min');
    load_js('tinymce.popup.urlgrabber.min.js');
}

ModalBoxHelper::loadModalBox();
$head_content .= <<<hContent
<script type="text/javascript">
function checkrequired(which, entry) {
	var pass=true;
	if (document.images) {
		for (i=0;i<which.length;i++) {
			var tempobj=which.elements[i];
			if (tempobj.name == entry) {
				if (tempobj.type=="text"&&tempobj.value=='') {
					pass=false;
					break;
		  		}
	  		}
		}
	}
	if (!pass) {
		alert("$langEmptyLinkURL");
		return false;
	} else {
		return true;
	}
}

</script>
hContent;

if (isset($_GET['category'])) {
    $category = intval($_GET['category']);
} else {
    unset($category);
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
} else {
    unset($id);
}

if (isset($_GET['urlview'])) {
    $urlview = urlencode($_GET['urlview']);
} else {
    $urlview = '';
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($is_editor) {
    if (isset($_POST['submitLink'])) {
        submit_link();
        $message = isset($_POST['id']) ? $langLinkMod : $langLinkAdded;
        Session::Messages($message, 'alert-success');
        redirect_to_home_page("modules/link/index.php");
    }
    if (isset($_POST['submitCategory'])) {
        submit_category();
        $messsage = isset($_POST['id']) ? $langCategoryModded : $langCategoryAdded;
        Session::Messages($messsage, 'alert-success');
        redirect_to_home_page("modules/link/index.php");
    }
    if (isset($_POST['submitSettings'])) {
        if (isset($_POST['settings_radio'])) {
            setting_set(SETTING_COURSE_SOCIAL_BOOKMARKS_ENABLE, intval($_POST['settings_radio']));
            Session::Messages($langLinkSettingsSucc, 'alert-success');
        }
        redirect_to_home_page("modules/link/index.php?course=$course_code");
    }
    switch ($action) {
        case 'deletelink':
            delete_link($id);
            Session::Messages($langLinkDeleted, 'alert-success');
            redirect_to_home_page("modules/link/index.php");
            break;
        case 'deletecategory':
            delete_category($id);
            Session::Messages($langCategoryDeleted, 'alert-success');
            redirect_to_home_page("modules/link/index.php");
            break;
    }


    if (!$is_in_tinymce) {
        if (isset($_GET['action'])) {
            $tool_content .= action_bar(array(
                array('title' => $langBack,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                      'icon' => 'fa-reply',
                      'level' => 'primary-label',
                      'show' => $is_editor)));

        } else {
            $ext = (isset($category)? "&amp;category=$category": '') .
                   (isset($urlview)? "&amp;urlview=$urlview": '');
            $tool_content .= action_bar(array(
                array('title' => $langLinkAdd,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;action=addlink$ext",
                      'icon' => 'fa-plus-circle',
                      'button-class' => 'btn-success',
                      'level' => 'primary-label'),
                array('title' => $langCategoryAdd,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;action=addcategory$ext",
                      'icon' => 'fa-plus-circle',
                      'button-class' => 'btn-success',
                      'level' => 'primary-label'),
                array('title' => $langConfig,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;action=settings",
                      'icon' => 'fa-gear',
                      'level' => 'primary')));
        }
    }

    // Display the correct title and form for adding or modifying a category or link.
    if (in_array($action, array('addlink', 'editlink'))) {
        $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langLinks);
        $tool_content .= "<div class = 'form-wrapper'>";
        $tool_content .= "<form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;urlview=$urlview' onsubmit=\"return checkrequired(this, 'urllink');\">";
        if ($action == 'editlink') {
            $tool_content .= "<input type='hidden' name='id' value='$id' />";
            link_form_defaults($id);
            $form_legend = $langLinkModify;
            $submit_label = $langLinkModify;
        } else {
            $form_url = $form_title = $form_description = '';
            $form_legend = $langLinkAdd;
            $submit_label = $langAdd;
        }
        $tool_content .= "
        <fieldset>
        <div class='form-group'>
            <label for='urllink' class='col-sm-2 control-label'>URL:</label>
            <div class='col-sm-10'>
                <input class='form-control' type='text' id='urllink' name='urllink' $form_url >
            </div>
        </div>
        <div class='form-group'>
            <label for='title' class='col-sm-2 control-label'>$langLinkName:</label>
            <div class='col-sm-10'>
                <input class='form-control' type='text' id='title' name='title'$form_title >
            </div>
         </div>
        <div class='form-group'>
            <label for='description' class='col-sm-2 control-label'>$langDescription:</label>
            <div class='col-sm-10'>". rich_text_editor('description', 3, 30, $form_description) . "</div>
        </div>
        <div class='form-group'>
            <label for='selectcategory' class='col-sm-2 control-label'>$langCategory:</label>
            <div class='col-sm-3'>
                <select class='form-control' name='selectcategory' id='selectcategory'>
                <option value='0'>--</option>";
        $resultcategories = Database::get()->queryArray("SELECT * FROM link_category WHERE course_id = ?d ORDER BY `order`", $course_id);
        foreach ($resultcategories as $myrow) {
            $tool_content .= "<option value='$myrow->id'";
            if (isset($category) and $myrow->id == $category) {
                $tool_content .= " selected='selected'";
            }
            $tool_content .= '>' . q($myrow->name) . "</option>";
        }
        $tool_content .= "
            </select>
            </div>
        </div>
        <div class='form-group'>
        <div class='col-sm-10 col-sm-offset-2'>
            <input type='submit' class='btn btn-primary' name='submitLink' value='$submit_label' />
            <a href='$_SERVER[SCRIPT_NAME]?course=$course_code' class='btn btn-default'>$langCancel</a>
        </div>
        </div>
        </fieldset>
        </form>
        </div>";
    } elseif (in_array($action, array('addcategory', 'editcategory'))) {
        $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langLinks);
        $tool_content .= "<div class = 'form-wrapper'>";
        $tool_content .= "<form class = 'form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&urlview=$urlview'>";
        if ($action == 'editcategory') {
            $tool_content .= "<input type='hidden' name='id' value='$id' />";
            category_form_defaults($id);
            $form_legend = $langCategoryMod;
        } else {
            $form_name = $form_description = '';
            $form_legend = $langCategoryAdd;
        }
        $tool_content .= "<fieldset>
                        <div class='form-group'>
                            <label for='CatName' class='col-sm-2 control-label'>$langCategoryName:</label>
                            <div class='col-sm-10'>
                                <input class='form-control' type='text' name='categoryname' size='53' placeholder='$langCategoryName' $form_name>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label for='CatDesc' class='col-sm-2 control-label'>$langDescription:</label>
                            <div class='col-sm-10'>
                                <textarea class='form-control' rows='5' name='description'>$form_description</textarea>
                            </div>
                        </div>
                        <div class='form-group'>
                            <div class='col-sm-10 col-sm-offset-2'>
                                <input type='submit' class='btn btn-primary' name='submitCategory' value='$form_legend' />
                                <a href='$_SERVER[SCRIPT_NAME]?course=$course_code' class='btn btn-default'>$langCancel</a>
                            </div>
                        </div>
                        </fieldset>
                    </form>
                </div>";
    } elseif ($action == 'settings') {
        $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langLinks);
        if (setting_get(SETTING_COURSE_SOCIAL_BOOKMARKS_ENABLE, $course_id) == 1) {
            $checkDis = "";
            $checkEn = "checked ";
        } else {
            $checkDis = "checked ";
            $checkEn = "";
        }
        $tool_content .= "<div class = 'form-wrapper'>";
        $tool_content .= "<form class = 'form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>";
        $tool_content .= "<fieldset>                               
                              <div class='form-group'>
                                  <label class='col-sm-3'>$langSocialBookmarksFunct</label>
                                  <div class='col-sm-9'> 
                                      <div class='radio'>
                                          <label>
                                              <input type='radio' value='1' name='settings_radio' $checkEn>$langActivate
                                          </label>
                                      </div>
                                      <div class='radio'>
                                          <label>
                                              <input type='radio' value='0' name='settings_radio' $checkDis>$langDeactivate
                                          </label>
                                      </div>
                                  </div>
                              </div>
                              <div class='form-group'>
                                  <div class='col-sm-9 col-sm-offset-3'>
                                      <input type='submit' class='btn btn-primary' name='submitSettings' value='$langSubmit' />
                                          <a href='$_SERVER[SCRIPT_NAME]?course=$course_code' class='btn btn-default'>$langCancel</a>
                                  </div>
                              </div>
                          </fieldset>
                      </form>
                  </div>";
    }
}

if (isset($_GET['down'])) {
    move_order('link', 'id', intval($_GET['down']), 'order', 'down', "course_id = $course_id");
} elseif (isset($_GET['up'])) {
    move_order('link', 'id', intval($_GET['up']), 'order', 'up', "course_id = $course_id");
} elseif (isset($_GET['cdown'])) {
    move_order('link_category', 'id', intval($_GET['cdown']), 'order', 'down', "course_id = $course_id");
} elseif (isset($_GET['cup'])) {
    move_order('link_category', 'id', intval($_GET['cup']), 'order', 'up', "course_id = $course_id");
}
$display_tools = $is_editor && !$is_in_tinymce;
if (!in_array($action, array('addlink', 'editlink', 'addcategory', 'editcategory', 'settings'))) {
    $countlinks = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM `link` WHERE course_id = ?d", $course_id)->cnt;

    if ($countlinks > 0) {
        $numberofzerocategory = count(Database::get()->queryArray("SELECT * FROM `link` WHERE course_id = ?d AND (category = 0 OR category IS NULL)", $course_id));
        // making the show none / show all links. Show none means urlview=0000 (number of zeros depending on the
        // number of categories). Show all means urlview=1111 (number of 1 depending on teh number of categories).
        $resultcategories = Database::get()->queryArray("SELECT * FROM `link_category` WHERE course_id = ?d ORDER BY `order`", $course_id);
        $aantalcategories = count($resultcategories);


        $tool_content .= "
            <div class='row'>
                <div class='col-sm-12'>
                <div class='table-responsive'>
                <table class='table-default nocategory-links'>";
        if ($numberofzerocategory !== 0) {
            $tool_content .= "<tr class='list-header'><th class='text-left'>$langNoCategory</th>";
            if ($display_tools) {
                $tool_content .= "<th class='text-center' style='width:109px;'>" . icon('fa-gears') . "</th>";
            }
            $tool_content .= "</tr>";
            showlinksofcategory(0);
        } else {
            $tool_content .= "<tr class='list-header'><th class='text-left list-header'>$langNoCategory</th>";
            if ($display_tools) {
                $tool_content .= "<th class='text-center' style='width:109px;'>" . icon('fa-gears') . "</th>";
            }
            $tool_content .= "</tr>";
            $tool_content .= "<tr><td class='text-left not_visible nocategory-link'> - $langNoLinkInCategory - </td>";
            if ($display_tools) {
                $tool_content .= "<td></td>";
            }
        }
        $tool_content .= "</tr></table></div></div></div>";

        $tool_content .= "
            <div class='row'>
                <div class='col-sm-12'>
                <div class='table-responsive'>
                <table class='table-default category-links'>";
        if ($aantalcategories > 0) {
           $tool_content .= "<tr class='list-header'><th>";

            $tool_content .= "$langCategorisedLinks&nbsp;";
            if (isset($urlview) and abs($urlview) == 0) {
                    $tool_content .= "&nbsp;&nbsp;<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;urlview=" . str_repeat('1', $aantalcategories) . $tinymce_params."'>" . icon('fa-folder', $showall)."</a>";
            } else {
                $tool_content .= "&nbsp;&nbsp;<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;urlview=" . str_repeat('0', $aantalcategories) . $tinymce_params."'>" .icon('fa-folder-open', $shownone)."</a>";
            }
            $tool_content .= "</th>";
            if ($display_tools) {
                $tool_content .= "<th class='text-center' style='width:109px;'>" . icon('fa-gears') . "</th>";
            }
            $tool_content .= "</tr>";
        } else {
            $tool_content .= "<tr><th>";

            $tool_content .= "$langCategorisedLinks&nbsp;";
            if (isset($urlview) and abs($urlview) == 0) {
                    $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;urlview=" . str_repeat('1', $aantalcategories) . $tinymce_params."'>&nbsp;&nbsp;" .icon('fa-folder', $showall)."</a>";
            } else {
                $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;urlview=" . str_repeat('0', $aantalcategories) . $tinymce_params."'>&nbsp;&nbsp;" .icon('fa-folder-open', $shownone)."</a>";
            }$tool_content .= "</th>";
            if ($display_tools) {
                $tool_content .= "<th class='text-center' style='width:109px;'>" . icon('fa-gears') . "</th>";
            }
            $tool_content .= "</tr>";
            $tool_content .= "<tr><td class='text-left not_visible nocategory-link'> - $langNoLinkCategories - </td>" .
                ($display_tools? '<td></td>': '') . "<tr>";
        }
        if ($urlview === '') {
            $urlview = str_repeat('0', $aantalcategories);
        }
        $i = 0;
        $catcounter = 0;
        foreach ($resultcategories as $myrow) {
            if (empty($urlview)) {
                // No $view set in the url, thus for each category link it should be all zeros except it's own
                $view = makedefaultviewcode($i);
            } else {
                $view = $urlview;
                $view[$i] = '1';
            }
            // if the $urlview has a 1 for this categorie, this means it is expanded and should be displayed as a
            // - instead of a +, the category is no longer clickable and all the links of this category are displayed
            $description = standard_text_escape($myrow->description);
            if ((isset($urlview[$i]) and $urlview[$i] == '1')) {
                $newurlview = $urlview;
                $newurlview[$i] = '0';
                $tool_content .= "<tr class='link-subcategory-title'><th class = 'text-left category-link'>".icon('fa-folder-open-o', $shownone)."&nbsp;
                            <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;urlview=$newurlview$tinymce_params' class='open-category'>" . q($myrow->name) . "</a>";
                if (!empty($description)) {
                    $tool_content .= "<br><span class='link-description'>$description</span></th>";
                } else {
                    $tool_content .= "</th>";
                }

                if ($display_tools) {
                    $tool_content .= "<td class='option-btn-cell'>";
                    showcategoryadmintools($myrow->id);
                    $tool_content .= "</td>";
                }

                $tool_content .= "</tr>";

                showlinksofcategory($myrow->id);
                if ($links_num == 1) {
                    $tool_content .= "<tr><td class='text-left not_visible nocategory-link'> - $langNoLinkInCategory - </td>" .
                        ($display_tools? '<td></td>': '') . "<tr>";
                }

            } else {
                $tool_content .= "<tr class='link-subcategory-title'><th class = 'text-left category-link'>".icon('fa-folder-o', $showall)
                    . "&nbsp;<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;urlview=";
                $tool_content .= is_array($view) ? implode('', $view) : $view;
                $tool_content .= $tinymce_params . "' class='open-category'>" . q($myrow->name) . "</a>";
                $description = standard_text_escape($myrow->description);
                if (!empty($description)) {
                    $tool_content .= "<br><span class='link-description'>$description</span</th>";
                } else {
                    $tool_content .= "</th>";
                }

                if ($display_tools) {
                    $tool_content .= "<td class='option-btn-cell'>";
                    showcategoryadmintools($myrow->id);
                    $tool_content .= "</td>";
                }

                $tool_content .= "</tr>";
            }
            $i++;
        }
        $tool_content .= "</table></div></div></div>";
    } else {   // no links
        $tool_content .= "<div class='alert alert-warning'>$langNoLinksExist</div>";
    }
    add_units_navigation(true);
}
draw($tool_content, $menuTypeID, null, $head_content);
