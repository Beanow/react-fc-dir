<?php namespace Friendica\Directory\React;

/**
 * Some helper functions you might want to use.
 */
class ReactUtils implements PromiseAwareInterface, LoopAwareInterface
{
    use PromiseAwareTrait;
    use LoopAwareTrait;

    public function sleep($timeout)
    {
        $deferred = $this->newDeferred();
        $this->loop->addTimer($timeout, function () use ($deferred) {
            $deferred->resolve();
        });

        return $deferred->promise();
    }
}
