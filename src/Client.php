<?php

declare(strict_types=1);

namespace Keboola\HttpExtractor;

use GuzzleHttp\HandlerStack;
use Keboola\HttpExtractor\Client\ExponentialDelay;
use Keboola\HttpExtractor\Client\RetryDecider;

class Client extends \GuzzleHttp\Client
{
    public function __construct(
        array $config = []
    ) {
        if (!isset($config['handler'])) {
            $config['handler'] = HandlerStack::create();
        }
        $stack = $config['handler'];
        $stack->push(\GuzzleHttp\Middleware::retry(new RetryDecider(), new ExponentialDelay()));

        parent::__construct($config);
    }
}
