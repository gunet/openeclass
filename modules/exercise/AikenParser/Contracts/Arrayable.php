<?php namespace Aiken\Parser\Contracts;

/**
 * Interface Arrayable
 *
 * Arrayable interface
 *
 * @package Aiken\Parser\Contracts
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
interface Arrayable
{
    /**
     * Return object as array
     *
     * @return array
     */
    public function toArray();
}
