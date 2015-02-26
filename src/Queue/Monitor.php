<?php namespace Friendica\Directory\Queue;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Friendica\Directory\Job\JobInterface;
use Friendica\Directory\React\LoopAwareInterface;
use Friendica\Directory\React\LoopAwareTrait;

class Monitor implements LoggerAwareInterface, LoopAwareInterface
{
    use LoggerAwareTrait;
    use LoopAwareTrait;

    protected $running;
    protected $startingJob;
    protected $jobSlots;

    public function getJobSlots()
    {
        return $this->jobSlots;
    }

    /**
     * Creates a new monitor instance, optionally bootstrapping it with a first job.
     * Note: this job is intended as a job that may insert new jobs.
     *
     * @param JobPriorityQueue  $queue
     * @param JobInterface|null $startingJob
     */
    public function __construct(JobPriorityQueue $queue, $jobSlots, JobInterface $startingJob = null)
    {
        $this->running = false;
        $this->queue = $queue;
        $this->startingJob = $startingJob;
        $this->jobSlots = (integer) $jobSlots;

        //Check the sanity of the configuration.
        if ($this->jobSlots <= 0) {
            throw new InvalidArugmentException("Must have at least one job slot.");
        }
    }

    /**
     * Queue a new Job that will eventually be run.
     *
     * @param JobInterface $job
     */
    public function queue(JobInterface $job)
    {
        $this->queue->insert($job);
    }

    /**
     * Starts the monitor, which will keep running jobs for every job-slot, when jobs are available.
     */
    public function __invoke()
    {
        // This is already an infinite loop, so we should not run more than once.
        if ($this->running) {
            return;
        }

        $this->logger->debug('Running the Job Queue Monitor');

        if (isset($this->startingJob)) {
            $this->queue($this->startingJob);
        }

        for ($i = 0; $i < $this->jobSlots; $i++) {
            $this->runNextJob();
        }
    }

    protected function runNextJob()
    {
        $jobName = null;

        return $this->queue->extract()
            ->then(function ($job) use (&$jobName) {
                $jobName = get_class($job);

                return $job->__invoke();
            })
            ->then(null, function ($e) use ($jobName) {
                $this->onFailedJob($jobName, $e);
            })
            ->done(function () {return $this->runNextJob();});
    }

    protected function onFailedJob($name, $e)
    {
        $error = sprintf('%s: "%s" at %s line %s', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine());
        $this->logger->error('Uncaught exception during job "{name}":'."\n\t".$error, ['name' => $name, 'exception' => $e]);
    }
}
