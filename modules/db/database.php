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

define("DB_TYPE", "MYSQL");

require_once 'dbhelper.php';
require_once realpath(dirname(__FILE__)) . '/../admin/debug.php';

final class DBResult {

    var $lastInsertID;
    var $affectedRows;

    public function __construct($lastInsertID, $affectedRows) {
        $this->lastInsertID = $lastInsertID;
        $this->affectedRows = $affectedRows;
    }

}

final class Database {

    private static $REQ_LASTID = 1;
    private static $REQ_OBJECT = 2;
    private static $REQ_ARRAY = 3;
    private static $REQ_FUNCTION = 4;
    private static $CORE_DB_TAG = "::CORE DB::";

    /**
     *
     * @var Hash map to store various databases
     */
    private static $dbs = array();

    /**
     * Get a database with a specific name. It might throw an exception, if the database does not exist.
     * @param type $dbase The name of the database. Could be missing (or null) for the default database.
     * @return Database|null The database object
     */
    public static function get($dbase = null) {
        global $mysqlServer, $mysqlUser, $mysqlPassword, $mysqlMainDb;
        if (is_null($dbase)) {
            $dbase = $mysqlMainDb;
        }
        if (array_key_exists($dbase, self::$dbs)) {
            $db = self::$dbs[$dbase];
        } else {
            $db = new Database($mysqlServer, $dbase, $mysqlUser, $mysqlPassword);   // might throw exception
            self::$dbs[$dbase] = $db;
        }
        return $db;
    }

    /**
     * Remove a database from the cache. Since for every database, a new database connection is
     * established (and this conenction is cached), with this option it is possible to remove
     * from cache a connection that is known that it is no longer needed.
     *
     * In case the database is needed again, a new conenction will be created, thus it is safe to
     * use this function.
     * @param Database $dbase The name of the database
     */
    public static function forget($dbase) {
        unset(self::$dbs[$dbase]);
    }

    /**
     * Get a Database object which does not point to a specific database. 
     * This is useful to perform DBMS queries, such as creating/destroying a database.
     * @return Database|null The database object
     */
    public static function core() {
        return Database::get(Database::$CORE_DB_TAG);
    }

    /**
     * @var PDO
     */
    private $dbh;
    private $attribute;

    /**
     * @param string $server
     * @param string $dbase
     * @param string $user
     * @param string $password
     */
    public function __construct($server, $dbase, $user, $password) {
        try {
            $params = null;
            $databasename = $dbase == Database::$CORE_DB_TAG ? "" : (";dbname=" . $dbase);
            switch (DB_TYPE) {
                case "POSTGRES":
                    $dsn = "pgsql:host=" . $server . $databasename;
                    break;
                case "MYSQL":
                    $dsn = 'mysql:host=' . $server . ';charset=utf8' . $databasename;
                    $params = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
                    break;
                default :
                    Debug::message("Unknown database backend: " . DB_TYPE, Debug::ALWAYS);
            }
            $this->dbh = new PDO($dsn, $user, $password, $params);
        } catch (PDOException $e) {
            Debug::message("Error while initialize database: " . $e->getMessage(), Debug::CRITICAL);
            throw new Exception($e->getMessage());
        }
    }

    /** This is a transactional version of queryNT.
     * 
     * @param string $statement a non-empty string forming the prepared function: use ? for variables bound to this statement
     * @param function $callback_error An *optional* argument with a callback function in case error trapping is required.
     * If the second argument is a callable, then this argument is handled as an error callback. If it is any other type (including null), then it is passed as a binding argument.
     * @param anytype $argument... A variable argument list of each binded argument
     * @return DBResult Result of this query
     */
    public function query($statement) {
        return $this->queryI(func_get_args(), true);
    }

