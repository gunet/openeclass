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
 * Eclass Caching Object.
 */
class FileCache {

    private $resource;
    private $cachefile;
    private $tempdir;
    private $prefix;
    private $ttl;

    /*
     * Constructor
     *
     * @param string $resource - Name of cached resource
     * @param int    $ttl      - Cache Time To Live
     */
    public function __construct($resource, $ttl) {
        $this->resource = $resource;
        $webDir = isset($GLOBALS['webDir'])? $GLOBALS['webDir']: getcwd();
        $this->prefix = substr(md5($webDir), 0, 8);
        $this->tempdir = defined('CACHE_DIR')? CACHE_DIR: sys_get_temp_dir();
        $this->cachefile = $this->tempdir . '/' . $this->prefix . '_' . $resource . '.cache';
        $this->ttl = $ttl;
        if (php_sapi_name() == 'cli') {
            $this->cachefile = null;
        }
    }

    /**
     * Get cached data
     *
     * @return mixed $ret - The current data in cache or false
     */
    public function get() {
        $ret = false;
        if (file_exists($this->cachefile) && time() - $this->ttl < filemtime($this->cachefile)) {
            $data = file_get_contents($this->cachefile);
            if ($data === false) return false;
            $ret = unserialize($data);
        }
        return $ret;
    }

    /**
     * Store cached data
     *
     * @param  mixed $data - Data to store in cache
     * @return bool        - true on success false on failure
     */
    public function store($data) {
        if (!$this->cachefile) {
            return false;
        }
        $tmpname = tempnam($this->tempdir, $this->resource);
        $fp = fopen($tmpname, 'w');
        if ($fp === false) return false;
        $r = fwrite($fp,serialize($data));
        fclose($fp);
        if ($r === false) {
            unlink($tmpname);
            return false;
        }
        return rename($tmpname, $this->cachefile);
    }

    /**
     * Clear cached data
     *
     * @return bool        - true on success false on failure
     */
    public function clear() {
        if (file_exists($this->cachefile))
            return unlink($this->cachefile);
        return true;
    }

}
