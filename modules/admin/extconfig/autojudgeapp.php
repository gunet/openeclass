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

require_once 'genericrequiredparam.php';

abstract class AutojudgeApp extends ExtApp {

    public function __construct() {
        parent::__construct();
        foreach($this->getConfigFields() as $curField => $curLabel) {
            $this->registerParam(new GenericParam($this->getName(), $curLabel, $curField));
        }
    }

    public function getLongDescription() {
        return '<p>Ο αυτόματος κριτής είναι ένα εργαλείο που επιτρέπει την αυτόματη διόρθωση προγραμματιστικών εργασιών. Πιο συγκεκριμένα, μέσω του εργαλείου ο καθηγητής μπορεί να ορίσει σενάρια που περιλαμβάνουν input και output βάσει των οποίων οι αναρτώμενες εργασίες βαθμολογούνται αυτόματα.</p><p>Το συγκεκριμένο υποσύστημα αυτό συνδέεται με την υπηρεσία '.$this->getServiceURL().', <b>'.(!$this->supportsInput() ? 'δεν' : '').'υποστηρίζει μεταβλητά δεδομένα εισόδου</b> και περιλαμβάνει τις εξής γλώσσες: <b>'.implode(',', array_keys($this->getSupportedLanguages())).'</b></p><p>Προσοχή: Σε περίπτωση που υπάρχουν περισσότερες από μια υπηρεσίες αυτόματου κριτή ενεργοποιημένες χρησιμοποιείται μόνο η πρώτη.</p>';
    }

    public function getShortDescription() {
        return '<p>Ο αυτόματος κριτής είναι ένα εργαλείο που επιτρέπει την αυτόματη διόρθωση προγραμματιστικών εργασιών. Πιο συγκεκριμένα, μέσω του εργαλείου ο καθηγητής μπορεί να ορίσει σενάρια που περιλαμβάνουν input και output βάσει των οποίων οι αναρτώμενες εργασίες βαθμολογούνται αυτόματα.</p><p>Το συγκεκριμένο υποσύστημα αυτό συνδέεται με την υπηρεσία '.$this->getServiceURL().', <b>'.(!$this->supportsInput() ? 'δεν ' : '').'υποστηρίζει μεταβλητά δεδομένα εισόδου</b> και περιλαμβάνει τις εξής γλώσσες: <b>'.implode(',', array_keys($this->getSupportedLanguages())).'</b></p><p>Προσοχή: Σε περίπτωση που υπάρχουν περισσότερες από μια υπηρεσίες αυτόματου κριτή ενεργοποιημένες χρησιμοποιείται μόνο η πρώτη.</p>';
    }

    public function getDisplayName() {
        return $this->getServiceURL();
    }

    public static function getAutojudge() {
        $firstAutojudge = null;
        foreach(ExtAppManager::getApps() as $curApp) {
            if(strpos(get_class($curApp), 'Autojudge') !== false) {
                $firstAutojudge = $curApp;
                if($curApp->isEnabled()) {
                    return $curApp;
                }
            }
        }
        if(!$firstAutojudge) { throw new \Exception('Error! No autojudge connectors defined!'); }
        return $firstAutojudge;
    }

    protected static function getAutoJudgeApp($classname) {
        foreach(ExtAppManager::getApps() as $curApp) {
            if(strpos(get_class($curApp), $classname) !== false) {
                return $curApp;
            }
        }
        return null;
    }

    abstract public function compile(AutoJudgeConnectorInput $input);

    abstract public function getConfigFields();

    abstract public function getSupportedLanguages();

    abstract public function supportsInput();

    abstract public function getServiceURL();
}

interface AutoJudgeConnector {
    public function compile(AutoJudgeConnectorInput $input);

    public function getConfigFields();

    public function getName();

    public function getSupportedLanguages();

    public function supportsInput();
}

class AutoJudgeConnectorResult {
    public $compileStatus;

    public $output;

    const COMPILE_STATUS_OK = 'OK';
}

class AutoJudgeConnectorInput {
    public $input;

    public $code;

    public $lang;
}