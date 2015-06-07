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

require_once 'database.php';
require_once 'recycle.php';

final class ForeignKeys {

    /**
     * Create a new foreign key from $masterTableName to $detailTableName based on the $detailFieldName field
     * @param type $detailTableName The detail table name
     * @param type $detailFieldName The detail table's field name, which connects with the master table
     * @param type $masterTableName The master table name
     * @param type $defaultEntryResolver A function which returns the master id 
     * field, which will be used as default for all entries of detail table,
     * which are orphaned or where the reference is missing. If this function
     * return null, or is null itself, then the orphaned entries will be removed
     * from the detail table and recycled to the recyclebin table.
     */
    public static function create($detailTableName, $detailFieldName, $masterTableName, $defaultEntryResolver) {
        $masterIDFieldName = DBHelper::primaryKeyOf($masterTableName);
        $detailIDFieldName = DBHelper::primaryKeyOf($detailTableName);
        $defaultEntryID = $defaultEntryResolver ? $defaultEntryResolver() : null;

        Database::get()->queryFunc("select `$detailIDFieldName`,`$detailFieldName`  from `$detailTableName`"
                , function ($entry) use($masterTableName, $masterIDFieldName, $detailTableName, $detailIDFieldName, $detailFieldName, $defaultEntryID) {
            $masterID = $entry->$detailFieldName;
            if (!is_null($masterID)) {
                $master = Database::get()->querySingle("select `$masterIDFieldName` from `$masterTableName` where `$masterIDFieldName` = ?d", $masterID);
                if (!$master) {
                    $masterID = null;
                }
            }
            if (!$masterID) {   // Master wasn't found
                if (is_null($defaultEntryID)) {
                    Recycle::deleteObject($detailTableName, $entry->$detailIDFieldName, $detailIDFieldName);
                } else {
                    Database::get()->query("update `" . $detailTableName . "` set `" . $detailFieldName . "` = ?d", $defaultEntryID);
                }
            }
        });

        DBHelper::createForeignKey($detailTableName, $detailFieldName, $masterTableName, $masterIDFieldName);
    }

}
