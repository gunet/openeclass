<?php

/* ========================================================================
 * Open eClass
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
 * ========================================================================
 */

$require_mlogin = false;
$require_noerrors = true;
require_once 'minit.php';
require_once 'modules/auth/auth.inc.php';

list($identityDom, $identityDomRoot) = createIdentityDom();

echo $identityDom->saveXML();
exit();

//////////////////////////////////////////////////////////////////////////////////////

function createIdentityDom () {
    global $Institution, $InstitutionUrl, $siteName;

    $dom = new DomDocument('1.0', 'utf-8');
    $root = $dom->appendChild($dom->createElement('identity'));

    $inst = $root->appendChild($dom->createElement('institute'));
    $inst->appendChild(new DOMAttr('name', q($Institution)));
    $inst->appendChild(new DOMAttr('url', q($InstitutionUrl)));

    $plat = $root->appendChild($dom->createElement('platform'));
    $plat->appendChild(new DOMAttr('name', q($siteName)));
    $plat->appendChild(new DOMAttr('version', q(ECLASS_VERSION)));

    $adm = $root->appendChild($dom->createElement('administrator'));
    $adm->appendChild(new DOMAttr('name', q(get_config('admin_name'))));

    $authMethods = getAuthMethods();
    if ($authMethods) {
        foreach ($authMethods as $method) {
            $auth = $root->appendChild($dom->createElement('auth'));
            $auth->appendChild(new DOMAttr('title', $method['title']));
            if (isset($method['url'])) {
                $auth->appendChild(new DOMAttr('url', $method['url']));
            }
        }
    }

    $dom->formatOutput = true;
    return array($dom, $root);
}

function getAuthMethods () {
    global $langLogInWith, $extAuthMethods, $hybridAuthMethods, $urlServer;
    $methods = array();
    $plainMethodProvided = false;
    $next = 'next=/modules/mobile/mtoken.php';
    $q = Database::get()->queryArray("SELECT auth_name, auth_default, auth_title
        FROM auth WHERE auth_default > 0
        ORDER BY auth_default DESC, auth_id");
    foreach ($q as $method) {
        $auth = $method->auth_name;
        $title = empty($method->auth_title)? "$langLogInWith $auth": getSerializedMessage($method->auth_title);
        if (in_array($auth, $extAuthMethods)) {
            $url = $urlServer . ($auth == 'cas'? 'modules/auth/cas.php': 'secure/') . '?' . $next;
            $methods[] = array('url' => $url, 'title' => $title);
        } elseif (in_array($auth, $hybridAuthMethods)) {
            $url = $urlServer . 'index.php?provider=' . $auth . '&' . $next;
            $title =  ucfirst($auth);
            $methods[] = array('url' => $url, 'title' => $title);
        } elseif (!$plainMethodProvided) {
            $methods[] = array('title' => $title);
            $plainMethodProvided = true;
        }
    }
    if (count($methods) == 1 and $plainMethodProvided) {
        // if plain username/password login is the only method possible,
        // no extra info needed
        return null;
    } else {
        return $methods;
    }
}
