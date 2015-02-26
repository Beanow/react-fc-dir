<?php namespace Friendica\Directory\_Config;

use Aura\Di\Config;
use Aura\Di\Container;

class Common extends Config
{
    public function define(Container $di)
    {
        $this->defineLogger($di);
        $this->defineReactServices($di);
        $this->defineKernel($di);

        $di->setters['Friendica\Directory\Job\SayHello'] = array(
            'setBaseUrl' => 'https://fc.oscp.info/',
            'setHttpClient' => $di->lazyGet('react/react:http-client'),
        );
    }

    public function defineKernel(Container $di)
    {
        $di->set('friendica/directory:job-queue-monitor', $di->lazyNew('Friendica\Directory\Queue\Monitor'));

        $di->params['Friendica\Directory\Kernel\ReactApplication'] = array(
            'monitor' => $di->lazyGet('friendica/directory:job-queue-monitor'),
            'signalTimeout' => $di->lazyValue('friendica/directory:kernel-signal-timeout'),
        );

        $di->params['Friendica\Directory\Queue\Monitor'] = array(
            'queue' => $di->lazyNew('Friendica\Directory\Queue\JobPriorityQueue'),
            'startingJob' => $di->lazyNew('Friendica\Directory\Job\SayHello'),
        );

        $di->params['Friendica\Directory\Queue\JobPriorityQueue'] = array(
            'queue' => $di->lazyNew('\SplPriorityQueue'),
        );

        //The default time to loop through signals.
        //Keep in mind, the closer to 0 this setting is the more CPU it will use.
        $di->values['friendica/directory:kernel-signal-timeout'] = 1.2;
    }
    public function defineReactServices(Container $di)
    {
        $di->set('react/react:event-loop', $di->lazy(function () {
            return \React\EventLoop\Factory::create();
        }));

        $di->set('react/react:dns', $di->lazy(function () use ($di) {
            return (new \React\Dns\Resolver\Factory())->createCached(
                $di->lazyValue('react/react:dns-server')->__invoke(),
                $di->get('react/react:event-loop')
            );
        }));

        $di->set('react/react:http-client', $di->lazy(function () use ($di) {
            return (new \React\HttpClient\Factory())->create(
                $di->get('react/react:event-loop'),
                $di->get('react/react:dns')
            );
        }));

        //Default DNS server to use.
        $di->values['react/react:dns-server'] = '8.8.8.8';

        $di->setters['Friendica\Directory\React\PromiseAwareInterface'] = array(
            'setDeferredFactory' => $di->newFactory('React\Promise\Deferred'),
        );

        $di->setters['Friendica\Directory\React\LoopAwareInterface'] = array(
            'setLoop' => $di->lazyGet('react/react:event-loop'),
        );

        $di->setters['Friendica\Directory\React\ReactUtilsAwareInterface'] = array(
            'setReactUtils' => $di->lazyNew('Friendica\Directory\React\ReactUtils'),
        );
    }

    public function defineLogger(Container $di)
    {
        $di->set('aura/project-kernel:logger', $di->lazyNew('Monolog\Logger'));

        $di->params['Monolog\Logger'] = array(
            'name' => 'Friendica::Directory',
        );

        $di->setters['Psr\Log\LoggerAwareInterface'] = array(
            'setLogger' => $di->get('aura/project-kernel:logger'),
        );
    }

    public function modify(Container $di)
    {
        $this->modifyLogger($di);
    }

    protected function modifyLogger(Container $di)
    {
        $logger = $di->get('aura/project-kernel:logger');

        //Writes to a file based on the current env.
        $project = $di->get('project');
        $mode = $project->getMode();
        $file = $project->getPath("tmp/log/{$mode}.log");
        $streamHandler = $di->newInstance(
            'Monolog\Handler\StreamHandler',
            array('stream' => $file)
        );
        $logger->pushHandler($streamHandler);

        //Add PSR-3 formatting support.
        $logger->pushProcessor($di->newInstance('Monolog\Processor\PsrLogMessageProcessor'));

        //Log PHP (fatal) errors and uncaught exceptions.
        \Monolog\ErrorHandler::register($logger);
    }
}
