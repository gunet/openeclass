<?php namespace Aiken\Parser;

use Aiken\Parser\Contracts\Arrayable;

/**
 * Class TestItemCollection
 *
 * Collection of test item objects
 *
 * @package Aiken\Parser
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class TestItemCollection implements Arrayable
{
    /**
     * @var TestItem[]
     */
    protected $testItems = [];

    /**
     * Append a new test item to the test item collection
     *
     * @param TestItem $item
     * @return $this
     */
    public function append(TestItem $item)
    {
        $this->testItems[] = $item;
        return $this;
    }

    /**
     * Return object as array
     *
     * @return array
     * @throws \Exception
     */
    public function toArray()
    {
        $data = [];

        foreach ($this->testItems as $testItem) {
            $data[] = $testItem->toArray();
        }

        return $data;
    }

    /**
     * Return object as array
     *
     * @return array
     * @throws \Exception
     */
    public function toHTML()
    {
        $data = '<table width="100%">';

        foreach ($this->testItems as $testItem) {
            $data .= "<tr><td style='border: 2px solid #9c4c67;padding: 2px;border-radius: 6px;'>".$testItem->toHTML()."</td></tr>".
                "<tr><td><span style='height: 20px;'>&nbsp;</span></td></tr>";;
        }
        $data .= "</table>";

        return $data;
    }
}
