<?php namespace Friendica\Directory\Job;

/**
 * A Job that runs a certain non-blocking task.
 */
interface JobInterface
{
    /**
     * Gets the priority value of this job.
     *
     * @return integer
     */
    public function getPriority();

    /**
     * Runs the non-blocking task.
     *
     * @return Promise
     */
    public function __invoke();
}
