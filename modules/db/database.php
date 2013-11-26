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

class Database {

    /**
     *
     * @var Hash map to store various databases
     */
    private static $dbs = array();

    /**
     * Get a database on its name: this is a static method
     * @param type $dbase The name of the database. Could be missing (or null) for the default database.
     * @return \Database|null THe database object
     */
    public static function get($dbase = null) {
        global $mysqlServer, $mysqlUser, $mysqlPassword, $mysqlMainDb;
        if (is_null($dbase))
            $dbase = $mysqlMainDb;
        if (array_key_exists($dbase, self::$dbs)) {
            $db = self::$dbs[$dbase];
        } else {
            try {
                $db = new Database($mysqlServer, $dbase, $mysqlUser, $mysqlPassword);
                self::$dbs[$dbase] = $db;
            } catch (Exception $e) {
                return null;
            }
        }
        return $db;
    }

    /**
     * @var PDO
     */
    private $dbh;

    /**
     * @var boolean
     */
    private $isTransactional;

    /**
     * @param string $server
     * @param string $dbase
     * @param string $user
     * @param string $password
     */
    public function __construct($server, $dbase, $user, $password) {
        try {
            $params = null;
            switch (DB_TYPE) {
                case "POSTGRES":
                    $dsn = "pgsql:host=" . $server . ';dbname=' . $dbase;
                    break;
                case "MYSQL":
                    $dsn = 'mysql:host=' . $server . ';dbname=' . $dbase . ';charset=utf8';
                    $params = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
                    break;
                default :
                    Debug::message("Unknown database backend: " . DB_TYPE, Debug::ALWAYS);
            }
            $this->dbh = new PDO($dsn, $user, $password, $params);
            $this->setTransactional(true);
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @param boolean $transactional Set whether this database is transactional or not.
     */
    public function setTransactional($transactional) {
        $this->isTransactional = $transactional;
    }

    /**
     * @param string $statement a non-empty string forming the prepared function: use ? for variables bound to this statement
     * @param function $callback_error An *optional* argument with a callback function in case error trapping is required.
     * If the second argument is a callable, then this argument is handled as an error callback. If it is any other type (including null), then it is passed as a binding argument.
     * @param anytype $argument... A variable argument list of each binded argument
     */
    public function query($statement) {
        $offset = 1;
        $args = func_get_args();
        $callback_error = $this->findErrorCallback($args, $offset);
        $this->queryImpl($statement, null, $callback_error, 0, array_slice($args, $offset));
    }

    /**
     * @param string $statement a non-empty string forming the prepared function: use ? for variables bound to this statement
     * @param function $callback_function A function which as first argument gets an object constructed by each row of the result set. Can be null
     * @param function $callback_error An *optional* argument with a callback function in case error trapping is required.
     * If the third argument is a callable, then this argument is handled as an error callback. If it is any other type (including null), then it is passed as a binding argument.
     * @param anytype $argument... A variable argument list of each binded argument
     */
    public function queryFunc($statement, $callback_function) {
        $offset = 2;
        $args = func_get_args();
        $callback_error = $this->findErrorCallback($args, $offset);
        return $this->queryImpl($statement, $callback_function, $callback_error, 0, array_slice($args, $offset));
    }

    /**
     * @param string $statement a non-empty string forming the prepared function: use ? for variables bound to this statement
     * @param function $callback_error An *optional* argument with a callback function in case error trapping is required.
     * If the second argument is a callable, then this argument is handled as an error callback. If it is any other type (including null), then it is passed as a binding argument.
     * @param anytype $argument... A variable argument list of each binded argument
     * @return array An array of all objects as a result of this statement
     */
    public function queryArray($statement) {
        $offset = 1;
        $args = func_get_args();
        $callback_error = $this->findErrorCallback($args, $offset);
        return $this->queryImpl($statement, null, $callback_error, 2, array_slice($args, $offset));
    }

    /**
     * @param string $statement a non-empty string forming the prepared function: use ? for variables bound to this statement
     * @param function $callback_error An *optional* argument with a callback function in case error trapping is required.
     * If the second argument is a callable, then this argument is handled as an error callback. If it is any other type (including null), then it is passed as a binding argument.
     * @param anytype $argument... A variable argument list of each binded argument
     * @return array A single object as a result of this statement
     */
    public function querySingle($statement) {
        $offset = 1;
        $args = func_get_args();
        $callback_error = $this->findErrorCallback($args, $offset);
        return $this->queryImpl($statement, null, $callback_error, 1, array_slice($args, $offset));
    }

    private function findErrorCallback($arguments, &$offset) {
        if ($arguments && count($arguments) > $offset && is_callable($arguments[$offset])) {
            $func = $arguments[$offset];
            $offset++;
            return $func;
        }
        return null;
    }

    private function queryImpl($statement, $callback_fetch, $callback_error, $arrayType, $variables) {
        $init_time = microtime();
        if (is_null($statement) || !is_string($statement) || empty($statement))
            return $this->errorFound($callback_error, "First parameter of query should be a non-empty string; found " . gettype($statement), $statement, $init_time);
        if (!is_callable($callback_fetch) && !is_null($callback_fetch))
            return $this->errorFound($callback_error, "Second parameter of query should be a closure, or null; found " . gettype($callback_fetch), $statement, $init_time);

        /* Start transaction, if required */
        if ($this->isTransactional && !$this->dbh->beginTransaction())
            return $this->errorFound($callback_error, "Unable to initialize transaction", $statement, $init_time);

        /* Prepare statement */
        $stm = $this->dbh->prepare($statement);
        if (!$stm)
            return $this->errorFound($callback_error, "Unable to prepare statement", $statement, $init_time);

        /* Bind values - use '?' notation  */
        $howmanyvalues = count($variables);
        for ($i = 1; $i <= $howmanyvalues; $i++) {
            $bound = $variables[$i - 1];
            if (is_bool($bound)) {
                if (!$stm->bindValue($i, $bound, PDO::PARAM_BOOL))
                    $this->errorFound($callback_error, "Unable to bind boolean parameter '$bound' at location #" . $i, $statement, $init_time, false);
            }
            else if (is_int($bound)) {
                if (!$stm->bindValue($i, $bound, PDO::PARAM_INT))
                    $this->errorFound($callback_error, "Unable to bind integer parameter '$bound' at location #" . $i, $statement, $init_time, false);
            }
            else if (is_float($bound)) {
                if (!$stm->bindValue($i, strval($bound), PDO::PARAM_STR))
                    $this->errorFound($callback_error, "Unable to bind float parameter '$bound' at location #" . $i, $statement, $init_time, false);
            }
            else if (is_string($bound)) {
                if (!$stm->bindValue($i, $bound, PDO::PARAM_STR))
                    $this->errorFound($callback_error, "Unable to bind string parameter '$bound' at location #" . $i, $statement, $init_time, false);
            } else {
                if (!$stm->bindValue($i, $bound))
                    $this->errorFound($callback_error, "Unable to bind generic parameter '$bound' at location #" . $i, $statement, $init_time, false);
            }
        }
        /* Execute statement */
        if (!$stm->execute())
            return $this->errorFound($callback_error, "Unable to execute statement", $statement, $init_time);

        /* fetch results */
        $result = null;
        if ($arrayType == 1) {
            $result = $stm->fetch(PDO::FETCH_OBJ);
            if (!is_object($result))
                return $this->errorFound($callback_error, "Unable to fetch single result as object", $statement, $init_time);
        } else if ($arrayType == 2) {
            $result = $stm->fetchAll(PDO::FETCH_OBJ);
            if (!is_array($result))
                return $this->errorFound($callback_error, "Unable to fetch all results as objects", $statement, $init_time);
        } else if ($callback_fetch)
            while (TRUE)
                if (!($res = $stm->fetch(PDO::FETCH_OBJ)) || $callback_fetch($res))
                    break;
        /* Close transaction, if required */
        if ($this->isTransactional)
            $this->dbh->commit();
        Database::dbg("Succesfully performed query", $statement, $init_time, Debug::INFO);
        return $result;
    }

    /**
     * 
     * @return integer the last inserted id
     */
    public function lastInsertID() {
        return $this->dbh->lastInsertId();
    }

    private function errorFound($callback_error, $error_msg, $statement, $init_time, $close_transaction = true) {
        if ($callback_error && is_callable($callback_error))
            $callback_error($error_msg);
        if ($close_transaction && $this->isTransactional && $this->dbh->inTransaction())
            $this->dbh->rollBack();
        Database::dbg("Error: " . $error_msg, $statement, $init_time);
        return null;
    }

    /**
     * Private function to call master Debug object
     */
    private static function dbg($message, $statement, $init_time, $level = Debug::ERROR) {
        Debug::message($message . " [Statement='$statement' Elapsed='" . (microtime() - $init_time) . "]", $level);
    }

}
