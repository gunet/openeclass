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

class Recycle {

    /**
     * Store a table entry to the recycle bin
     * @param string $tablename The table to retrieve data from
     * @param string $id The id of the table entry to store
     * @param string $idfieldname The primary key id of the table; could be null and retrieved automatically
     * @return boolean true, if successful
     */
    public static function storeObject($tablename, $id, $idfieldname = null) {
        return Recycle::storeObjectImpl($tablename, $id, $idfieldname, false);
    }

    /**
     * Store a table entry to the recycle bin and delete the original entry
     * @param string $tablename The table to retrieve data from
     * @param string $id The id of the table entry to delete
     * @param string $idfieldname The primary key id of the table; could be null and retrieved automatically
     * @return boolean true, if successful
     */
    public static function deleteObject($tablename, $id, $idfieldname = null) {
        return Recycle::storeObjectImpl($tablename, $id, $idfieldname, true);
    }

    private static function storeObjectImpl($tablename, $id, $idfieldname, $alsoDelete) {
        return Database::get()->transaction(function () use($tablename, $id, $idfieldname, $alsoDelete, &$success) {
                    if (is_null($idfieldname))
                        $idfieldname = DBHelper::primaryKeyOf($tablename);
                    if (is_null($idfieldname))
                        return Database::TRANSACTION_ERROR;
                    $result = Database::get()->querySingle("select * from `" . $tablename . "` where " . $idfieldname . " = ?d", $id);
                    if ($result) {
                        Database::get()->query("delete from recyclebin where `tablename` = ?s and `entryid` = ?d", $tablename, $id);
                        $result = (array) $result;  // need to do this and the casting back, because in strict mode unset($result->$idfieldname) is not possible
                        unset($result[$idfieldname]);
                        $result = (object) $result;
                        if (Database::get()->query("insert into recyclebin (tablename, entryid, entrydata) values (?s, ?d, ?s)", $tablename, $id, serialize($result))->affectedRows > 0) {
                            if ($alsoDelete) {
                                $dbresult = Database::get()->query("delete from `" . $tablename . "` where `" . $idfieldname . "` = ?d", $id);
                                return $dbresult && $dbresult->affectedRows > 0 ?
                                        Database::TRANSACTION_SUCCESS :
                                        Database::TRANSACTION_ERROR;
                            } else
                                return Database::TRANSACTION_SUCCESS;
                        }
                    }
                    return Database::TRANSACTION_ERROR;
                });
    }

    /**
     * Restore and return an object from the recycle bin. Note that no database altering is performed
     * @param string $tablename The table to retrieve data from
     * @param string $id The id of the table entry to restore
     * @param string $idfieldname The primary key id of the table; could be null and retrieved automatically
     * @return object The retrieved object
     */
    public static function restoreObject($tablename, $id, $idfieldname = null) {
        if (is_null($idfieldname))
            $idfieldname = DBHelper::primaryKeyOf($tablename);
        if (is_null($idfieldname))
            return null;
        $entrydata = Database::get()->querySingle("select entrydata from recyclebin where tablename=?s and entryid=?d", $tablename, $id);
        if ($entrydata && $entrydata->entrydata) {
            $result = unserialize($entrydata->entrydata);
            $result = (array) $result;  // need to do this and the casting back, because in strict mode $result->$idfieldname = ...  is not possible
            $result[$idfieldname] = $id;
            $result = (object) $result;
            return $result;
        }
        return null;
    }

    /**
     * Undelete a table entry from the recycle bin and store it back to the table.
     * @param string $tablename The table to retrieve data from
     * @param string $id The id of the table entry to undelete. If the id field was altered in the recycle bin table, then the new id will be used, not the old id when the object was stored.
     * @param string $idfieldname The primary key id of the table; could be null and retrieved automatically
     * @return boolean true, if successful
     */
    public static function undeleteObject($tablename, $id, $idfieldname = null) {
        if (is_null($idfieldname))
            $idfieldname = DBHelper::primaryKeyOf($tablename);
        if (is_null($idfieldname))
            return null;
        $result = Recycle::restoreObject($tablename, $id, $idfieldname);
        if ($result)
            return Recycle::restoreFromRecycle($tablename, $result, $id);
        else
            return false;
    }

    /**
     * Persist an object to a table
     * @param type $tablename The table to store data to
     * @param type $entity The object to persist
     * @param type $idfieldname The primary key id of the table; could be null and retrieved automatically
     * @return boolean true, if successful
     */
    public static function persistObject($tablename, $entity) {
        Recycle::restoreFromRecycle($tablename, $entity, null);
    }

    private static function restoreFromRecycle($tablename, $entity, $id) {
        return Database::get()->transaction(function () use($tablename, $entity, $id, &$success) {
                    $fields = "";
                    $spacer = "";
                    $values = array();
                    foreach ($entity as $key => $value) {
                        $fields .= $key . ", ";
                        $spacer .= "?s, ";
                        $values[] = $value;
                    }
                    if (strlen($fields) > 0) {
                        $fields = substr($fields, 0, strlen($fields) - 2);
                        $spacer = substr($spacer, 0, strlen($spacer) - 2);
                        $dbresult = Database::get()->query("insert into `$tablename` ($fields) values ($spacer);", $values);
                        if ($dbresult && $dbresult->affectedRows > 0) {
                            if (is_null($id))
                                return Database::TRANSACTION_SUCCESS;
                            else
                                return
                                        Database::get()->query("delete from recyclebin where `tablename` = ?s and `entryid` = ?d ", $tablename, $id)->affectedRows > 0 ?
                                        Database::TRANSACTION_SUCCESS :
                                        Database::TRANSACTION_ERROR;
                        }
                    }
                    return Database::TRANSACTION_ERROR;
                });
    }

}
