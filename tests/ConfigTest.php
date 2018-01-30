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
            'parameters' => [
                'downloadUrl' => null,
            ],
            'processors' => [
                'before' => [],
                'after' => [],
            ],
            'image_parameters' => [],
            'action' => 'run',
        ], $config->getData());
    }
}
