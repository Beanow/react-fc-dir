<?php namespace Friendica\Directory\React;

use Aura\Di\InstanceFactory;

/**
 * Describes a promise-aware instance.
 */
interface PromiseAwareInterface
{
    /**
     * Sets a InstanceFactory instance on the object.
     *
     * @param InstanceFactory $deferredFactory
     */
    public function setDeferredFactory(InstanceFactory $deferredFactory);
}
