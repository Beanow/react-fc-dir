<?php namespace Friendica\Directory\_Config;

use Aura\Di\Config;
use Aura\Di\Container;

class Prod extends Config
{
    public function define(Container $di)
    {
        //Restrict logging to a certain level.
        $di->params['Monolog\Handler\StreamHandler'] = array(
            'level' => 400, #\Monolog\Logger::ERROR
        );

        //DNS server to use in productions.
        $di->values['react/react:dns-server'] = '8.8.8.8';

        //Configure the amount of job slots in production.
        $di->params['Friendica\Directory\Queue\Monitor']['jobSlots'] = 50;
    }

    public function modify(Container $di)
    {
    }
}
