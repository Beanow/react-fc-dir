<?php namespace Friendica\Directory\Kernel;

use DateTime;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Friendica\Directory\React\LoopAwareInterface;
use Friendica\Directory\React\LoopAwareTrait;
use Friendica\Directory\Queue\Monitor as QueueMonitor;

class ReactApplication implements LoggerAwareInterface, LoopAwareInterface
{
    use LoggerAwareTrait;
    use LoopAwareTrait;

    protected $monitor;
    protected $signalTimeout;

    public function __construct(QueueMonitor $monitor, $signalTimeout)
    {
        $this->monitor = $monitor;
        $this->signalTimeout = (float) $signalTimeout;
    }

    public function __invoke()
    {
        $this->logger->debug('Running ReactApplication kernel');

        $start = new DateTime();
        $this->monitor->__invoke();
        $this->registerSignals();
        $this->loop->run();
        $end = new DateTime();

        $uptime = $start->diff($end);
        $this->logger->info('Event loop ended, application will exit', ['uptime' => $uptime->format('%ad %hh %im %ss')]);
    }

    protected function registerSignals()
    {
        if (function_exists('pcntl_signal')) {
            $handler = $this->createSignalHandler();
            pcntl_signal(SIGTERM, $handler);
            pcntl_signal(SIGINT, $handler);
            $this->signalLoop();
        }
    }

    protected function signalLoop()
    {
        if (function_exists('pcntl_signal_dispatch')) {
            pcntl_signal_dispatch();
            $this->loop->addTimer($this->signalTimeout, function () {
                $this->signalLoop();
            });
        }
    }

    protected function createSignalHandler()
    {
        return function ($signalNumber) {
            $this->loop->stop();
            $name = 'unknown';
            switch ($signalNumber) {
                case SIGTERM: $name = 'terminate'; break;
                case SIGINT: $name = 'interrupt'; break;
            }
            $this->logger->notice('Received {name} signal, stopping event loop', ['name' => $name, 'number' => $signalNumber]);
        };
    }
}
