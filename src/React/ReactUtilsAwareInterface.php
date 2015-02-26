<?php namespace Friendica\Directory\React;

/**
 * Describes a utils-aware instance.
 */
interface ReactUtilsAwareInterface
{
    /**
     * Sets a ReactUtils instance on the object.
     *
     * @param ReactUtils $reactUtils
     */
    public function setReactUtils(ReactUtils $reactUtils);
}
