<?php
/* ========================================================================
 * Open eClass 2.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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


/*
 * Announcements preview utility functions
 *
 */

define('PREVIEW_SIZE', 500);

function create_preview($content, $preview, $id, $cours_id, $code_cours)
{
        global $langMore, $mysqlMainDb;

        if (!$preview) {
                $preview = purify(standard_text_escape(ellipsize($content, PREVIEW_SIZE,
                        "<strong>&nbsp;...<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;an_id=$id'> <span class='smaller'>[$langMore]</span></a></strong>")));
                db_query("UPDATE `$mysqlMainDb`.annonces
                                 SET preview = " . autoquote($preview) . "
                                 WHERE id = $id AND cours_id = $cours_id");
        }
        return $preview;
}
