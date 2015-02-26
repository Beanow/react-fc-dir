<?php namespace Friendica\Directory\React;

use React\EventLoop\LoopInterface;

/**
 * Basic Implementation of LoopAwareInterface.
 */
trait LoopAwareTrait
{
    /** @var LoopInterface */
    protected $loop;

    /**
     * Sets a Loop.
     *
     * @param LoopInterface $loop
     */
    public function setLoop(LoopInterface $loop)
    {
        $this->loop = $loop;
    }
}