    /** This is a non-transactional version of query.
     * 
     * @param string $statement a non-empty string forming the prepared function: use ? for variables bound to this statement
     * @param function $callback_error An *optional* argument with a callback function in case error trapping is required.
     * If the second argument is a callable, then this argument is handled as an error callback. If it is any other type (including null), then it is passed as a binding argument.
     * @param anytype $argument... A variable argument list of each binded argument
     * @deprecated
     * @return int Last inserted ID
     */
    private function queryNT($statement) {
        return $this->queryI(func_get_args(), false);
    }

    private function queryI($args, $transactional) {
        $statement = $args[0];
        $offset = 1;
        $callback_error = $this->findErrorCallback($args, $offset);
        return $this->queryImpl($statement, $transactional, null, $callback_error, Database::$REQ_LASTID, array_slice($args, $offset));
    }

    /** This is a transactional version of queryFuncNT.
     *
     * @param string $statement a non-empty string forming the prepared function: use ? for variables bound to this statement
     * @param function $callback_function A function which as first argument gets an object constructed by each row of the result set. Can be null
     * @param function $callback_error An *optional* argument with a callback function in case error trapping is required.
     * If the third argument is a callable, then this argument is handled as an error callback. If it is any other type (including null), then it is passed as a binding argument.
     * @param anytype $argument... A variable argument list of each binded argument
     */
    public function queryFunc($statement, $callback_function) {
        return $this->queryFuncI(func_get_args(), true);
    }

    /** This is a non-transactional version of queryFunc.
     *
     * @param string $statement a non-empty string forming the prepared function: use ? for variables bound to this statement
     * @param function $callback_function A function which as first argument gets an object constructed by each row of the result set. Can be null
     * @param function $callback_error An *optional* argument with a callback function in case error trapping is required.
     * If the third argument is a callable, then this argument is handled as an error callback. If it is any other type (including null), then it is passed as a binding argument.
     * @deprecated
     * @param anytype $argument... A variable argument list of each binded argument
     */
    private function queryFuncNT($statement, $callback_function) {
        return $this->queryFuncI(func_get_args(), false);
    }

    private function queryFuncI($args, $transactional) {
        $statement = $args[0];
        $callback_function = $args[1];
        $offset = 2;
        $callback_error = $this->findErrorCallback($args, $offset);
        return $this->queryImpl($statement, $transactional, $callback_function, $callback_error, Database::$REQ_FUNCTION, array_slice($args, $offset));
    }

    /** This is a transactional version of queryArrayNT.
     *
     * @param string $statement a non-empty string forming the prepared function: use ? for variables bound to this statement
     * @param function $callback_error An *optional* argument with a callback function in case error trapping is required.
     * If the second argument is a callable, then this argument is handled as an error callback. If it is any other type (including null), then it is passed as a binding argument.
     * @param anytype $argument... A variable argument list of each binded argument
     * @return array An array of all objects as a result of this statement
     */
    public function queryArray($statement) {
        return $this->queryArrayI(func_get_args(), true);
    }

    /** This is a non-transactional version of queryArray.
     *
     * @param string $statement a non-empty string forming the prepared function: use ? for variables bound to this statement
     * @param function $callback_error An *optional* argument with a callback function in case error trapping is required.
     * If the second argument is a callable, then this argument is handled as an error callback. If it is any other type (including null), then it is passed as a binding argument.
     * @param anytype $argument... A variable argument list of each binded argument
     * @deprecated
     * @return array An array of all objects as a result of this statement
     */
    private function queryArrayNT($statement) {
        return $this->queryArrayI(func_get_args(), false);
    }

    private function queryArrayI($args, $transactional) {
        $statement = $args[0];
        $offset = 1;
        $callback_error = $this->findErrorCallback($args, $offset);
        return $this->queryImpl($statement, $transactional, null, $callback_error, Database::$REQ_ARRAY, array_slice($args, $offset));
    }

