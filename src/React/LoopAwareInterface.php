<?php namespace Friendica\Directory\React;

use React\EventLoop\LoopInterface;

/**
 * Describes a loop-aware instance.
 */
interface LoopAwareInterface
{
    /**
     * Sets a Loop instance on the object.
     *
     * @param LoopInterface $Loop
     */
    public function setLoop(LoopInterface $loop);
}
