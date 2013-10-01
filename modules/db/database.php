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


require_once '../../config/config.php';

define("DB_TYPE", "MYSQL");

class Database {

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
            switch (DB_TYPE) {
                case "POSTGRES":
                    $dsn = "pgsql:host=" . $server . ';dbname=' . $dbase;
                    break;
                case "MYSQL":
                default :
                    $dsn = 'mysql:host=' . $server . ';dbname=' . $dbase . ';charset=utf8';
            }
            $this->dbh = new PDO($dsn, $user, $password);
            $this->isTransactional = true;
        } catch (PDOException $e) {
            $this->errorFound($e->getMessage());
        }
    }

    /**
     * @param boolean $transactional Set whether this database is transactional or not.
     */
    public function setTransactional($transactional) {
        $this->isTransactional = $transactional;
    }

    /**
     * @param string $statement a non-empty string forming the prepared function: use ? for all variables bound to this statement
     * @param anytype $argument... A variable argument list of each binded argument
     */
    public function query($statement) {
        $this->queryImpl($statement, null, false, array_slice(func_get_args(), 1));
    }

    /**
     * @param string $statement a non-empty string forming the prepared function: use ? for all variables bound to this statement
     * @param function $callback_function A function which as first argument gets an object constructed by each row of the result set. Can be null
     * @param anytype $argument... A variable argument list of each binded argument
     */
    public function queryFunc($statement, $callback_function) {
        $this->queryImpl($statement, $callback_function, false, array_slice(func_get_args(), 2));
    }

    /**
     * @param string $statement a non-empty string forming the prepared function: use ? for all variables bound to this statement
     * @param anytype $argument... A variable argument list of each binded argument
     * @return array An array of all objects as a result of this statement
     */
    public function queryArray($statement) {
        return $this->queryImpl($statement, null, true, array_slice(func_get_args(), 1));
    }

    private function queryImpl($statement, $function, $asArray, $variables) {
        // Get information if this is a transactional database as fast as possible
        $transactional = $this->isTransactional;

        if (!is_string($statement) || is_null($statement) || empty($statement)) {
            $this->errorFound("First parameter of query should be a non-empty string; found: " . $statement . " (" . gettype($statement) . ").");
            return;
        }
        if (!is_callable($function) && !is_null($function)) {
            $this->errorFound("Second parameter of query should be a closure, or null; found: " . $function . " (" . gettype($function) . ").");
            return;
        }

        /* Start transaction, if required */
        if ($transactional)
            $this->dbh->beginTransaction();
        /* Prepare statement */
        $stm = $this->dbh->prepare($statement);
        /* Bind values - use '?' notation  */
        $howmanyvalues = count($variables);
        for ($i = 1; $i <= $howmanyvalues; $i++) {
            $bound = $variables[$i - 1];
            if (is_bool($bound))
                $stm->bindValue($i, $bound, PDO::PARAM_BOOL);
            else if (is_int($bound))
                $stm->bindValue($i, $bound, PDO::PARAM_INT);
            else if (is_float($bound))
                $stm->bindValue($i, strval($bound), PDO::PARAM_STR);
            else if (is_string($bound))
                $stm->bindValue($i, $bound, PDO::PARAM_STR);
            else
                $stm->bindValue($i, $bound);
        }
        /* Execute statement */
        $stm->execute();
        /* fetch results */
        $result = null;
        if ($asArray)
            $result = $stm->fetchAll(PDO::FETCH_OBJ);
        else if (!is_null($function))
            while (TRUE)
                if (!($res = $stm->fetch(PDO::FETCH_OBJ)) || $function($res))
                    break;
        /* Close transaction, if required */
        if ($transactional)
            $this->dbh->commit();
        
        return $result;
    }

    private function errorFound($error) {
        print "Error!: " . $error . "<br/>";
    }

}

// Test database object
$d = new Database($mysqlServer, $mysqlMainDb, $mysqlUser, $mysqlPassword);

$d->queryFunc("SELECT * FROM course WHERE id >= ? LIMIT 10", function($course) {
            echo "A course with title " . $course->title . " and language " . $course->lang . "<p>";
        }, 1);

echo "<b>Now break after second element</b><p>";
$idx = 0;
$d->queryFunc("SELECT * FROM course WHERE id >= ? LIMIT 10", function($course) use(&$idx) {
            echo "A course with title " . $course->title . " and language " . $course->lang . "<p>";
            $idx++;
            if ($idx == 2)
                return true;
        }, 1);

echo "<b>Original size of query result is " . count($d->queryArray("SELECT * FROM course WHERE id >= ? LIMIT 10", 1)) . "</b><p>";

echo "<b>A query without being interested on result: should be blank </b>";
$d->query("SELECT * FROM course WHERE id >= ? LIMIT 10", 1);
