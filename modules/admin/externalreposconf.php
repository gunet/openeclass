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
 * @file externalreposconf.php
 * @brief Admin configuration page for external repositories
 */

$require_admin = true;
$require_help = true;
$helpTopic = 'external_repositories';

require_once '../../include/baseTheme.php';
require_once 'modules/admin/extconfig/externals.php';
require_once 'modules/admin/extconfig/externalreposapp.php';

$toolName = $langExternalRepos ?? 'External Repositories';
$navigation[] = ['url' => 'index.php', 'name' => $langAdmin];
$navigation[] = ['url' => 'extapp.php', 'name' => $langExtAppConfig ?? 'External Apps'];

$data = [];
$data['menuTypeID'] = 3;

// Handle AJAX requests
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    
    if (isset($_POST['action'])) {
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) {
            echo json_encode(['success' => false, 'message' => $langCSRF ?? 'Invalid token']);
            exit;
        }
        
        switch ($_POST['action']) {
            case 'toggle':
                $id = intval($_POST['id']);
                $enabled = intval($_POST['enabled']);
                $result = ExternalReposApp::toggleRepository($id, (bool)$enabled);
                echo json_encode(['success' => $result]);
                exit;
                
            case 'delete':
                $id = intval($_POST['id']);
                $result = ExternalReposApp::deleteRepository($id);
                echo json_encode(['success' => $result]);
                exit;
                
            case 'test':
                $id = intval($_POST['id']);
                $repo = ExternalReposApp::getRepository($id);
                if ($repo) {
                    require_once 'include/lib/externalrepos/ExternalRepoFactory.php';
                    $service = ExternalRepoFactory::create($repo);
                    if ($service) {
                        $result = $service->testConnection();
                        echo json_encode($result);
                    } else {
                        echo json_encode(['success' => false, 'message' => $langUnsupportedRepoType ?? 'Unsupported repository type']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => $langRepoNotFound ?? 'Repository not found']);
                }
                exit;
        }
    }
    
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) {
        Session::flash('message', $langCSRF ?? 'Invalid security token');
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page('modules/admin/externalreposconf.php');
    }
    
    if (isset($_POST['save_repo'])) {
        $repoData = [
            'id' => isset($_POST['repo_id']) ? intval($_POST['repo_id']) : null,
            'name' => trim($_POST['repo_name']),
            'type' => $_POST['repo_type'],
            'base_url' => trim($_POST['base_url'] ?? ''),
            'api_key' => trim($_POST['api_key'] ?? ''),
            'auth_type' => $_POST['auth_type'] ?? 'none',
            'enabled' => isset($_POST['enabled']) ? 1 : 0,
            'config' => null
        ];
        
        // Validate required fields
        if (empty($repoData['name'])) {
            Session::flash('message', $langEmptyRepoName ?? 'Repository name is required');
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page('modules/admin/externalreposconf.php' . ($repoData['id'] ? '?edit=' . $repoData['id'] : '?add=1'));
        }
        
        // Validate type
        $validTypes = array_keys(ExternalReposApp::getRepositoryTypes());
        if (!in_array($repoData['type'], $validTypes)) {
            Session::flash('message', $langInvalidRepoType ?? 'Invalid repository type');
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page('modules/admin/externalreposconf.php' . ($repoData['id'] ? '?edit=' . $repoData['id'] : '?add=1'));
        }
        
        // Handle additional config as JSON
        if (!empty($_POST['additional_config'])) {
            $repoData['config'] = $_POST['additional_config'];
        }

        // DSpace: merge the metadata profile (set via its dropdown) into the config JSON.
        if ($repoData['type'] === 'dspace') {
            $configData = [];
            if (!empty($repoData['config'])) {
                $decoded = json_decode($repoData['config'], true);
                if (is_array($decoded)) {
                    $configData = $decoded;
                }
            }
            $profile = $_POST['metadata_profile'] ?? 'dublin_core';
            $configData['metadata_profile'] = in_array($profile, ['dublin_core', 'lom'], true)
                ? $profile : 'dublin_core';
            $repoData['config'] = json_encode($configData);
        }

        $result = ExternalReposApp::saveRepository($repoData);
        
        if ($result) {
            Session::flash('message', $langRepoSaved ?? 'Repository saved successfully');
            Session::flash('alert-class', 'alert-success');
        } else {
            Session::flash('message', $langRepoSaveError ?? 'Error saving repository');
            Session::flash('alert-class', 'alert-danger');
        }
        
        redirect_to_home_page('modules/admin/externalreposconf.php');
    }
}

// Determine which view to show
if (isset($_GET['add'])) {
    // Add new repository form
    $pageName = $langAddExternalRepo ?? 'Add External Repository';
    $data['action'] = 'add';
    $data['repository'] = null;
    $data['repositoryTypes'] = ExternalReposApp::getRepositoryTypes();
    
    view('admin.other.externalrepos_form', $data);
    
} elseif (isset($_GET['edit'])) {
    // Edit existing repository
    $id = intval($_GET['edit']);
    $repository = ExternalReposApp::getRepository($id);
    
    if (!$repository) {
        Session::flash('message', $langRepoNotFound ?? 'Repository not found');
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page('modules/admin/externalreposconf.php');
    }
    
    $pageName = $langEditExternalRepo ?? 'Edit External Repository';
    $data['action'] = 'edit';
    $data['repository'] = $repository;
    $data['repositoryTypes'] = ExternalReposApp::getRepositoryTypes();
    
    view('admin.other.externalrepos_form', $data);
    
} else {
    // List all repositories
    $pageName = $langExternalRepos ?? 'External Repositories';
    $data['repositories'] = ExternalReposApp::getRepositories();
    $data['repositoryTypes'] = ExternalReposApp::getRepositoryTypes();
    
    view('admin.other.externalrepos_index', $data);
}


