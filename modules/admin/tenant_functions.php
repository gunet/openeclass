<?php

function getTenantOption($options, $key, $default = '')
{
    return $options[$key] ?? $default;
}

/**
 * @brief Get the current tenant users, optionally filtered
 *
 * @param array $filters Supported keys: surname, givenname, username, am
 * @return array
 */
function getTenantUsers(array $filters = [
    'surname'   => '',
    'givenname' => '',
    'username'  => '',
    'am'        => '',
], $tenant_id = null)
{

    if ($tenant_id) {
        $tenant = getTenantById($tenant_id);
    } else {
        $tenant = getCurrentTenant();
    }

    if (!$tenant) {
        return [];
    }

    // Allowed filters for the query.
    $defaultFilters = [
        'surname'   => '',
        'givenname' => '',
        'username'  => '',
        'am'        => '',
    ];

    $filters = array_merge($defaultFilters, $filters);

    $tree = new Hierarchy();
    $tenantNodes = $tree->getTenantNodes($tenant_id);

    if (!$tenantNodes) {
        return [];
    }

    $tenantNodeIds = array_map(fn($node) => intval($node->id), $tenantNodes);
    $inClause = implode(', ', $tenantNodeIds);

    $query = "
        SELECT DISTINCT u.id, u.surname, u.givenname, u.email, u.username, u.am
        FROM user u
        JOIN user_department ud ON ud.user = u.id
        WHERE ud.department IN ($inClause)
    ";

    $values = [];

    foreach ($filters as $field => $value) {
        if (!empty($value)) {
            $query .= " AND LOWER(u.$field) LIKE LOWER(?s)";
            $values[] = '%' . $value . '%';
        }
    }

    return Database::get()->queryArray($query, ...$values);
}

/**
 * @brief Get the current tenant admins
 *
 * @param int $tenant_id
 * @return array
 */
function getTenantAdmins($tenant_id = null)
{
    $tenant = $tenant_id ? getTenantById($tenant_id) : getCurrentTenant();

    if (!$tenant) {
        return [];
    }

    $tree = new Hierarchy();
    $tenantNodes = $tree->getTenantNodes($tenant->id);
    $tenantNodesIds = implode(",", array_map(fn($n) => intval($n->id), $tenantNodes));
    $privilege = DEPARTMENTMANAGE_USER;

    $query = "
        SELECT u.id, u.surname, u.givenname, u.email, u.username, u.am
        FROM user u
        JOIN admin a ON a.user_id = u.id
        WHERE 1=1 
            AND a.department_id IN ($tenantNodesIds)
            AND a.privilege = $privilege
    ";

    $admins = Database::get()->queryArray($query);

    return $admins;
}

/**
 * @brief Get the current tenant, if any
 */
function getCurrentTenant()
{
    global $require_current_course, $course_id, $uid;

    // If in course, get tenant of course
    if ($require_current_course and $course_id) {
        return getCourseTenant($course_id);
    }

    // If no user is logged in, there is no tenant
    if (!$uid) {
        return null;
    }

    // Get the current user's tenant and cache it in the session if found
    if (!isset($_SESSION['current_user_tenant'])) {
        $_SESSION['current_user_tenant'] = getUserTenant($uid);
    }

    return $_SESSION['current_user_tenant'];
}

/**
 * @brief Get specific tenant via id
 */
function getTenantById($tenant_id = null)
{
    if (!$tenant_id) {
        return null;
    }

    return Database::get()->querySingle(
        'SELECT tenant.*, hierarchy.lft, hierarchy.rgt
         FROM tenant
         JOIN hierarchy ON hierarchy.id = tenant.department_id
         WHERE tenant.id = ?d',
        $tenant_id
    );
}

/**
 * @brief Get the tenant a course belongs to
 */
function getCourseTenant($course_id)
{
    $tenant = Database::get()->querySingle(
        'SELECT tenant.id, hierarchy.id AS hierarchy_id, hierarchy.lft, hierarchy.rgt
       FROM tenant JOIN hierarchy ON tenant.department_id = hierarchy.id,
            course_department JOIN hierarchy AS course_hierarchy ON department = course_hierarchy.id
       WHERE course = ?d AND course_hierarchy.lft BETWEEN hierarchy.lft AND hierarchy.rgt',
        $course_id
    );
    if ($tenant) {
        return $tenant;
    } else {
        return null;
    }
}

/**
 * @brief Get the tenant a user belongs to
 */
function getUserTenant($user_id)
{
    $tenant = Database::get()->querySingle(
        'SELECT tenant.id, tenant.options, hierarchy.id AS hierarchy_id, hierarchy.lft, hierarchy.rgt
       FROM tenant JOIN hierarchy ON tenant.department_id = hierarchy.id,
            user_department JOIN hierarchy AS user_hierarchy ON department = user_hierarchy.id
       WHERE user = ?d AND user_hierarchy.lft BETWEEN hierarchy.lft AND hierarchy.rgt',
        $user_id
    );
    if ($tenant) {
        return $tenant;
    } else {
        return null;
    }
}
