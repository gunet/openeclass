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

        // Could not use functional interface, since sub-transaction is needed and this is not supported by MySQL, so paging is performed.
        $size = Database::get()->querySingle("select count(*) as count from `$detailTableName`")->count;
        $from = 0;
        while ($from < $size) {
            $to = ($size - $from) > 100 ? 100 : $size - $from;
            foreach (Database::get()->queryArray("select `$detailIDFieldName`,`$detailFieldName` from `$detailTableName` limit $to offset $from") as $entry) {
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
            }
            $from+=100;
        }
        DBHelper::createForeignKey($detailTableName, $detailFieldName, $masterTableName, $masterIDFieldName);
    }

}
