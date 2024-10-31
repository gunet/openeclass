<?php namespace Aiken\Parser;

use Aiken\Parser\Contracts\Arrayable;
use Exception;

const MAXDISTRACTORS = 6;
/**
 * Class TestItem
 *
 * Class representing a single test item
 *
 * @package Aiken\Parser
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class TestItem implements Arrayable
{
    const KEY = 'index';
    const TYPE = 'type';
    const ID = 'id';
    const STEM = 'question';
    const CORRECT_ANSWER = 'correct';
    const DISTRACTORS = 'answers';
    const SCORE = 'score';

    const QUESTION_LINE_DETECTOR_SLUG = '/^\s*(\d+)(?:[\)\.])\s+(.*)/ui';
    const SCORE_CAPTURE_SLUG = '/(?:\(\s*(?:score|μονάδες|βαθμός|points|weight):\s*(\d+|\d*\.\d+)\s*\)$)/ui';
    const DISTRACTOR_LINE_DETECTOR_SLUG = '/^\s*(\*)?([a-fA-Fα-ωΑ-Ω])(?:[\)\.])\s*(.*)/ui';

    const MIN_STEM_SIZE = 12;
    const MIN_ANSWER_QTY = 2;

    /**
     * Test item stem
     *
     * @var string
     */
    protected $id;

    /**
     * Test item stem
     *
     * @var string
     */
    protected $key;

    /**
     * Test item stem
     *
     * @var string
     */
    protected $stem;

    /**
     * Test item stem
     *
     * @var integer
     */
    protected $score;

    /**
     * Test item distractor collection
     *
     * @var DistractorCollection
     */
    private $distractors;

    /**
     * Test item correct answer
     *
     * @var string
     */
    protected $correctAnswer;

    /**
     * TestItem constructor.
     *
     * @param $line
     */
    public function __construct($line, $score =1)
    {
        $this->id = uniqid();
        $this->score = $score;
        preg_match(TestItem::QUESTION_LINE_DETECTOR_SLUG, $line, $match);
        if ($match) {
            $this->key = $match[1];
            $this->stem = $match[2];
        }
        preg_match(TestItem::SCORE_CAPTURE_SLUG, $line, $match);
        if ($match) {
            $this->score = $match[1] ?: 1;
        }
    }

    /**
     * Get collection of distractors object
     *
     * @return DistractorCollection
     */
    protected function getDistractorCollection()
    {
        if (!$this->distractors) {
            $this->distractors = new DistractorCollection();
        }
        return $this->distractors;
    }

    /**
     * Append a distractor to the array
     *
     * @param array $matcher
     * @return $this
     */
    public function appendDistractor(array $matcher)
    {
        $key = $matcher[2];
        $value = $matcher[3];
        $iscorrect = $matcher[1]=='*';
        $score = $iscorrect?$this->score:0;
        $distractor = new Distractor($key, $value, $iscorrect, $score);
        return $this->getDistractorCollection()->append($distractor);
    }

    /**
     * Set the correct answer
     *
     * @param $text
     * @return void
     */
    public function addStem($text)
    {
        $this->stem .= $text;
    }

        /**
     * Set the correct answer
     *
     * @param $answerKey
     * @return $this
     * @throws Exception
     */
    public function setCorrectAnswer($answerKey)
    {
        if (!empty($this->correctAnswer)) {
            throw new Exception('<p style="color:#ff0000;">Υπάρχει πρόβλημα με την επόμενη ερώτηση:</p>' .
                $this->key . ' '. htmlentities($this->stem) .
                '<p style="color:#ff0000;">Φαίνεται να υπάρχουν περισσότερες από μία σωστές απαντήσεις.</p>');
        } else {
            $this->correctAnswer = $answerKey;
        }
        return $this;
    }

    /**
     * Validate the test item has everything it needs
     *
     * @throws Exception
     */
    public function validate()
    {
        if (count($this->getDistractorCollection()->toArray()) < TestItem::MIN_ANSWER_QTY) {
            throw new Exception('<p style="color:red;">Υπάρχει πρόβλημα με την επόμενη ερώτηση:</p>' .
                $this->key . ' '. htmlentities($this->stem) .
                '<p style="color:red;">Βρέθηκαν πολύ λίγες απαντήσεις.</p>');
        }

        if (strlen($this->stem) < TestItem::MIN_STEM_SIZE) {
            throw new Exception('<p style="color:red;">Υπάρχει πρόβλημα με την επόμενη ερώτηση:</p>' .
                $this->key .' '. htmlentities($this->stem) .
                '<p style="color:red;">Η ερώτηση έχει λιγότερο κείμενο από '.TestItem::MIN_STEM_SIZE.' χαρακτήρες</p>');
        }

        if (empty($this->correctAnswer)) {
            throw new Exception('<p style="color:red;">Υπάρχει πρόβλημα με την επόμενη ερώτηση:</p>' .
                $this->key . ' '. htmlentities($this->stem) .
                '<p style="color:red;">Δεν έχει σημειωθεί η σωστή απάντηση.</p>');
        }
    }

    /**
     * Validate that a test item does not have too many distractors
     *
     * @throws Exception
     */
    public function validateDoesNotHaveTooManyDistractors()
    {
        if (count($this->getDistractorCollection()->toArray()) > MAXDISTRACTORS) {
            throw new Exception('<p style="color:red;">Υπάρχει πρόβλημα με την επόμενη ερώτηση:</p>' .
                $this->key . ' '. htmlentities($this->stem) .
                '<p style="color:red;">Η ερώτηση φαίνετια να έχει υπερβολικά πολλές απαντήσεις.</p>');
        }
    }

    /**
     * Validate that a test item does not have too many distractors
     *
     * @throws Exception
     */
    public function validateHasCorrectAnswer()
    {
        if (empty($this->correctAnswer)) {
            throw new Exception('<p style="color:red;">Υπάρχει πρόβλημα με την επόμενη ερώτηση:</p>' .
                $this->key . ' '. htmlentities($this->stem) .
                '<p style="color:red;">Δεν έχει σημειωθεί η σωστή απάντηση.</p>');
        }
    }

    /**
     * Return object as array
     *
     * @return array
     * @throws Exception
     */
    public function toArray()
    {
        $this->validate();

        return [
            self::ID => $this->id,
            self::KEY => $this->key,
            self::STEM => $this->stem,
            self::CORRECT_ANSWER => $this->correctAnswer,
            self::SCORE => $this->score,
            self::TYPE => 1,
            self::DISTRACTORS => $this->getDistractorCollection()->toArray(),
        ];
    }

    /**
     * Return object as array
     *
     * @return string
     * @throws Exception
     */
    public function toHTML()
    {
        $this->validate();
        return '<table width="100%" cellpadding="2" cellspacing="2">'.
            "<tr><th width='10%'>".self::KEY."</th><td>{$this->key}</td></tr>".
            "<tr><th>".self::ID."</th><td>{$this->id}</td></tr>".
            "<tr><th>".self::CORRECT_ANSWER."</th><td>{$this->correctAnswer}</td></tr>".
            "<tr><th>".self::SCORE."</th><td>{$this->score}</td></tr>".
            "<tr><th>".self::TYPE."</th><td>1</td></tr>".
            "<tr><th>".self::STEM."</th><td colspan='9'>".htmlentities($this->stem)."</td></tr>".
            "<tr><td colspan='2' class='anstitle'>Answers</td></tr>".
            "<tr><td colspan='2' style='/*border: 1px solid #bbbbbb;*/'>".$this->getDistractorCollection()->toHTML()."</td></tr>".
            "</table>";
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


