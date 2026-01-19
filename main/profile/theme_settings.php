<?php
/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2018  Greek Universities Network - GUnet
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
 * @author Marios Giannopoulos
 * @file theme_settings.php
 * @abstract Theme Customization Page for Users
 */

$require_login = true;
include '../../include/baseTheme.php';

// Check if user theme customization is enabled by admin
if (!get_config('enable_user_theme_customization', 0)) {
    Session::flash('message', $langUserThemeCustomizationDisabled);
    Session::flash('alert-class', 'alert-warning');
    redirect_to_home_page('main/profile/display_profile.php');
    exit;
}

$toolName = $langThemeSettings;
$navigation[] = array('url' => 'display_profile.php', 'name' => $langMyProfile);


// Save theme selection to cookie
if (isset($_POST['submit_theme'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) {
        csrf_token_error();
    }
    
    $selected_id = isset($_POST['selected_theme_id']) ? intval($_POST['selected_theme_id']) : 0;

    if ($selected_id > 0) {
        // Verify theme exists and is version 4 (available for users)
        $exists = Database::get()->querySingle("SELECT id FROM theme_options WHERE id = ?d AND version = 4", $selected_id);
        
        // Also verify theme is in admin's allowed list
        $user_selectable_themes_str = get_config('user_selectable_themes', '');
        $allowed = true;
        if (!empty($user_selectable_themes_str)) {
            $allowed_theme_ids = array_map('intval', explode(',', $user_selectable_themes_str));
            $allowed_theme_ids = array_filter($allowed_theme_ids);
            if (!empty($allowed_theme_ids) && !in_array($selected_id, $allowed_theme_ids)) {
                $allowed = false;
            }
        }
        
        if ($exists && $allowed) {
            setcookie('user_theme_selection', $selected_id, time() + (86400 * 365), $urlAppend);
            Session::flash('message', $langThemeSaved);
            Session::flash('alert-class', 'alert-success');
        } else {
            Session::flash('message', 'Invalid theme selected.');
            Session::flash('alert-class', 'alert-danger');
        }
    } else {
        setcookie('user_theme_selection', '', time() - 3600, $urlAppend);
        Session::flash('message', $langThemeSaved . ' (Default)');
        Session::flash('alert-class', 'alert-success');
    }
    
    // Clear preview session when saving
    if (isset($_SESSION['user_theme_preview_id'])) {
        unset($_SESSION['user_theme_preview_id']);
    }
    
    // Force CSS regeneration with new saved theme
    if (isset($_SESSION['theme_changed'])) {
        unset($_SESSION['theme_changed']);
    }
    $_SESSION['theme_changed'] = true;
    
    redirect_to_home_page('main/profile/theme_settings.php');
    exit;
}

// Preview theme selection (temporary, stored in session)
if (isset($_POST['submit_preview'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) {
        csrf_token_error();
    }
    
    $selected_id = isset($_POST['selected_theme_id']) ? intval($_POST['selected_theme_id']) : 0;
    
    // If theme ID > 0, verify it exists and is version 4
    if ($selected_id > 0) {
        $exists = Database::get()->querySingle("SELECT id FROM theme_options WHERE id = ?d AND version = 4", $selected_id);
        if (!$exists) {
            Session::flash('message', 'Invalid theme selected.');
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page('main/profile/theme_settings.php');
            exit;
        }
    }
    
    // Store preview in session for temporary display
    $_SESSION['user_theme_preview_id'] = $selected_id;
    
    // Force CSS regeneration for preview
    $_SESSION['theme_changed'] = true;
    
    Session::flash('message', $langPreviewState); // "You are in a preview state of theme"
    Session::flash('alert-class', 'alert-warning');
    
    // Stay on the same page to see preview immediately
    redirect_to_home_page('main/profile/theme_settings.php');
    exit;
}

// Cancel preview and return to saved theme
if (isset($_POST['cancel_preview'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) {
        csrf_token_error();
    }
    
    unset($_SESSION['user_theme_preview_id']);
    // Clear theme_changed flag if it was set
    if (isset($_SESSION['theme_changed'])) {
        unset($_SESSION['theme_changed']);
    }
    redirect_to_home_page('main/profile/theme_settings.php');
    exit;
}

// Fetch available themes (version 4 = user-selectable)
$all_themes = Database::get()->queryArray("SELECT * FROM theme_options WHERE version = 4 ORDER BY name ASC");

// Filter themes based on admin selection
$user_selectable_themes_str = get_config('user_selectable_themes', '');
if (!empty($user_selectable_themes_str)) {
    $allowed_theme_ids = array_map('intval', explode(',', $user_selectable_themes_str));
    $allowed_theme_ids = array_filter($allowed_theme_ids);
    
    if (!empty($allowed_theme_ids)) {
        $filtered_themes = array();
        foreach ($all_themes as $theme_item) {
            if (in_array(intval($theme_item->id), $allowed_theme_ids)) {
                $filtered_themes[] = $theme_item;
            }
        }
        $all_themes = $filtered_themes;
    } else {
        // If admin enabled feature but selected no themes, show none
        $all_themes = array();
    }
} else {
    $all_themes = array();
}

// Determine current selection: preview (session) takes priority over saved (cookie)
$preview_mode = isset($_SESSION['user_theme_preview_id']);
$preview_id = $preview_mode ? intval($_SESSION['user_theme_preview_id']) : null;
$cookie_id = isset($_COOKIE['user_theme_selection']) ? intval($_COOKIE['user_theme_selection']) : 0;
$current_selection = $preview_mode ? $preview_id : $cookie_id;


$action_bar = action_bar(array(
    array('title' => $langBack,
          'url' => 'display_profile.php',
          'icon' => 'fa-reply',
          'level' => 'primary-label')
));

$theme_cards = "";

// Default Card
$is_def_checked = ($current_selection == 0) ? 'checked' : '';
$card_class = ($current_selection == 0) ? 'border-primary bg-light' : '';

$theme_cards .= "
<div class='col-xl-4 col-md-6 mb-4 theme-item'>
    <div class='card h-100 theme-card $card_class' style='cursor:pointer;' onclick='selectTheme(0)'>
        <div class='card-body text-center d-flex flex-column'>
            <div class='theme-preview mb-3 d-flex align-items-center justify-content-center rounded' style='height:180px; background:#f5f5f5; border:1px solid #ddd;'>
                <div class='text-muted'>
                    <i class='fa-solid fa-desktop fa-3x mb-2'></i><br>
                    $langDefaultThemeSettings
                </div>
            </div>
            <h5 class='card-title mt-auto'>$langDefaultThemeSettings</h5>
            <div class='form-check d-inline-block mt-2'>
                <input class='form-check-input' type='radio' name='selected_theme_id' id='theme_def' value='0' $is_def_checked>
                <label class='form-check-label' for='theme_def'>$langSelect</label>
            </div>
        </div>
    </div>
</div>";

// Loop Themes
foreach ($all_themes as $th) {
    $styles = unserialize($th->styles);
    $t_id = $th->id;
    $t_name = htmlspecialchars($th->name);
    
    $is_checked = ($current_selection == $t_id) ? 'checked' : '';
    $border_class = ($current_selection == $t_id) ? 'border-primary bg-light' : '';

    // Image
    $preview_img = "";
    $img_path = "";
    if (!empty($styles['loginImg'])) {
        $img_path = $styles['loginImg'];
    } elseif (!empty($styles['imageUpload'])) {
        $img_path = $styles['imageUpload'];
    }

    if (!empty($img_path)) {
        $full_url = "{$urlAppend}courses/theme_data/{$t_id}/{$img_path}";
        $preview_img = "<img src='$full_url' class='img-fluid rounded' style='max-height:160px; object-fit:contain;' alt='$t_name'>";
    } else {
        $bg_color = isset($styles['bgColor']) ? $styles['bgColor'] : '#ddd';
        $preview_img = "<div class='d-flex align-items-center justify-content-center rounded' style='height:160px; background:$bg_color; width:100%;'>
                            <span class='badge bg-dark'>No Preview</span>
                        </div>";
    }

    $theme_cards .= "
    <div class='col-xl-4 col-md-6 mb-4 theme-item'>
        <div class='card h-100 theme-card $border_class' style='cursor:pointer;' onclick='selectTheme($t_id)'>
            <div class='card-body text-center d-flex flex-column'>
                <div class='theme-preview mb-3 d-flex align-items-center justify-content-center' style='height:180px; background:#fff; border:1px solid #eee;'>
                    $preview_img
                </div>
                <h5 class='card-title mt-auto'>$t_name</h5>
                <div class='form-check d-inline-block mt-2'>
                    <input class='form-check-input' type='radio' name='selected_theme_id' id='theme_$t_id' value='$t_id' $is_checked>
                    <label class='form-check-label' for='theme_$t_id'>$langSelect</label>
                </div>
            </div>
        </div>
    </div>";
}

// Check if any themes are available
if (empty($all_themes)) {
    $content = "
    <div class='row'>
        <div class='col-12'>
            $action_bar
            
            <div class='form-wrapper form-edit rounded'>
                <div class='alert alert-warning'>
                    <i class='fa-solid fa-triangle-exclamation me-2'></i>
                    <span>$langNoThemesAvailable</span>
                </div>
            </div>
        </div>
    </div>";
} else {
    $content = "
<style>
    .pagination-custom .page-link { color: #333; border: 1px solid #dee2e6; margin: 0 4px; border-radius: 4px; cursor: pointer; }
    .pagination-custom .page-item.active .page-link { background-color: #0d6efd; border-color: #0d6efd; color: white; }
    .pagination-custom .page-item.disabled .page-link { color: #6c757d; pointer-events: none; background-color: #fff; border-color: #dee2e6; }
    .pagination-custom .page-item:first-child .page-link { margin-left: 0; }
    .pagination-custom .page-item:last-child .page-link { margin-right: 0; }
</style>

<div class='row'>
    <div class='col-12'>
        $action_bar
        
        <div class='form-wrapper form-edit rounded'>
            
            <div class='alert alert-info'>
                <span>Επιλέξτε ένα θέμα και πατήστε <strong>$langSee</strong> για δοκιμή ή <strong>$langSave</strong> για εφαρμογή.</span>
            </div>
            
            <form method='post' action='$_SERVER[PHP_SELF]'>
                ". generate_csrf_token_form_field() ."
                <div class='row' id='themes-container'>
                    $theme_cards
                </div>

                <!-- Pagination -->
                <div class='row mt-3'>
                    <div class='col-12 d-flex justify-content-center'>
                        <nav aria-label='Page navigation'>
                            <ul class='pagination pagination-custom' id='pagination-list'></ul>
                        </nav>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class='row mt-4 border-top pt-3'>
                    <div class='col-12 text-center'>
                        <div class='d-flex justify-content-center gap-3'>
                            <button type='submit' name='submit_preview' class='btn btn-default btn-lg d-flex align-items-center'>
                                <i class='fa-solid fa-eye me-2'></i> $langSee
                            </button>
                            
                            <button type='submit' name='submit_theme' class='btn successAdminBtn btn-lg d-flex align-items-center'>
                                <i class='fa-solid fa-save me-2'></i> $langSave
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Handle theme card selection
function selectTheme(id) {
    document.querySelectorAll('input[name=\"selected_theme_id\"]').forEach(el => el.checked = false);
    document.querySelectorAll('.theme-card').forEach(el => el.classList.remove('border-primary', 'bg-light'));
    
    let radio;
    if (id === 0) {
        radio = document.getElementById('theme_def');
    } else {
        radio = document.getElementById('theme_' + id);
    }
    
    if(radio) {
        radio.checked = true;
        radio.closest('.theme-card').classList.add('border-primary', 'bg-light');
    }
}

// Pagination: Show page containing currently selected theme on load
document.addEventListener('DOMContentLoaded', function() {
    const items = document.querySelectorAll('.theme-item');
    const paginationList = document.getElementById('pagination-list');
    let currentPage = 1;
    let itemsPerPage = 6;
    const selectedThemeId = ". json_encode($current_selection) ."; // PHP variable passed to JS

    function updateItemsPerPage() {
        const width = window.innerWidth;
        if (width >= 1200) { itemsPerPage = 6; } else { itemsPerPage = 4; }
    }

    function showPage(page) {
        const totalPages = Math.ceil(items.length / itemsPerPage);
        if (page < 1) page = 1;
        if (page > totalPages) page = totalPages;
        currentPage = page;
        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;

        items.forEach((item, index) => {
            if (index >= start && index < end) { item.style.display = 'block'; } 
            else { item.style.display = 'none'; }
        });
        renderPagination(totalPages);
    }

    function renderPagination(totalPages) {
        paginationList.innerHTML = '';
        if (totalPages <= 1) return;

        const prevLi = document.createElement('li');
        prevLi.className = `page-item \${currentPage === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class='page-link' href='javascript:void(0)'><i class='fa-solid fa-chevron-left'></i></a>`;
        prevLi.onclick = () => { if(currentPage > 1) showPage(currentPage - 1); };
        paginationList.appendChild(prevLi);

        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement('li');
            li.className = `page-item \${i === currentPage ? 'active' : ''}`;
            li.innerHTML = `<a class='page-link' href='javascript:void(0)'>\${i}</a>`;
            li.onclick = () => showPage(i);
            paginationList.appendChild(li);
        }

        const nextLi = document.createElement('li');
        nextLi.className = `page-item \${currentPage === totalPages ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class='page-link' href='javascript:void(0)'><i class='fa-solid fa-chevron-right'></i></a>`;
        nextLi.onclick = () => { if(currentPage < totalPages) showPage(currentPage + 1); };
        paginationList.appendChild(nextLi);
    }

    // Find which pagination page contains the selected theme
    function findPageForSelectedTheme() {
        let page = 1;
        if (selectedThemeId !== null && selectedThemeId !== undefined) {
            items.forEach((item, index) => {
                const radio = item.querySelector('input[type=\"radio\"][value=\"' + selectedThemeId + '\"]');
                if (radio) {
                    page = Math.floor(index / itemsPerPage) + 1;
                }
            });
        }
        return page;
    }

    updateItemsPerPage();
    
    // Display page containing the currently selected theme
    const initialPage = findPageForSelectedTheme();
    showPage(initialPage);

    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            const oldLimit = itemsPerPage;
            updateItemsPerPage();
            // Recalculate page after resize to keep selected theme visible
            if (oldLimit !== itemsPerPage) {
                const newPage = findPageForSelectedTheme();
                showPage(newPage);
            }
        }, 100);
    });
});
</script>
";
    }

draw($content, $toolName, null);
?>