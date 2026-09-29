<?php
/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2026, Greek Universities Network - GUnet
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

$require_departmentmanage_user = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/user.class.php';
require_once 'Service.php';
require_once 'Sync.php';
require_once 'modules/create_course/functions.php';
require_once 'modules/admin/extconfig/externals.php';

use modules\eduapi\Service;
use modules\eduapi\Sync;

load_js('jstree3');

$toolName = $langAdmin;
$pageName = $langEduApiSync;
$navigation[] = ['url' => '../admin/index.php', 'name' => $langAdmin];

$tree = new Hierarchy();
$service = new Service();
$user = new User();

if (isset($_POST['import'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) {
        csrf_token_error();
    }

    $sessionId = isset($_POST['session']) ? trim($_POST['session']) : '';
    $offeringIds = (isset($_POST['offerings']) && is_array($_POST['offerings'])) ? $_POST['offerings'] : [];
    $syncType = $_POST['sync_type'] ?? 'full';
    $syncStudents = ($syncType === 'full');

    // Admin-approved course-code prefixes from the confirmation step,
    // invalid or empty entries fall back to the auto-calculated prefix
    $codePrefixes = [];
    if (isset($_POST['code_prefix']) && is_array($_POST['code_prefix'])) {
        foreach ($_POST['code_prefix'] as $organizationId => $rawPrefix) {
            $prefix = Sync::sanitizePrefix($rawPrefix);
            if ($prefix !== '') {
                // '__none__' is the form key for offerings without organization
                $organizationId = ($organizationId === '__none__') ? '' : trim((string)$organizationId);
                $codePrefixes[$organizationId] = $prefix;
            }
        }
    }

    // Organization nodes approved on the confirmation step, null when the
    // import was posted without it
    $approvedOrgs = null;
    if (isset($_POST['orgs_step'])) {
        $approvedOrgs = [];
        if (isset($_POST['approved_orgs']) && is_array($_POST['approved_orgs'])) {
            foreach ($_POST['approved_orgs'] as $approvedOrgId) {
                $approvedOrgId = trim((string)$approvedOrgId);
                if ($approvedOrgId !== '') {
                    $approvedOrgs[] = $approvedOrgId;
                }
            }
        }
    }

    // Increase execution time and memory limits for large imports
    // set_time_limit(600); // 10 minutes
    // ini_set('memory_limit', '512M');

    try {
        if (empty($sessionId) || empty($offeringIds)) {
            throw new Exception($langEduApiNoOfferingsSelected);
        }

        // School node: the admin's choice from the confirmation step when
        // valid (department managers only within their managed subtrees),
        // else the automatic pick
        $topNode = null;
        $chosenNodeId = isset($_POST['school_node']) ? intval($_POST['school_node']) : 0;
        if ($chosenNodeId > 0) {
            $chosenNode = Database::get()->querySingle(
                "SELECT id, code, lft, rgt FROM hierarchy WHERE id = ?d", $chosenNodeId);
            if ($chosenNode) {
                if ($is_admin) {
                    $topNode = $chosenNode;
                } else {
                    foreach ($user->getDepartmentIds($uid) as $departmentId) {
                        $department = Database::get()->querySingle("SELECT lft, rgt FROM hierarchy WHERE id = ?d", intval($departmentId));
                        if ($department && $chosenNode->lft >= $department->lft && $chosenNode->rgt <= $department->rgt) {
                            $topNode = $chosenNode;
                            break;
                        }
                    }
                }
            }
        }
        if (!$topNode) {
            $topNode = $service->getSchoolNode();
        }
        if (!$topNode) {
            throw new Exception($langEduApiTopNodeNotFound);
        }

        $sync = new Sync($service, $topNode, $tree, $language, $uid, "$_SESSION[givenname] $_SESSION[surname]");
        $summary = $sync->run($sessionId, $offeringIds, $syncStudents, $codePrefixes, $approvedOrgs);

        // Build structured result message
        $header = $syncStudents ? $langEduApiSyncCompletedFull : $langEduApiSyncCompletedPartial;
        $message = $header . ' ' . q($summary['sessionCode']) . '.<br>';

        $message .= "<strong>$langEduApiCoursesLabel</strong> $langEduApiCreated {$summary['coursesCreated']}, $langEduApiReused {$summary['coursesReused']}<br>";

        if ($syncStudents) {
            $message .= "<strong>$langEduApiUsersLabel</strong> $langEduApiCreated {$summary['usersCreated']}";
            if ($summary['usersAdopted'] > 0) {
                $message .= ", $langEduApiAdopted {$summary['usersAdopted']}";
            }
            if ($summary['usersRenamed'] > 0) {
                $message .= ", $langEduApiRenamed {$summary['usersRenamed']}";
            }
            if ($summary['usersPromoted'] > 0) {
                $message .= ", $langEduApiPromoted {$summary['usersPromoted']}";
            }
            $message .= '<br>';

            $message .= "<strong>$langEduApiEnrollmentsLabel</strong> $langEduApiCreated {$summary['enrollmentsCreated']}<br>";

            if (!empty($summary['skippedPersons'])) {
                $skippedCount = count($summary['skippedPersons']);
                $message .= "<br><strong class='text-warning'>&#9888;</strong> $skippedCount $langEduApiSkippedPersons<br>";
                $message .= '<small>' . implode('<br>', array_map('q', array_slice($summary['skippedPersons'], 0, 10)));
                if ($skippedCount > 10) {
                    $message .= '<br>' . sprintf($langEduApiAndMore, $skippedCount - 10);
                }
                $message .= '</small><br>';
            }
        }

        if (!empty($summary['warnings'])) {
            $message .= "<br><strong>$langEduApiWarningsLabel</strong><br><small>" . implode('<br>', array_map('q', array_slice($summary['warnings'], 0, 10)));
            if (count($summary['warnings']) > 10) {
                $message .= '<br>' . sprintf($langEduApiAndMore, count($summary['warnings']) - 10);
            }
            $message .= '</small><br>';
        }

        if (!empty($summary['errors'])) {
            $message .= "<br><strong>$langEduApiErrorsLabel</strong><br><small>" . implode('<br>', array_map('q', array_slice($summary['errors'], 0, 10)));
            if (count($summary['errors']) > 10) {
                $message .= '<br>' . sprintf($langEduApiAndMore, count($summary['errors']) - 10);
            }
            $message .= '</small><br>';
        }

        if ($summary['teachersNotified'] > 0) {
            $message .= "<br><strong>{$summary['teachersNotified']} $langEduApiTeachersNotified</strong>";
        }

        $alertClass = ($summary['coursesCreated'] > 0 || $summary['coursesReused'] > 0) ? 'alert-success' : 'alert-warning';
        Session::flash('message', $message);
        Session::flash('alert-class', $alertClass);
    } catch (Exception $e) {
        Session::flash('message', $langEduApiImportFailed . ' ' . q($e->getMessage()));
        Session::flash('alert-class', 'alert-danger');
    }

    // Clear hierarchy caches
    (new FileCache('nodes', 300))->clear();
    (new FileCache('coursedeps', 300))->clear();

    redirect_to_home_page('modules/admin/hierarchy.php');
}

// ---- Confirmation step (POST preview_codes): the admin approves or edits the
// auto-calculated course-code prefix of each organization before the sync runs ----
if (isset($_POST['preview_codes'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) {
        csrf_token_error();
    }

    $sessionId = isset($_POST['session']) ? trim($_POST['session']) : '';
    $offeringIds = (isset($_POST['offerings']) && is_array($_POST['offerings'])) ? $_POST['offerings'] : [];
    $syncType = (isset($_POST['sync_type']) && $_POST['sync_type'] === 'partial') ? 'partial' : 'full';

    try {
        if (empty($sessionId) || empty($offeringIds)) {
            throw new Exception($langEduApiNoOfferingsSelected);
        }

        $selectedOfferings = [];
        foreach ($service->getCourseOfferings($sessionId) as $offering) {
            if (!empty($offering->sourcedId) && in_array($offering->sourcedId, $offeringIds)) {
                $selectedOfferings[] = $offering;
            }
        }
        if (empty($selectedOfferings)) {
            throw new Exception($langEduApiNoOfferingsSelected);
        }
    } catch (Exception $e) {
        Session::flash('message', q($e->getMessage()));
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page('modules/eduapi/index.php?session=' . urlencode($sessionId));
    }

    $sessionCode = trim((string)($selectedOfferings[0]->academicSessionCode ?? ''));
    if ($sessionCode === '') {
        $sessionCode = $sessionId;
    }

    // Group the selected offerings by organization, same as Sync::run()
    $offeringsByOrg = [];
    foreach ($selectedOfferings as $offering) {
        $organizationId = trim((string)($offering->organization ?? ''));
        $offeringsByOrg[$organizationId][] = $offering;
    }

    $usedFallback = false;
    $semesterTag = Sync::semesterTag($sessionCode, $usedFallback);

    $prefixRows = [];
    $orgNamesById = [];
    foreach ($offeringsByOrg as $organizationId => $orgOfferings) {
        if ($organizationId !== '') {
            $orgName = '';
            try {
                $organization = $service->getOrganization($organizationId);
                $orgName = Service::organizationName($organization, $language);
            } catch (Exception $e) {
                // fall through to organizationCode / raw id below
            }
            if ($orgName === '') {
                foreach ($orgOfferings as $offering) {
                    if (!empty($offering->organizationCode)) {
                        $orgName = $offering->organizationCode;
                        break;
                    }
                }
            }
            if ($orgName === '') {
                $orgName = $organizationId;
            }
            $orgNamesById[$organizationId] = $orgName;
            $proposedPrefix = Sync::orgInitials($orgName) . '-' . $semesterTag;
        } else {
            $orgName = $langEduApiNoOrganization;
            $proposedPrefix = 'EA-' . $semesterTag;
        }

        // A code already stamped on the session node by a previous sync wins
        $mappedNode = Database::get()->querySingle(
            "SELECT h.code FROM eduapi_nodes e
             JOIN hierarchy h ON h.id = e.hierarchy_id
             WHERE e.ref_key = ?s",
            'session:' . $organizationId . ':' . $sessionId);
        if ($mappedNode && trim((string)$mappedNode->code) !== '') {
            $proposedPrefix = trim($mappedNode->code);
        }

        $prefixRows[] = [
            'field_key' => ($organizationId !== '') ? $organizationId : '__none__',
            'org_name' => $orgName,
            'count' => count($orgOfferings),
            'prefix' => $proposedPrefix,
        ];
    }

    // ---- Organization ancestor chains: one checkbox per distinct organization
    // (deduplicated union across the org groups), parent -> child, all pre-checked ----
    $orgList = []; // orgId => ['name', 'depth', 'directCount', 'exists']
    foreach ($offeringsByOrg as $organizationId => $orgOfferings) {
        if ($organizationId === '') {
            continue;
        }
        $chainEntries = [];
        try {
            foreach ($service->getOrganizationChain($organizationId) as $depth => $chainOrg) {
                $chainId = trim((string)($chainOrg->sourcedId ?? ''));
                if ($chainId === '') {
                    continue;
                }
                $chainName = ($chainId === $organizationId)
                    ? $orgNamesById[$organizationId]
                    : Service::organizationName($chainOrg, $language);
                if ($chainName === '') {
                    $chainName = $chainId;
                }
                $chainEntries[] = [$chainId, $chainName, $depth];
            }
        } catch (Exception $e) {
            // fall through to the direct organization alone
        }
        if (empty($chainEntries)) {
            $chainEntries[] = [$organizationId, $orgNamesById[$organizationId], 0];
        }
        foreach ($chainEntries as $entry) {
            list($chainId, $chainName, $depth) = $entry;
            if (!isset($orgList[$chainId])) {
                $orgList[$chainId] = ['name' => $chainName, 'depth' => $depth, 'directCount' => 0, 'exists' => false];
            }
            if ($chainId === $organizationId) {
                $orgList[$chainId]['directCount'] += count($orgOfferings);
            }
        }
    }

    $orgItems = [];
    foreach ($orgList as $orgId => $info) {
        $exists = Database::get()->querySingle("SELECT 1 AS found FROM eduapi_nodes WHERE ref_key = ?s", 'org:' . $orgId);
        $orgItems[] = [
            'id' => $orgId,
            'name' => $info['name'],
            'depth' => $info['depth'],
            'direct_count' => $info['directCount'],
            'exists' => (bool)$exists,
            'checkbox_id' => 'eduapi-org-' . md5($orgId),
        ];
    }

    // ---- School node picker: everything above is created under this node ----
    $schoolNode = $service->getSchoolNode();
    $treeopts = [
        // Double quotes are required here: buildJSNodePicker() splices this
        // string into single-quoted JS literals
        'params' => 'name="school_node"',
        'multiple' => false,
        'defaults' => $schoolNode ? $schoolNode->id : '',
    ];
    if (!$is_admin) {
        $treeopts['allowables'] = $user->getDepartmentIds($uid);
    }
    list($pickerJs, $pickerHtml) = $tree->buildNodePicker($treeopts);
    $head_content .= $pickerJs;

    view('admin.eduapi.confirm', [
        'session_id' => $sessionId,
        'sync_type' => $syncType,
        'sync_type_label' => ($syncType === 'full') ? $langEduApiFullSyncBtn : $langEduApiPartialSyncBtn,
        'offering_ids' => $offeringIds,
        'selected_count' => count($offeringIds),
        'fallback_warning' => $usedFallback ? sprintf($langEduApiCodeFallback, $sessionCode, $semesterTag) : null,
        'org_items' => $orgItems,
        'picker_html' => $pickerHtml,
        'prefix_rows' => $prefixRows,
        'back_url' => $_SERVER['SCRIPT_NAME'] . '?session=' . urlencode($sessionId),
    ]);
    exit;
}

// ---- Preview (GET): live session list, then offerings of the chosen session ----
$data = [];
try {
    $sessions = $service->getAcademicSessions();
    if (empty($sessions)) {
        throw new Exception($langEduApiNoSessions);
    }

    // Sessions are sorted by endDate: preselect the most recent one
    $selectedSession = $_GET['session'] ?? '';
    $sessionIds = array_map(function ($session) {
        return $session->sourcedId ?? '';
    }, $sessions);
    if (empty($selectedSession) || !in_array($selectedSession, $sessionIds)) {
        $selectedSession = end($sessionIds);
    }

    // Arrange the sessions as a parent -> child tree (schoolYear -> semester
    // -> gradingPeriod), flattened depth-first for indented rendering.
    // Orphans (parent missing from the list) render as roots; a seen-set
    // guards against parent cycles in the API data.
    $data['session_tree'] = eduapi_session_tree($sessions);

    $data['sessions'] = $sessions;
    $data['selected_session'] = $selectedSession;
    $data['offerings'] = $service->getCourseOfferings($selectedSession);

    // Resolve organization names for the distinct organization ids
    $data['org_names'] = [];
    foreach ($data['offerings'] as $offering) {
        $organizationId = $offering->organization ?? '';
        if ($organizationId === '' || isset($data['org_names'][$organizationId])) {
            continue;
        }
        try {
            $organization = $service->getOrganization($organizationId);
            $data['org_names'][$organizationId] = Service::organizationName($organization, $language);
        } catch (Exception $e) {
            $data['org_names'][$organizationId] = ''; // fall back to organizationCode below
        }
    }
} catch (Exception $e) {
    $data['error'] = $e->getMessage();
}

function eduapi_session_label($session, $language) {
    $title = is_array($session->title ?? null)
        ? Service::pickTitle($session->title, $language)
        : trim((string)($session->title ?? ''));
    if ($title === '') {
        $title = $session->sourcedId ?? '';
    }
    $start = eduapi_format_date($session->startDate ?? '');
    $end = eduapi_format_date($session->endDate ?? '');
    if ($start || $end) {
        $title .= ' (' . implode(' - ', array_filter([$start, $end], 'strlen')) . ')';
    }
    return $title;
}

/**
 * Render the date or datetime string, accepts 'YYYY-MM-DD' or ISO-8601.
 * Midnight values render as dd/MM/yyyy, else as dd/MM/yyyy HH:mm.
 */
function eduapi_format_date($value) {
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }
    try {
        $dt = new DateTime($value); // retain the string's own offset
    } catch (Exception $e) {
        return $value;
    }
    if ($dt->format('H:i:s') === '00:00:00') {
        $day = new DateTime($dt->format('Y-m-d'));
        return format_locale_date($day->getTimestamp(), null, false, 'dd/MM/yyyy');
    }
    return format_locale_date($dt->getTimestamp(), null, true, 'dd/MM/yyyy HH:mm');
}

/**
 * Translated label for an academic session's sessionType
 * (unknown types fall back to the raw value, empty type to '')
 */
function eduapi_session_type_label($session) {
    global $langEduApiTypeSchoolYear, $langEduApiTypeSemester, $langEduApiTypeGradingPeriod;

    $type = trim((string)($session->sessionType ?? ''));
    $map = [
        'schoolyear' => $langEduApiTypeSchoolYear,
        'semester' => $langEduApiTypeSemester,
        'gradingperiod' => $langEduApiTypeGradingPeriod,
    ];

    return $map[strtolower($type)] ?? $type;
}

/**
 * Flatten the academic sessions into depth-first parent -> child order.
 * Sessions with an empty or unknown parent become roots, sibling order
 * follows the API (endDate) order, a seen-set breaks parent cycles.
 *
 * @return array list of ['session' => object, 'depth' => int]
 */
function eduapi_session_tree(array $sessions): array {
    $byId = [];
    $childrenOf = [];
    foreach ($sessions as $session) {
        $sessionId = trim((string)($session->sourcedId ?? ''));
        if ($sessionId === '') {
            continue;
        }
        $byId[$sessionId] = $session;
        $childrenOf[Service::parentSourcedId($session)][] = $sessionId;
    }

    $flat = [];
    $seen = [];
    $append = function ($sessionId, $depth) use (&$append, &$flat, &$seen, $byId, $childrenOf) {
        if (isset($seen[$sessionId])) {
            return;
        }
        $seen[$sessionId] = true;
        $flat[] = ['session' => $byId[$sessionId], 'depth' => $depth];
        foreach ($childrenOf[$sessionId] ?? [] as $childId) {
            $append($childId, $depth + 1);
        }
    };

    foreach ($byId as $sessionId => $session) {
        $parentId = Service::parentSourcedId($session);
        if ($parentId === '' || !isset($byId[$parentId])) {
            $append($sessionId, 0);
        }
    }
    // Anything left unvisited sits inside a parent cycle, render as roots
    foreach (array_keys($byId) as $sessionId) {
        $append($sessionId, 0);
    }

    return $flat;
}

$viewData = [];
if (isset($data['error'])) {
    $viewData['error'] = $data['error'];
} else {
    // Session dropdown options: indented tree labels with translated type tags
    $sessionOptions = [];
    foreach ($data['session_tree'] as $entry) {
        $session = $entry['session'];
        $sessionId = $session->sourcedId ?? '';
        $label = eduapi_session_label($session, $language);
        $typeLabel = eduapi_session_type_label($session);
        if ($typeLabel !== '') {
            $label .= ' — ' . $typeLabel;
        }
        $sessionOptions[] = [
            'value' => $sessionId,
            'label' => str_repeat("\u{00A0}", $entry['depth'] * 3) . ($entry['depth'] > 0 ? '└ ' : '') . $label,
            'selected' => ($sessionId === $data['selected_session']),
        ];
    }

    $offeringsRows = [];
    $preselectedCount = 0;
    foreach ($data['offerings'] as $offering) {
        $isActive = (strcasecmp($offering->recordStatus ?? 'active', 'active') === 0);
        $isOpen = (strcasecmp($offering->registrationStatus ?? '', 'open') === 0);
        $preselected = ($isActive && $isOpen);
        if ($preselected) {
            $preselectedCount++;
        }
        $organizationId = $offering->organization ?? '';
        $organizationName = $data['org_names'][$organizationId] ?? '';
        if ($organizationName === '') {
            $organizationName = $offering->organizationCode ?? '';
        }
        $offeringsRows[] = [
            'sourced_id' => $offering->sourcedId ?? '',
            'title' => Service::pickTitle($offering->title ?? [], $language),
            'description' => Service::pickTitle($offering->description ?? [], $language),
            'org_name' => $organizationName,
            'dates' => implode(' - ', array_filter([
                eduapi_format_date($offering->startDate ?? ''),
                eduapi_format_date($offering->endDate ?? ''),
            ], 'strlen')),
            'enrolled' => ($offering->enrolledNumberStudents ?? '-') . ' / ' . ($offering->maxNumberStudents ?? '-'),
            'status' => ($offering->registrationStatus ?? '') . ' / ' . ($offering->recordStatus ?? ''),
            'is_active' => $isActive,
            'is_open' => $isOpen,
            'preselected' => $preselected,
        ];
    }

    $viewData = [
        'session_options' => $sessionOptions,
        'selected_session' => $data['selected_session'],
        'offerings_rows' => $offeringsRows,
        'offerings_total' => count($data['offerings']),
        'preselected_count' => $preselectedCount,
    ];
}

view('admin.eduapi.index', $viewData);
