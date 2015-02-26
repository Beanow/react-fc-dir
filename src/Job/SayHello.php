<?php namespace Friendica\Directory\Job;

use Friendica\Directory\React\ReactUtilsAwareInterface;
use Friendica\Directory\React\ReactUtilsAwareTrait;

class SayHello extends AbstractJob implements ReactUtilsAwareInterface
{
    use ReactUtilsAwareTrait;

    protected static $priority = JobPriorities::MAINTENANCE;

    public function run()
    {
        echo "Hello";

        return $this->reactUtils->sleep(0.7)->then(function () {
            echo " non-blocking";

            return $this->reactUtils->sleep(0.7)->then(function () {
                echo " world!".PHP_EOL;
                throw new \LogicException("Broken!");
            });
        });
    }
}
