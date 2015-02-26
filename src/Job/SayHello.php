<?php namespace Friendica\Directory\Job;

use React\HttpClient\Client;
use Friendica\Directory\React\ReactUtilsAwareInterface;
use Friendica\Directory\React\ReactUtilsAwareTrait;

class SayHello extends AbstractJob implements ReactUtilsAwareInterface
{
    use ReactUtilsAwareTrait;

    protected static $priority = JobPriorities::MAINTENANCE;

    protected $httpClient;
    protected $baseUrl;

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    public function setBaseUrl($value)
    {
        $this->baseUrl = $value;
    }

    public function getHttpClient()
    {
        return $this->httpClient;
    }

    public function setHttpClient(Client $value)
    {
        $this->httpClient = $value;
    }

    public function run()
    {
        $deferred = $this->newDeferred();
        $request = $this->httpClient->request('GET', $this->baseUrl.'friendica/json');
        $request->on('response', function ($response) use ($deferred) {
            $buffer = '';
            $response->on('data', function ($raw) use (&$buffer) {
                $buffer .= $raw;
            });
            $response->on('end', function () use (&$buffer, $deferred) {
                $data = json_decode($buffer, true);
                echo 'Hello '.$data['site_name'].'!'.PHP_EOL;
                $deferred->resolve();
            });
        });
        $request->end();

        return $deferred->promise();
    }
}
