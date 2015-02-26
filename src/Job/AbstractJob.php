<?php namespace Friendica\Directory\Job;

use Exception;
use RuntimeException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Friendica\Directory\React\PromiseAwareInterface;
use Friendica\Directory\React\PromiseAwareTrait;
use Friendica\Directory\React\LoopAwareInterface;
use Friendica\Directory\React\LoopAwareTrait;
use React\Promise\PromiseInterface;

/**
 * A Job that runs a certain non-blocking task.
 */
abstract class AbstractJob implements JobInterface, LoggerAwareInterface, PromiseAwareInterface, LoopAwareInterface
{
    use LoggerAwareTrait;
    use PromiseAwareTrait;
    use LoopAwareTrait;

    /**
     * A priority value that will be used to order jobs.
     *
     * @var int
     */
    protected static $priority;

    /**
     * Runs the non-blocking task.
     *
     * @return mixed
     */
    abstract protected function run();

    /**
     * Gets the priority value of this job.
     *
     * @return integer
     */
    public function getPriority()
    {
        if (!isset(static::$priority)) {
            throw new RuntimeException("No priority has been set for job: ".get_class($this));
        }

        return static::$priority;
    }

    /**
     * Runs the non-blocking task.
     *
     * @return Promise
     */
    public function __invoke()
    {
        $this->logger->info('Running job', [
            'priority' => $this->getPriority(),
            'name' => get_class($this),
        ]);

        $deferred = $this->newDeferred();

        try {
            $output = $this->run();
            if ($output instanceof PromiseInterface) {
                $output->then(function ($output) use ($deferred) {
                    $deferred->resolve($output);
                });
            }
        } catch (Exception $ex) {
            $deferred->reject($ex);
        }

        return $deferred->promise();
    }
}
