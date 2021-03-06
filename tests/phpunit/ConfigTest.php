<?php

declare(strict_types=1);

namespace Keboola\HttpExtractor\Tests;

use Keboola\HttpExtractor\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testCustomGetters(): void
    {
        $configArray = [
            'parameters' => [
                'baseUrl' => 'http://google.com/',
                'path' => 'favicon.ico',
                'maxRedirects' => 6,
            ],
        ];
        $config = new Config($configArray, new Config\ConfigDefinition());

        $this->assertSame('http://google.com/', $config->getBaseUrl());
        $this->assertSame('favicon.ico', $config->getPath());
        $this->assertSame(['maxRedirects' => 6], $config->getClientOptions());
    }
    public function testOptionalBehaviour(): void
    {
        $configArray = [
            'parameters' => [
                'baseUrl' => 'http://google.com/',
                'path' => 'favicon.ico',
            ],
        ];
        $config = new Config($configArray, new Config\ConfigDefinition());

        $this->assertSame([], $config->getClientOptions());
    }
}