    /** This is a transactional version of querySingleNT.
     *
     * @param string $statement a non-empty string forming the prepared function: use ? for variables bound to this statement
     * @param function $callback_error An *optional* argument with a callback function in case error trapping is required.
     * If the second argument is a callable, then this argument is handled as an error callback. If it is any other type (including null), then it is passed as a binding argument.
     * @param anytype $argument... A variable argument list of each binded argument
     * @return array A single object as a result of this statement
     */
    public function querySingle($statement) {
        return $this->querySingleI(func_get_args(), true);
    }

    /** This is a non-transactional version of querySingle.
     *
     * @param string $statement a non-empty string forming the prepared function: use ? for variables bound to this statement
     * @param function $callback_error An *optional* argument with a callback function in case error trapping is required.
     * If the second argument is a callable, then this argument is handled as an error callback. If it is any other type (including null), then it is passed as a binding argument.
     * @param anytype $argument... A variable argument list of each binded argument
     * @deprecated
     * @return array A single object as a result of this statement
     */
    private function querySingleNT($statement) {
        return $this->querySingleI(func_get_args(), false);
    }

    private function querySingleI($args, $transactional) {
        $statement = $args[0];
        $offset = 1;
        $callback_error = $this->findErrorCallback($args, $offset);
        return $this->queryImpl($statement, $transactional, null, $callback_error, Database::$REQ_OBJECT, array_slice($args, $offset));
    }

    private function findErrorCallback($arguments, &$offset) {
        if ($arguments && count($arguments) > $offset) {
            $funcTest = $arguments[$offset];
            if (is_object($funcTest) && is_callable($funcTest)) {
                $offset++;
                return $funcTest;
            }
        }
        return null;
    }

