<?php namespace Aiken\Parser;

/**
 * Class Distractor
 *
 * Represent an individual distractor
 *
 * @package Aiken\Parser
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class Distractor
{
    /**
     * @var string
     */
    public $key;

    /**
     * @var string
     */
    public $value;

    /**
     * @var integer
     */
    public $weight;

    /**
     * @var bool
     */
    public $isCorrect;

    /**
     * Distractor constructor.
     *
     * @param $key
     * @param $value
     * @param $correct
     * @param $weight
     */
    public function __construct($key, $value, $correct, $weight)
    {
        $this->key = $key;
        $this->value = $value;
        $this->weight = $weight;
        $this->isCorrect = $correct;
    }

    /**
     * Set the correct answer
     *
     * @param $text
     * @return void
     */
    public function addAnswer($text)
    {
        $this->value .= $text;
    }


}
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


