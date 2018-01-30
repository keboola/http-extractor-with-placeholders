<?php

declare(strict_types=1);

namespace Keboola\HttpExtractor;

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testCreateFromFile(): void
    {
        $configFilePath = __DIR__ . '/fixtures/config.json';

        $config = Config::fromFile($configFilePath);

        $this->assertSame([
            'downloadUrlBase' => 'http://google.com/',
            'downloadUrlPath' => 'favicon.ico',
            'saveAs' => null,
        ], $config->getData());
    }

    public function testCreateFromArray(): void
    {
        $configArray = [
            'downloadUrlBase' => 'http://google.com/',
            'downloadUrlPath' => 'favicon.ico',
        ];
        $config = Config::fromArray($configArray);
        $this->assertSame([
            'downloadUrlBase' => 'http://google.com/',
            'downloadUrlPath' => 'favicon.ico',
            'saveAs' => null,
        ], $config->getData());
    }

    public function testCustomGetters(): void
    {
        $configArray = [
            'downloadUrlBase' => 'http://google.com/',
            'downloadUrlPath' => 'favicon.ico',
            'saveAs' => 'favicon-local.ico',
        ];
        $config = Config::fromArray($configArray);

        $this->assertSame('http://google.com/', $config->getDownloadUrlBase());
    }
}
