<?php namespace Aiken\Parser;

/**
 * Class AikenParser
 *
 * Parse aiken file and return test item collection
 *
 * @package Aiken\Parser
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class AikenParser
{
    /**
     * Location of the file to parse into array
     *
     * @var string
     */
    private $file;

    /**
     * Location of the file to parse into array
     *
     * @var array
     */
    private $lines = array();

    /**
     * @var TestItemCollection
     */
    private $testItemCollection;

    /**
     * Location of the file to parse into array
     *
     * @var string
     */
    private $warnings;

    /**
     * AikenParser constructor.
     *
     * @param string $file
     */
    public function __construct($file = '')
    {
        $this->file = $file;
        if ($file != '') {
            $this->lines = file($this->file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        }
    }

    /**
     * Build the test item collection and return it
     *
     * @param $quiz
     * @return void
     */
    public function setQuiz($quiz)
    {
        $this->lines = preg_split('/\n|\r/', $quiz, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($this->lines as $ix => $line) {
            $this->lines[$ix] = ltrim($line, " \t\n\r\0\x0B");
        }
    }

    /**
     * Build the test item collection and return it
     *
     * @return string
     */
    public function getQuiz()
    {
        $quiz = "";
        foreach ($this->lines as $ix => $line) {
            $quiz .= $this->lines[$ix] . "\n";
        }
        return $quiz;
    }

    /**
     * Build the test item collection and return it
     *
     * @return string
     */
    public function getWarnings()
    {
        return $this->warnings;
    }

    /**
     * Build the test item collection and return it
     *
     * @return TestItemCollection
     * @throws \Exception
     */
    public function buildTestItemCollection($score = 1)
    {
        if (!$this->testItemCollection) {

            $this->testItemCollection = new TestItemCollection();
            $testItem = null;
            $lastitem = array();

            foreach ($this->lines as $line) {

                if ($this->isQuestion($line)) {
                    if ($testItem) {
                        $testItem->validateDoesNotHaveTooManyDistractors();
                        $testItem->validateHasCorrectAnswer();
                        $this->testItemCollection->append($testItem);
                    }
                    $testItem = new TestItem($line, $score);
                    $lastitem = array("type" => 'q', "obj" => $testItem);

                } elseif ($answer = $this->isDistractor($line)) {
                    if (!$testItem) {
                        $this->warnings .= "<p style='color:#ff0000;'>Η γραμμή: </p><p>$line</p><p style='color:#ff0000;'>είναι λανθασμένη.</p><br/>";
                    }
                    $distractor = $testItem->appendDistractor($answer);
                    if ($answer[1] == '*') {
                        $testItem->setCorrectAnswer($answer[2]);
                    }
                    $lastitem = array("type" => 'a', "obj" => $distractor);
                } else {
                    if (strlen($line) > 1) {
                        $this->warnings .= "<p style='color:#ff0000;'>Η γραμμή: </p><p>$line</p><p style='color:#ff0000;'>Δεν είναι ούτε ερώτηση ούτε απάντηση και ενώθηκε με το προηγούμενο κείμενο.</p><br/>";
                        if ($lastitem['type'] == 'q') {
                            ($lastitem['obj'])->addStem($line);
                        } else if ($lastitem['type'] == 'a') {
                            ($lastitem['obj'])->addAnswer($line);
                        }
                    }
                }
            }
            if ($testItem) {
                $testItem->validateDoesNotHaveTooManyDistractors();
                $testItem->validateHasCorrectAnswer();
                $this->testItemCollection->append($testItem);
            }
        }

        return $this->testItemCollection;
    }

    /**
     * Check if this line is the correct answer line
     *
     * @param string $line
     * @return bool
     */
    protected function isQuestion($line)
    {
        preg_match(TestItem::QUESTION_LINE_DETECTOR_SLUG, $line, $match);
        if ($match) {
            return true;
        }
        return false;
    }

    /**
     * Check if the line is a distractor
     *
     * @param string $line
     * @return bool
     */
    protected function isDistractor($line)
    {
        preg_match(TestItem::DISTRACTOR_LINE_DETECTOR_SLUG, $line, $match);
        if ($match) {
            return $match;
        }
        return false;
    }
}