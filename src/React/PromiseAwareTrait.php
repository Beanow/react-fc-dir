<?php namespace Friendica\Directory\React;

use Aura\Di\InstanceFactory;

/**
 * Describes a promise-aware instance.
 */
trait PromiseAwareTrait
{
    private $deferredFactory;

    /**
     * Sets a InstanceFactory instance on the object.
     *
     * @param InstanceFactory $deferredFactory
     */
    public function setDeferredFactory(InstanceFactory $deferredFactory)
    {
        $this->deferredFactory = $deferredFactory;
    }

    /**
     * Creates a new deferred instance.
     *
     * @return Deferred
     */
    protected function newDeferred(callable $canceller = null)
    {
        return $this->deferredFactory->__invoke($canceller);
    }
}
