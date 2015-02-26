<?php namespace Friendica\Directory\React;

/**
 * Describes a promise-aware instance.
 */
trait ReactUtilsAwareTrait
{
    protected $reactUtils;

    /**
     * Sets a ReactUtils instance on the object.
     *
     * @param ReactUtils $reactUtils
     */
    public function setReactUtils(ReactUtils $reactUtils)
    {
        $this->reactUtils = $reactUtils;
    }
}
