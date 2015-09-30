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

/**
 * Description of clouddriveparam
 *
 * @author teras
 */
class GenericParam extends ExtParam {

    private $app;

    public function __construct($app, $display, $name, $defaultValue = "", $type = ExtParam::TYPE_STRING) {
        $this->app = $app;
        parent::__construct($display, $name, $defaultValue, $type);
    }

    protected function retrieveValue() {
        $result = Database::get()->querySingle("SELECT `value` FROM `config` WHERE `key` = ?s", $this->persist_key());
        return $result ? $result->value : "";
    }

    public function persistValue() {
        $key = $this->persist_key();
        Database::get()->query("DELETE FROM `config` WHERE `key` = ?s", $key);
        Database::get()->querySingle("INSERT INTO `config` (`key`, `value`) VALUES (?s, ?s)", $key, $this->value());
    }

    private function persist_key() {
        return "ext_" . $this->app . "_" . $this->name();
    }

}
