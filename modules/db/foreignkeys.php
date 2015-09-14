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
     * Create a new foreign key from $masterTableName to $detailTableName based
     * on the $detailFieldName field
     * @param type $detailTableName The detail table name
     * @param type $detailFieldName The detail table's field name, which
     * connects with the master table
     * @param type $masterTableName The master table name
     * @param type $defaultEntryResolver A numeric value or a function which 
     * returns a numeric value, in order to get the master id field value. This
     * will be used as default for those entries of the detail table, who are
     * orphaned (have a wrong reference). If this value is null, or if the
     * function returns null, and the field does not accept null values, then
     * the orphaned entries will be removed from the detail table and recycled
     * to the recyclebin table.
     */
    public static function create($detailTableName, $detailFieldName, $masterTableName, $defaultEntryResolver) {
        $masterIDFieldName = DBHelper::primaryKeyOf($masterTableName);
        if (DBHelper::foreignKeyExists($detailTableName, $detailFieldName, $masterTableName, $masterIDFieldName))
            return;
        $detailIDFieldName = DBHelper::primaryKeyOf($detailTableName);
        $defaultEntryID = is_null($defaultEntryResolver) ? null :
                (is_numeric($defaultEntryResolver) ? $defaultEntryResolver :
                        is_callable($defaultEntryResolver) ? $defaultEntryResolver() :
                                null);
        $nullable = DBHelper::isColumnNullable($detailTableName, $detailFieldName);

        $wrongIDs = Database::get()->queryArray("select `$detailTableName`.`$detailIDFieldName` as detailid from `$detailTableName`
                left join `$masterTableName` on `$detailTableName`.`$detailFieldName` = `$masterTableName`.`$masterIDFieldName`
                where `$detailTableName`.`$detailFieldName` is not null and `$masterTableName`.`$masterIDFieldName` is null");
        if ($wrongIDs) {
            foreach ($wrongIDs as $entry) {
                if (is_null($defaultEntryID)) {
                    if ($nullable)
                        Database::get()->query("update `" . $detailTableName . "` set `" . $detailFieldName . "` = NULL where $detailIDFieldName = ?d"
                                , $entry->detailid);
                    else
                        Recycle::deleteObject($detailTableName, $entry->detailid, $detailIDFieldName);
                } else {
                    Database::get()->query("update `" . $detailTableName . "` set `" . $detailFieldName . "` = ?d where $detailIDFieldName = ?d"
                            , $defaultEntryID, $entry->detailid);
                }
            }
        }
        DBHelper::createForeignKey($detailTableName, $detailFieldName, $masterTableName, $masterIDFieldName);
    }

}
