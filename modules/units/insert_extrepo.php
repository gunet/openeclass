<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

/**
 * @file insert_extrepo.php
 * @brief Insert external repository resources into course units
 */

require_once 'include/lib/externalrepos/ExternalRepoFactory.php';
require_once 'modules/admin/extconfig/externals.php';
require_once 'modules/admin/extconfig/externalreposapp.php';

/**
 * Display the external repository search interface
 */
function display_extrepo_search() {
    global $tool_content, $head_content, $course_code, $course_id, $urlAppend, $id,
           $langSearch, $langSearchResults, $langNoResults, $langAdd, $langCancel,
           $langTitle, $langDescription, $langType, $langSource,
           $langExternalRepos, $langSelectRepository, $langSearchPlaceholder,
           $langNoExternalRepos, $langLoading;
    
    // Get enabled repositories
    $repositories = ExternalReposApp::getRepositories(true);
    
    // Prepare data for view
    $data = [
        'course_code' => $course_code,
        'unit_id' => $id,
        'repositories' => $repositories,
        'repositoryTypes' => ExternalReposApp::getRepositoryTypes(),
        'backUrl' => $urlAppend . "modules/units/index.php?course=$course_code&id=$id"
    ];
    
    // Load JavaScript
    $head_content .= "
    <script>
    var extRepoConfig = {
        searchUrl: '" . $urlAppend . "modules/units/extrepo_search.php',
        courseCode: '$course_code',
        unitId: $id,
        csrfToken: '" . js_escape($_SESSION['csrf_token'] ?? '') . "',
        lang: {
            loading: '" . js_escape($langLoading ?? 'Loading...') . "',
            noResults: '" . js_escape($langNoResults ?? 'No results found') . "',
            error: '" . js_escape($GLOBALS['langError'] ?? 'An error occurred') . "',
            add: '" . js_escape($langAdd) . "'
        }
    };
    </script>
    ";
    
    // Render using Blade template
    view('modules.units.insert_extrepo', $data);
}

/**
 * Insert selected external resources into the unit
 * 
 * @param int $unit_id Unit ID
 */
function insert_extrepo($unit_id) {
    global $course_code, $course_id;
    
    require_once 'modules/search/classes/ConstantsUtil.php';
    require_once 'modules/search/classes/SearchEngineFactory.php';
    require_once 'modules/course_metadata/CourseXML.php';
    
    if (!isset($_POST['extrepo']) || !is_array($_POST['extrepo']) || empty($_POST['extrepo'])) {
        Session::flash('message', $GLOBALS['langNoResourceSelected'] ?? 'No resource selected');
        Session::flash('alert-class', 'alert-warning');
        redirect_to_home_page("modules/units/index.php?course=$course_code&id=$unit_id");
    }
    
    $searchEngine = SearchEngineFactory::create();
    $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM unit_resources WHERE unit_id = ?d", $unit_id)->maxorder ?? 0;
    
    $insertedCount = 0;
    
    foreach ($_POST['extrepo'] as $resourceData) {
        // Decode the JSON resource data
        $resource = json_decode($resourceData, true);
        if (!$resource) {
            continue;
        }
        
        $order++;
        
        // First, save the external resource to the external_resource table
        $extResourceId = save_external_resource($resource, $course_id);
        
        if (!$extResourceId) {
            continue;
        }
        
        // Then, add it to unit_resources
        if (isset($_SESSION['fc_type']) && isset($_SESSION['act_name'])) {
            $q = Database::get()->query("INSERT INTO unit_resources SET 
                unit_id = ?d, 
                type = 'extrepo',
                title = ?s, 
                comments = ?s,
                visible = 1, 
                `order` = ?d,
                `date` = " . DBHelper::timeAfter() . ", 
                res_id = ?d,
                fc_type = ?d,
                activity_title = ?s,
                activity_id = ?s",
                $unit_id, 
                $resource['title'], 
                $resource['description'] ?? '',
                $order, 
                $extResourceId,
                $_SESSION['fc_type'],
                $_SESSION['act_name'],
                $_SESSION['act_id']
            );
        } else {
            $q = Database::get()->query("INSERT INTO unit_resources SET 
                unit_id = ?d, 
                type = 'extrepo',
                title = ?s, 
                comments = ?s,
                visible = 1, 
                `order` = ?d,
                `date` = " . DBHelper::timeAfter() . ", 
                res_id = ?d",
                $unit_id, 
                $resource['title'], 
                $resource['description'] ?? '',
                $order, 
                $extResourceId
            );
        }
        
        if ($q) {
            $uresId = $q->lastInsertID;
            $searchEngine->indexResource(ConstantsUtil::REQUEST_STORE, ConstantsUtil::RESOURCE_UNITRESOURCE, $uresId);
            $insertedCount++;
        }
    }
    
    // Update course index
    $searchEngine->indexResource(ConstantsUtil::REQUEST_STORE, ConstantsUtil::RESOURCE_COURSE, $course_id);
    CourseXMLElement::refreshCourse($course_id, $course_code);
    
    // Clear session variables
    unset($_SESSION['fc_type'], $_SESSION['act_name'], $_SESSION['act_id']);
    
    if ($insertedCount > 0) {
        Session::flash('message', sprintf($GLOBALS['langResourcesAdded'] ?? '%d resource(s) added', $insertedCount));
        Session::flash('alert-class', 'alert-success');
    } else {
        Session::flash('message', $GLOBALS['langNoResourceAdded'] ?? 'No resources were added');
        Session::flash('alert-class', 'alert-warning');
    }
    
    redirect_to_home_page("modules/units/index.php?course=$course_code&id=$unit_id");
}

/**
 * Save an external resource to the database
 * 
 * @param array $resource Resource data
 * @param int $course_id Course ID
 * @return int|false Resource ID or false on failure
 */
function save_external_resource(array $resource, int $course_id) {
    // Check if this resource already exists for this course
    $existing = Database::get()->querySingle(
        "SELECT id FROM external_resource 
         WHERE course_id = ?d 
         AND repository_id = ?d 
         AND external_id = ?s",
        $course_id,
        $resource['repository_id'] ?? 0,
        $resource['id'] ?? ''
    );
    
    if ($existing) {
        return $existing->id;
    }
    
    // Insert new external resource
    $result = Database::get()->query(
        "INSERT INTO external_resource 
         (course_id, repository_id, external_id, title, description, url, resource_type, thumbnail_url, metadata, created)
         VALUES (?d, ?d, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?t)",
        $course_id,
        $resource['repository_id'] ?? 0,
        $resource['id'] ?? '',
        $resource['title'] ?? 'Untitled',
        $resource['description'] ?? null,
        $resource['url'] ?? '',
        $resource['type'] ?? 'document',
        $resource['thumbnail'] ?? null,
        isset($resource['metadata']) ? json_encode($resource['metadata']) : null,
        date('Y-m-d H:i:s')
    );
    
    return $result ? $result->lastInsertID : false;
}

