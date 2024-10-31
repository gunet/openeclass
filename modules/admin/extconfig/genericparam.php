<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
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
        $result = get_config($this->persist_key());
        return $result ? $result : '';
    }

    public function persistValue() {
        $key = $this->persist_key();
        set_config($key, $this->value());
    }

    private function persist_key() {
        return "ext_" . $this->app . "_" . $this->name();
    }

}
