<?php

declare(strict_types=1);

namespace Keboola\HttpExtractor;

use Keboola\Component\Config\BaseConfig;

class Config extends BaseConfig
{
    public function getBaseUrl(): string
    {
        return $this->getValue(['parameters', 'baseUrl']);
    }

    public function getPath(): string
    {
        return $this->getValue(['parameters', 'path']);
    }
}
