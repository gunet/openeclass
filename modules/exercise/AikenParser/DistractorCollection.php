<?php namespace Aiken\Parser;

use Aiken\Parser\Contracts\Arrayable;

/**
 * Class DistractorCollection
 *
 * A collection of distractors
 *
 * @package Aiken\Parser
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class DistractorCollection implements Arrayable
{
    /**
     * @var Distractor[]
     */
    protected $distractors = [];

    /**
     * Append distractor to collection
     *
     * @param Distractor $distractor
     * @return $this
     */
    public function append(Distractor $distractor)
    {
        $this->distractors[] = $distractor;
        return $distractor;
    }

    /**
     * Return object as array of values
     *
     * @return array
     */
    public function toArray()
    {
        $data = [];

        foreach ($this->distractors as $distractor) {
            $data[] = array(
                "index" => $distractor->key,
                "answer" => $distractor->value,
                "weighting" => $distractor->weight,
                "isCorrect" => $distractor->isCorrect?'yes':'no');
        }

        return $data;
    }

    /**
     * Return object as array of values
     *
     * @return string
     */
    public function toHTML()
    {
        $data = '<table width="100%">';

        foreach ($this->distractors as $distractor) {
            $data .= '<tr style="border-bottom: 1px solid #d8d8d8;">'.
                "<th>ix:</th><td>".($distractor->isCorrect?("<b>".$distractor->key."</b>"):$distractor->key)."</td>".
                "<th>ans:</th><td width='80%'>".
                    ($distractor->isCorrect?"<b>".htmlentities($distractor->value).
                        "</b>":htmlentities($distractor->value))."</td>".
                "<th>pt:</th><td style='text-align:right'>".($distractor->isCorrect?("<b>".$distractor->weight."</b>"):
                    $distractor->weight)."</td>".
                /*"<th>correct:</th><td>".($distractor->isCorrect?'yes':'no')."</td>".*/
                "</tr>";
        }
        $data .= '</table>';

        return $data;
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


