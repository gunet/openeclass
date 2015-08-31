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
     * Find all the primary keys of a table.
     * @param string $table The table name
     * @return string The name of the primary key field
     */
    public static function primaryKeysOf($tableName) {
        return DBHelper::impl()->primaryKeysOfImpl($tableName);
    }

    /**
     * Find the primary key of a table. If more than one key exist, throw an exception.
     * @param string $table The table name
     * @return string The name of the primary key field
     */
    public static function primaryKeyOf($tableName) {
        $keys = DBHelper::primaryKeysOf($tableName);
        if (!$keys || count($keys) != 1) {
            $msg = "Exactly one primary key for table '$tableName' was expected; " . count($keys) . " found.";
            Debug::message($msg, Debug::CRITICAL);
            throw new Exception($msg);
        }
        return $keys[0];
    }

    public static function isColumnNullable($table, $column) {
        return DBHelper::impl()->isColumnNullableImpl($table, $column);
    }

    /**
     * Create a foreign key which connects the detail table's field $detailFieldName with master table's $masterIDFieldName
     * @param type $detailTableName The detail table name
     * @param type $detailFieldName The detail table's field name, which connects with master table
     * @param type $masterTableName The master table name
     * @param type $masterIDFieldName The master table's primary key field name
     */
    public static function createForeignKey($detailTableName, $detailFieldName, $masterTableName, $masterIDFieldName = null) {
        if (is_null($masterIDFieldName))
            $masterIDFieldName = DBHelper::primaryKeyOf($masterTableName);
        if (DBHelper::foreignKeyExists($detailTableName, $detailFieldName, $masterTableName, $masterIDFieldName))
            return;
        return DBHelper::impl()->createForeignKeyImpl($detailTableName, $detailFieldName, $masterTableName, $masterIDFieldName);
    }

    /**
     * Check if a foreign key which connects the detail table's field $detailFieldName with master table's $masterIDFieldName already exists.
     * @param type $detailTableName The detail table name
     * @param type $detailFieldName The detail table's field name, which connects with the master table
     * @param type $masterTableName The master table name
     * @param type $masterIDFieldName The master table's primary key field name
     */
    public static function foreignKeyExists($detailTableName, $detailFieldName, $masterTableName, $masterIDFieldName = null) {
        if (is_null($masterIDFieldName))
            $masterIDFieldName = DBHelper::primaryKeyOf($masterTableName);
        return DBHelper::impl()->foreignKeyExistsImpl($detailTableName, $detailFieldName, $masterTableName, $masterIDFieldName);
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

    abstract protected function primaryKeysOfImpl($table);

    abstract protected function isColumnNullableImpl($table, $column);

    abstract protected function createForeignKeyImpl($detailTableName, $detailFieldName, $masterTableName, $masterIDFieldName);

    abstract protected function foreignKeyExistsImpl($detailTableName, $detailFieldName, $masterTableName, $masterIDFieldName);
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
        global $mysqlMainDb;
        if ($db == null)
            $db = $mysqlMainDb;
        if (!DBHelper::tableExists($table, $db))
            return 0;
        return count(Database::get()->queryArray("SHOW COLUMNS from `$db`.`$table` LIKE '$field'")) > 0;
    }

    protected function indexExistsImpl($table, $index_name, $db) {
        global $mysqlMainDb;
        if ($db == null)
            $db = $mysqlMainDb;
        if (!DBHelper::tableExists($table, $db))
            return 0;
        return count(Database::get()->queryArray("SHOW INDEX FROM `$db`.`$table` WHERE Key_name = ?s", $index_name)) > 0;
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

    public function isColumnNullableImpl($tableName, $columnname) {
        $result = Database::get()->querySingle("select IS_NULLABLE from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME = ?s and COLUMN_NAME = ?s", $tableName, $columnname);
        return $result ? strcmp($result->IS_NULLABLE, 'YES') == 0 : false;
    }

    public function primaryKeysOfImpl($tableName) {
        $tableKeys = Database::get()->queryArray("show keys from `" . $tableName . "` where `Key_name` = 'PRIMARY'");
        $keys = array();
        foreach ($tableKeys as $key) {
            $keys[] = $key->Column_name;
        }
        return $keys;
    }

    private function getForeignKeyName($detailTableName, $detailFieldName, $masterTableName) {
        return "fk_" . $masterTableName . "_" . $detailTableName . "_" . $detailFieldName;
    }

    protected function createForeignKeyImpl($detailTableName, $detailFieldName, $masterTableName, $masterIDFieldName) {
        Database::get()->query("
            ALTER TABLE " . $detailTableName . "
            ADD CONSTRAINT " . $this->getForeignKeyName($detailTableName, $detailFieldName, $masterTableName) . "
            FOREIGN KEY (" . $detailFieldName . ")
            REFERENCES " . $masterTableName . "(" . $masterIDFieldName . ")");
    }

    protected function foreignKeyExistsImpl($detailTableName, $detailFieldName, $masterTableName, $masterIDFieldName) {
        $constrInfo = Database::get()->querySingle("select CONSTRAINT_NAME as name from INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                where TABLE_NAME = ?s 
                and COLUMN_NAME = ?s 
                and REFERENCED_TABLE_NAME = ?s 
                and REFERENCED_COLUMN_NAME = ?s 
        ", $detailTableName, $detailFieldName, $masterTableName, $masterIDFieldName);
        if ($constrInfo) {
            $name = $constrInfo->name;
            return is_null($name) ? false :
                    strcmp($name, $this->getForeignKeyName($detailTableName, $detailFieldName, $masterTableName)) == 0;
        }
        return false;
    }

}
