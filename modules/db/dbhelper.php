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

abstract class DBHelper {

    private static $helper_impl;

    private static function impl() {
        if (!DBHelper::$helper_impl) {
            switch ('DB_TYPE') {
                case "MYSQL":
                default :
                    DBHelper::$helper_impl = new _DBHelper_MYSQL();
            }
        }
        return DBHelper::$helper_impl;
    }

    /**
     * @deprecated It should be private.
     * @param int $unixDate The date in numeric format. If it is missing (or null) defaults to "now".
     * @return timestamp The timestamp in SQL wrapper
     */
    public static function intToDate($unixDate = null) {
        if (is_null($unixDate))
            $unixDate = time();
        return DBHelper::impl()->intToDateImpl(intval($unixDate));
    }

    /**
     * 
     * @param int $secondsOffset Offset by current time in seconds
     * @return timestamp The timestamp in SQL wrapper
     */
    public static function timeAfter($secondsOffset = null) {
        if (is_null($secondsOffset))
            $secondsOffset = 0;
        return DBHelper::impl()->intToDateImpl(time() + intval($secondsOffset));
    }

    /**
     * Check if a specific table exists in the database
     * @param String $table The table name
     * @param String $db The database name; could be null for the default database
     * @return boolean true if exists, false otherwise
     */
    public static function tableExists($table, $db = null) {
        return DBHelper::impl()->tableExistsImpl($table, $db);
    }

    /**
     * Check if a specific field in a table exists in the database
     * @param String $table The table name
     * @param String $field The field name
     * @param String $db The database name; could be null for the default database
     * @return boolean true if exists, false otherwise
     */
    public static function fieldExists($table, $field, $db = null) {
        return DBHelper::impl()->fieldExistsImpl($table, $field, $db);
    }

    /**
     * Check if a specific field index of a field in a table exists in the database
     * @param String $table The table name
     * @param String $index_name The field index name
     * @param String $db The database name; could be null for the default database
     * @return boolean true if exists, false otherwise
     */
    public static function indexExists($table, $index_name, $db = null) {
        return DBHelper::impl()->indexExistsImpl($table, $index_name, $db);
    }

    /**
     * Specifically lock a table and release this lock when execution has finished
     * @param callable $function The code inside this function will be called while the database has locked the given tables
     * @param String... $table a list of tables to lock. It could be more than one table
     */
    public static function writeLockTables($function, $table) {
        $arguments = func_get_args();
        if (count($arguments) < 2)
            return;
        DBHelper::impl()->writeLockTablesImpl($arguments[0], array_slice($arguments, 1));
    }

    abstract protected function intToDateImpl($unixdate);

    abstract protected function tableExistsImpl($table, $db);

    abstract protected function fieldExistsImpl($table, $field, $db);

    abstract protected function indexExistsImpl($table, $index_name, $db);

    abstract protected function writeLockTablesImpl($function, $tables);
}

/**
 * Private object to implement the various helper commands.
 * All method parameters should be already checked.
 */
class _DBHelper_MYSQL extends DBHelper {

    protected function intToDateImpl($unixdate) {
        return "FROM_UNIXTIME(" . $unixdate . ")";
    }

    protected function tableExistsImpl($table, $db) {
        global $mysqlMainDb;
        if ($db == null)
            $db = $mysqlMainDb;
        return count(Database::get()->queryArray('SHOW TABLES FROM `' . $db . '` LIKE \'' . $table . '\'')) == 1;
    }

    protected function fieldExistsImpl($table, $field, $db) {
        return count(Database::get($db)->queryArray("SHOW COLUMNS from $table LIKE '$field'")) > 0;
    }

    protected function indexExistsImpl($table, $index_name, $db) {
        return count(Database::get($db)->queryArray("SHOW INDEX FROM `$table` WHERE Key_name = ?s", $index_name)) > 0;
    }

    public function writeLockTablesImpl($function, $tables) {
        if (count($tables) < 1) {
            return;
        }
        if (is_callable($function)) {
            $names = "";
            foreach ($tables as $tableName) {
                $names .= ", `" . $tableName . "` WRITE";
            }
            if (strlen($names) > 2)
                $names = substr($names, 2);
            try {
                Database::get()->query("LOCK TABLES " . $names);
            } catch (Exception $ex) {
                Database::get()->query("UNLOCK TABLES");
                throw $ex;
            }
            Database::get()->query("UNLOCK TABLES");
        } else {
            $backtrace_entry = debug_backtrace();
            $backtrace_info = $backtrace_entry[1];
            Debug::message("Lock needs a function as parameter", $backtrace_info['file'], $backtrace_info['line']);
        }
    }

}
