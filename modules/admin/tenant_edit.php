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

$require_admin = true;
$require_help = true;
$helpTopic = 'tenants';

require_once '../../include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';

$tree = new Hierarchy();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) {
        csrf_token_error();
    }

    $v = new Valitron\Validator($_POST);
    $v->rule('required', ['name']);
    if (!$v->validate()) {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page('modules/admin/tenant_edit.php');
    } else {
        if ($_POST['id']) {
            Database::get()->query(
                'UPDATE tenant
                SET name = ?s, description = ?s, updated_at = NOW()
                WHERE id = ?d',
                $_POST['name'],
                purify($_POST['description']),
                $_POST['id']
            );
            Session::Messages($langTenantUpdated, 'alert-success');
        } else {
            if ($_POST['tenant_category'] == '1') {
                $department_id = $_POST['category'];
            } else {
                $tree->addNode($_POST['name'], '', 0, '', 1, 1, 0, NODE_SUBSCRIBED, '');
                // Temporary workaround - for unknown reasons, adding a node using the stored procedure
                // doesn't return the lastInsertId
                $department_id = Database::get()->querySingle('SELECT MAX(id) AS id FROM hierarchy')->id;
            }
            if(!$department_id){
                //Throw early when no department exists
                Session::Messages($langTenantCategoryNotFound, 'alert-danger');
                redirect_to_home_page('modules/admin/tenant_edit.php');
            }
            Database::get()->query(
                'INSERT INTO tenant
                (name, description, department_id, options, created_at, updated_at)
                VALUES (?s, ?s, ?d, ?s, NOW(), NOW())',
                $_POST['name'],
                purify($_POST['description']),
                $department_id,
                ''
            );

            $adminUsername = $_POST['admin_id'][0] ?? null;

            if (!empty($adminUsername)) {

                $adminUser = Database::get()->querySingle(
                    'SELECT id FROM user WHERE username = ?s',
                    $adminUsername
                );

                if (!$adminUser) {
                    Session::Messages($langTenantAdminNotFound, 'alert-danger');
                    redirect_to_home_page('modules/admin/tenant_edit.php');
                }

                Database::get()->query(
                    'INSERT INTO admin (user_id, privilege, department_id)
                 VALUES (?d, ?d, ?d)',
                    $adminUser->id,
                    DEPARTMENTMANAGE_USER,
                    $department_id
                );
            }

            Session::Messages($langTenantAdded, 'alert-success');
        }
        redirect_to_home_page('modules/admin/tenants.php');
    }
}

$toolName = $langAdmin;
$pageName = $langAddTenant;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'tenants.php', 'name' => $langTenants);

// javascript
load_js('jstree3'); 
load_js('bootstrap-datetimepicker');

if (isset($_GET['id'])) {
    $data['tenant'] = Database::get()->querySingle('SELECT * FROM tenant WHERE id = ?d', $_GET['id']);
    $data['department_name'] = $tree->getFullPath($data['tenant']->department_id);
    $data['categories'] = [];
} else { // user account request
    load_js('select2');
    $data['tenant'] = null;
    $tenant_departments = array_map(function ($tenant) {
        return $tenant->department_id;
    }, Database::get()->queryArray('SELECT department_id FROM tenant'));
    $data['categories'] = array_filter(
        $tree->buildRootsArray(),
        function ($node) use ($tenant_departments) {
            return !in_array($node->id, $tenant_departments);
        }
    );
}

$data['description_editor'] = rich_text_editor('description', 4, 20, $data['tenant']->description ?? '');


view('admin.other.tenants.edit', $data);
