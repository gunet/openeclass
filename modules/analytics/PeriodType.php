<?php

define('DAILY', 0);
define('WEEKLY', 1);
define('MONTHLY',2);
define('TOTAL', 3);


abstract class PeriodType {

    const periodType = array(
        DAILY => array('title' => 'Ημερήσιος Υπολογισμός'),
        WEEKLY => array('title' => 'Εβδομαδιαίος Υπολογισμός'),
        MONTHLY => array('title' => 'Μηνιαίος Υπολογισμός'), 
        TOTAL => array('title' => 'Συνολικός Υπολογισμός')
    );
}

