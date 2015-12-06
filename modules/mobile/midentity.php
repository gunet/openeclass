<?php

/* ========================================================================
 * Open eClass 
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2015  Greek Universities Network - GUnet
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
require_once('minit.php');

list($identityDom, $identityDomRoot) = createIdentityDom();

echo $identityDom->saveXML();
exit();

//////////////////////////////////////////////////////////////////////////////////////

function createIdentityDom() {
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
    
    $dom->formatOutput = true;
    return array($dom, $root);
}
