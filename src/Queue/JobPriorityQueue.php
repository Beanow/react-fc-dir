<?php namespace Friendica\Directory\Queue;

use SplPriorityQueue;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Friendica\Directory\Job\JobInterface;
use Friendica\Directory\React\PromiseAwareInterface;
use Friendica\Directory\React\PromiseAwareTrait;
use Friendica\Directory\React\LoopAwareInterface;
use Friendica\Directory\React\LoopAwareTrait;
use React\Promise\Promise;

/**
 * A priority non-blocking queue specifically for handling Job objects.
 */
class JobPriorityQueue implements LoggerAwareInterface, PromiseAwareInterface, LoopAwareInterface
{
    use LoggerAwareTrait;
    use PromiseAwareTrait;
    use LoopAwareTrait;

    /**
     * The internal queue we will use for the prioritizing.
     *
     * @var SplPriorityQueue
     */
    protected $queue;

    /**
     * An array of Deferred objects that reqested an extract while no jobs were available.
     *
     * @var array
     */
    protected $waitingList;

    /**
     * Set to true if we have registered a nextTick operation to notify our waiting list of new inserts.
     *
     * @var boolean
     */
    protected $willNotify;

    /**
     * Creates a new instance.
     *
     * @param SplPriorityQueue $queue [description]
     */
    public function __construct(SplPriorityQueue $queue)
    {
        $this->willNotify = false;
        $this->waitingList = array();
        $this->queue = $queue;
        $this->queue->setExtractFlags(SplPriorityQueue::EXTR_DATA);
    }

    /**
     * Inserts a new Job, by priority in the queue.
     *
     * @param JobInterface $job
     */
    public function insert(JobInterface $job)
    {
        $this->queue->insert($job, $job->getPriority());
        $this->logger->debug('Inserted new job in queue', [
            'priority' => $job->getPriority(),
            'name' => get_class($job),
        ]);

        //If we have a waiting list that we are not already notifying. Notify them in a future tick.
        //This is so that inserts may first be buffered and prioritized in this tick.
        if (!empty($waitingList) && !$this->willNotify) {
            $this->willNotify = true;
            $this->loop->futureTick(array($this, 'notifyWaitingList'));
        }
    }

    /**
     * Eventually extracts a Job from the queue.
     * If there are currently no Jobs, it will wait for an insert.
     *
     * @return Promise
     */
    public function extract()
    {
        $deferred = $this->newDeferred();

        //If have jobs and we are not skipping the waiting list, immediately resolve.
        if (!$this->isEmpty() && empty($this->waitingList)) {
            $job = $this->_extract();
            $deferred->resolve($job);
        }

        //Otherwise step in line.
        else {
            array_push($this->waitingList, $deferred);
        }

        return $deferred->promise();
    }

    /**
     * Checks if the queue is empty.
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return $this->queue->isEmpty();
    }

    /**
     * The immediate extract call.
     *
     * @return Job
     */
    protected function _extract()
    {
        $job = $this->queue->extract();
        $this->logger->debug('Extracted job from queue', [
            'priority' => $job->getPriority(),
            'name' => get_class($job),
        ]);

        return $job;
    }

    /**
     * Notify the waiting list that there have been inserts.
     */
    protected function notifyWaitingList()
    {
        //As long as we have jobs and items in our waiting list, pair them together.
        while (!$this->isEmpty() && !empty($this->waitingList)) {
            $job = $this->_extract();
            $deferred = array_pop($this->waitingList);
            $deferred->resolve($job);
        }

        //We've done our notification now.
        $this->willNotify = false;
    }
}
