<?php namespace Friendica\Directory\_Config;

use Aura\Di\Config;
use Aura\Di\Container;

class Dev extends Config
{
    public function define(Container $di)
    {
        //DNS server to use during development.
        $di->values['react/react:dns-server'] = '8.8.8.8';

        //Configure the amount of job slots during development.
        $di->params['Friendica\Directory\Queue\Monitor']['jobSlots'] = 1;

        #TODO: Remove this debugging job.
        $di->params['Friendica\Directory\Queue\Monitor']['startingJob'] =
            $di->lazyNew('Friendica\Directory\Job\SayHello');

        //Enable multiline messages.
        $di->setters['Monolog\Handler\HandlerInterface'] = array(
            'setFormatter' => $di->lazyNew(
                'Monolog\Formatter\LineFormatter',
                array(null, null, true)
            ),
        );

        //Restrict logging to a certain level.
        $di->params['Monolog\Handler\StreamHandler'] = array(
            'level' => 100, #\Monolog\Logger::DEBUG
        );
    }

    public function modify(Container $di)
    {
        //Add memory usage, multiline stacktraces and StdErr output to our logger.
        $logger = $di->get('aura/project-kernel:logger');
        $logger->pushProcessor($di->newInstance('Monolog\Processor\MemoryUsageProcessor'));
        $logger->pushProcessor($di->newInstance('Friendica\Directory\Logger\MultilineStacktraceProcessor'));
        $logger->pushHandler($di->newInstance(
            'Monolog\Handler\StreamHandler',
            array('stream' => 'php://stderr')
        ));
    }
}
