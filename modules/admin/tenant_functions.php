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
], $tenant_id = null, $user_type = null)
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
        SELECT DISTINCT u.id, u.surname, u.givenname, u.email, u.username, u.am , u.verified_mail
        FROM user u
        JOIN user_department ud ON ud.user = u.id
        WHERE ud.department IN ($inClause)
    ";

    $values = [];

    if (!is_null($user_type)) {
        if (!in_array($user_type, [USER_TEACHER, USER_STUDENT, USER_GUEST])) {
            return [];
        }
        $query .= " AND u.status = ?d";
        $values[] = $user_type;
    }


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
 * @brief Get themes for a specific tenant
 * @param int|null $tenant_id Optional tenant ID (defaults to current tenant)
 * @return array Array of theme option records, or empty array if tenant not found
 */
function getTenantThemes($tenant_id = null)
{
    if ($tenant_id) {
        $tenant = getTenantById($tenant_id);
    } else {
        $tenant = getCurrentTenant();
    }

    if (!$tenant) {
        return [];
    }

    return Database::get()->queryArray(
        'SELECT *
         FROM theme_options
         WHERE tenant_id = ?d',
        $tenant->id
    );
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
        'SELECT tenant.id, tenant.options, hierarchy.id AS hierarchy_id, hierarchy.lft, hierarchy.rgt, tenant.theme_id
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
        'SELECT tenant.id, tenant.options, hierarchy.id AS hierarchy_id, hierarchy.lft, hierarchy.rgt, tenant.theme_id
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


/**
 * @brief Get the current tenant courses, optionally filtered by visibility
 *
 * @param int|null $course_type COURSE_INACTIVE, COURSE_OPEN, COURSE_REGISTRATION, COURSE_CLOSED
 * @param int|null $tenant_id
 * @return array
 */
function getTenantCourses($course_type = null, $tenant_id = null)
{
    // Resolve tenant
    if ($tenant_id) {
        $tenant = getTenantById($tenant_id);
    } else {
        $tenant = getCurrentTenant();
    }

    if (!$tenant) {
        return [];
    }

    // Get tenant hierarchy nodes
    $tree = new Hierarchy();
    $tenantNodes = $tree->getTenantNodes($tenant->id);

    if (!$tenantNodes) {
        return [];
    }

    $tenantNodeIds = array_map(fn($node) => intval($node->id), $tenantNodes);
    $inClause = implode(', ', $tenantNodeIds);

    $query = "
        SELECT DISTINCT c.id, c.code, c.title, c.visible
        FROM course c
        JOIN course_department cd ON cd.course = c.id
        WHERE cd.department IN ($inClause)
    ";

    $values = [];

    if (!is_null($course_type)) {
        if (!in_array($course_type, [
            COURSE_INACTIVE,
            COURSE_OPEN,
            COURSE_REGISTRATION,
            COURSE_CLOSED
        ])) {
            return [];
        }

        $query .= " AND c.visible = ?d";
        $values[] = $course_type;
    }

    return Database::get()->queryArray($query, ...$values);
}

/**
 * @brief Retrieves failed login attempts for tenants within a specified date range.
 *
 * This function fetches all failed login attempts from the log table for the users
 * of a specific tenant (or all tenants if no tenant ID is provided) between
 * the given start and end dates.
 *
 * @param string $date_start Start date for filtering log entries.
 * @param string $date_end End date for filtering log entries.
 * @param int|null $tenantId Optional tenant ID to filter users by tenant.
 * @return array List of failed login attempts with timestamps, IPs, and details.
 */

function getTenantFailureLoginData(
    string $date_start,
    string $date_end,
    ?int $tenantId = null
) {
    if (empty($tenantId)) {
        $users = getTenantUsers();
    } else {
        $users =  getTenantUsers([], $tenantId);
    }

    $tenantUsers = getTenantUsers([], $tenantId);
    if (empty($tenantUsers)) {
        return [];
    }

    $usernames = array_map(fn($u) => $u->username, $users);

    $rows = Database::get()->queryArray("
        SELECT ts, ip, details
        FROM log
        WHERE action_type = ?d
          AND ts BETWEEN ?t AND ?t
        ORDER BY ts DESC
    ", LOG_LOGIN_FAILURE, $date_start, $date_end);

    $result = [];


    foreach ($rows as $r) {
        $details = unserialize($r->details);
        if (empty($details['uname'])) continue;

        if (in_array($details['uname'], $usernames)) {
            $r->details = $details;
            $result[] = $r;
        }
    }


    return $result;
}
