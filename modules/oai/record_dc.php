<?php

/* ========================================================================
 * Open eClass
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
 * ========================================================================
 */

/** \file
 * \brief Definition of Dublin Core handler.
 *
 * It is a plug-in helper function which will be called from where a metadata in DC format is being generated.
 * The name of function defined here cannot be changed.
 *
 * \sa oaidp-config.php 
 */
function create_metadata($outputObj, $cur_record, $identifier, $setspec) {
    // debug_message('In '.__FILE__.' function '.__FUNCTION__.' was called.');
    // debug_var_dump('metadata_node', $metadata_node);
    $metadata_node = $outputObj->create_metadata($cur_record);
    $obj_node = new ECLASS_OAIDC($outputObj, $metadata_node);
    try {
        $obj_node->create_obj_node($setspec, $identifier);
    } catch (Exception $e) {
        echo 'Caught exception: ', $e->getMessage(), " when adding $identifier\n";
    }
}