    private function queryImpl($statement, $isTransactional, $callback_fetch, $callback_error, $requestType, $variables) {
        $init_time = microtime();
        $backtrace_entry = debug_backtrace();
        $backtrace_info = $backtrace_entry[2];

        $isTransactional &=!$this->dbh->inTransaction();
        if (is_null($statement) || !is_string($statement) || empty($statement))
            return $this->errorFound($callback_error, $isTransactional, "First parameter of query should be a non-empty string; found " . gettype($statement), null, $statement, $init_time, $backtrace_info);
        if (!is_callable($callback_fetch) && !is_null($callback_fetch))
            return $this->errorFound($callback_error, $isTransactional, "Second parameter of query should be a closure, or null; found " . gettype($callback_fetch), null, $statement, $init_time, $backtrace_info);

        /* Start transaction, if required */
        if ($isTransactional && !$this->dbh->beginTransaction())
            return $this->errorFound($callback_error, $isTransactional, "Unable to initialize transaction", null, $statement, $init_time, $backtrace_info);

        /* flatten parameter array */
        $flatten = array();
        $it = new RecursiveIteratorIterator(new RecursiveArrayIterator($variables));
        foreach ($it as $v) {
            $flatten[] = $v;
        }
        $variables = $flatten;

        /* Construct actual statement */
        $statement_parts = explode("?", $statement);
        $variable_size = count($statement_parts) - 1;   // Do not take into account first part
        $variable_types = array($variable_size);
        if ($variable_size < count($variables)) {
            Database::dbg("Provided variables are more than the required statement fields", $statement, $init_time, $backtrace_info, Debug::ERROR);
        } else if ($variable_size > count($variables)) {
            Database::dbg("Provided variables are <b>less</b> than the required statement fields", $statement, $init_time, $backtrace_info, Debug::CRITICAL);
            die();
        }
        // Type safe input parameters
        $warning_parts = "";
        for ($i = 0; $i < $variable_size; $i++) {
            $entry = $statement_parts[$i + 1];
            $first = substr($entry, 0, 1);
            $value = $variables[$i];
            if (is_null($value)) {
                if ($first === "d" || $first === "b" || $first === "f" || $first === "t" || $first === "s") {
                    $statement_parts[$i + 1] = substr($entry, 1);
                }
                $type = PDO::PARAM_NULL;
            } else if ($first === "d") {   // Decimal
                $statement_parts[$i + 1] = substr($entry, 1);
                $value = intval($value);
                $type = PDO::PARAM_INT;
            } else if ($first === "b") {    // Boolean
                $statement_parts[$i + 1] = substr($entry, 1);
                $value = (($value) ? true : false);
                $type = PDO::PARAM_BOOL;
            } else if ($first === "f") {    // Floating point
                $statement_parts[$i + 1] = substr($entry, 1);
                $value = floatval($value);
                $type = PDO::PARAM_STR;
            } else if ($first === "t") {    // Date/time
                $statement_parts[$i + 1] = substr($entry, 1);
                $type = PDO::PARAM_STR;
            } else if ($first === "s") {    // String
                $statement_parts[$i + 1] = substr($entry, 1);
                $type = PDO::PARAM_STR;
            } else {    // Auto-guess
                $warning_parts .= ", ";
                if (is_int($value)) {
                    $type = PDO::PARAM_INT;
                    $warning_parts .="int_" . $i . "=" . $value;
                } else if (is_bool($value)) {
                    $type = PDO::PARAM_BOOL;
                    $warning_parts .="bool_" . $i . "=" . $value;
                } else {
                    $type = PDO::PARAM_STR;
                    $warning_parts .="string_" . $i . "=\"" . $value . "\"";
                }
            }
            $variables[$i] = $value;
            $variable_types[$i] = $type;
        }
        if (strlen($warning_parts) > 0) {
            $warning_parts = substr($warning_parts, 1);
            Database::dbg("Warning: parts [ $warning_parts ] of query '$statement' have undefined type.", $statement, $init_time, $backtrace_info, Debug::ERROR);
        }
        $statement = implode("?", $statement_parts);

        /* Prepare statement */
        $stm = $this->dbh->prepare($statement);
        if (!$stm)
            return $this->errorFound($callback_error, $isTransactional, "Unable to prepare statement", $this->dbh->errorInfo(), $statement, $init_time, $backtrace_info);

        /* Bind values - with type safety and '?' notation  */
        for ($i = 0; $i < $variable_size; $i++) {
            if (!$stm->bindValue($i + 1, $variables[$i], $variable_types[$i]))
                $this->errorFound($callback_error, $isTransactional, "Unable to bind boolean parameter'$variables[$i]' with type $variable_types[$i] at location #$i", $stm->errorInfo(), $statement, $init_time, $backtrace_info, false);
        }

        /* Execute statement */
        if (!$stm->execute())
            return $this->errorFound($callback_error, $isTransactional, "Unable to execute statement", $stm->errorInfo(), $statement, $init_time, $backtrace_info);

        /* fetch results */
        $result = null;
        if ($requestType == Database::$REQ_OBJECT) {
            $result = $stm->fetch(PDO::FETCH_OBJ);
            if ($result != false && !is_object($result))
                return $this->errorFound($callback_error, $isTransactional, "Unable to fetch single result as object", $stm->errorInfo(), $statement, $init_time, $backtrace_info);
        } else if ($requestType == Database::$REQ_ARRAY) {
            $result = $stm->fetchAll(PDO::FETCH_OBJ);
            if (!is_array($result))
                return $this->errorFound($callback_error, $isTransactional, "Unable to fetch all results as objects", $stm->errorInfo(), $statement, $init_time, $backtrace_info);
        } else if ($requestType == Database::$REQ_LASTID) {
            $result = new DBResult($this->dbh->lastInsertId(), $stm->rowCount());
        } else if ($requestType == Database::$REQ_FUNCTION) {
            $func_affected_rows = 0;
            if ($callback_fetch)
                while (TRUE)
                    if (!($res = $stm->fetch(PDO::FETCH_OBJ)) || $callback_fetch($res)) {
                        $result = new DBResult(0, $func_affected_rows);
                        break;
                    } else
                        $func_affected_rows++;
        }
        /* Close transaction, if required */
        if ($isTransactional)
            $this->dbh->commit();
        Database::dbg("Succesfully performed query", $statement, $init_time, null, Debug::INFO);
        return $result;
    }

