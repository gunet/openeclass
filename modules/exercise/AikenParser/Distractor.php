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