    /**
     * Safely start a transaction and clean up if an error was produced. If this 
     * method is called when we are already in a transaction, no new transaction
     * will be started.
     * @param callable $function The code inside this function will be called
     *  when the database is in transactional state
     * @throws Exception if an error occured while running; the transaction will
     *  be rolled back if required
     */
    public function transaction($function) {
        if (is_callable($function)) {
            $needsTransaction = !$this->dbh->inTransaction();
            if ($needsTransaction)
                $this->dbh->beginTransaction();
            try {
                $function();
            } catch (Exception $ex) {
                if ($needsTransaction)
                    $this->dbh->rollBack();
                throw $ex;
            }
            if ($needsTransaction)
                $this->dbh->commit();
        } else {
            $backtrace_entry = debug_backtrace();
            $backtrace_info = $backtrace_entry[1];
            Debug::message("Transaction needs a function as parameter", $backtrace_info['file'], $backtrace_info['line']);
        }
    }

    /**
     * Return an object with server information
     * @return DatabaseAttributes attributes object
     */
    public function attributes() {
        if (!$this->attribute) {
            $this->attribute = new DatabaseAttributes($this->dbh);
        }
        return $this->attribute;
    }

    private function errorFound($callback_error, $isTransactional, $error_msg, $pdo_error, $statement, $init_time, $backtrace_info, $close_transaction = true) {
        if ($callback_error && is_callable($callback_error))
            $callback_error($error_msg);
        if ($close_transaction && $isTransactional && $this->dbh->inTransaction())
            $this->dbh->rollBack();
        if ($pdo_error)
            $pdo_error_text = ":\"" . $pdo_error[2] . "\", sqlstate:\"" . $pdo_error[1] . "\", errornum:\"" . $pdo_error[0] . "\"";
        else
            $pdo_error_text = "";
        Database::dbg($error_msg . $pdo_error_text, $statement, $init_time, $backtrace_info);
        return null;
    }

    /**
     * Private function to call master Debug object
     */
    private static function dbg($message, $statement, $init_time, $backtrace_info, $level = Debug::ERROR) {
        $statement_pure = str_replace(array("\n", "\r", "\t"), array("", "", ""), $statement);
        Debug::message($message . ", \tstatement:\"$statement_pure\", \telapsed:" . (microtime() - $init_time), $level, $backtrace_info['file'], $backtrace_info['line']);
    }

}

class DatabaseAttributes {

    private $dbh;

    function __construct($dbh) {
        $this->dbh = $dbh;
    }

    function autocommit() {
        return $this->dbh->getAttribute(PDO::ATTR_AUTOCOMMIT);
    }

    function textCase() {
        return $this->dbh->getAttribute(PDO::ATTR_CASE);
    }

    function clientVersion() {
        return $this->dbh->getAttribute(PDO::ATTR_CLIENT_VERSION);
    }

    function connectionStatus() {
        return $this->dbh->getAttribute(PDO::ATTR_CONNECTION_STATUS);
    }

    function driverName() {
        return $this->dbh->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    function errorMode() {
        return $this->dbh->getAttribute(PDO::ATTR_ERRMODE);
    }

    function oracleNulls() {
        return $this->dbh->getAttribute(PDO::ATTR_ORACLE_NULLS);
    }

    function persistent() {
        return $this->dbh->getAttribute(PDO::ATTR_PERSISTENT);
    }

    function prefech() {
        return $this->dbh->getAttribute(PDO::ATTR_PREFETCH);
    }

    function serverInfo() {
        return $this->dbh->getAttribute(PDO::ATTR_SERVER_INFO);
    }

    function serverVersion() {
        return $this->dbh->getAttribute(PDO::ATTR_SERVER_VERSION);
    }

    function timeout() {
        return $this->dbh->getAttribute(PDO::ATTR_TIMEOUT);
    }

}
