<?php

declare(strict_types=1);

namespace Keboola\HttpExtractor\Tests\Code;

use Keboola\HttpExtractor\Code\Builder;
use Keboola\HttpExtractor\HttpExtractorComponent;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    public function testStringToDate(): void
    {
        $builder = new Builder(HttpExtractorComponent::ALLOWED_PLACEHOLDER_METHODS);

        $expected = date('Y-m-d', strtotime('-1 day'));

        $result = $builder->run((object) [
            'function' => 'strtodate',
            'args' => [
                '-1 day',
                'Y-m-d',
            ],
        ]);

        $this->assertSame($expected, $result);
    }

    public function testStringToDateError(): void
    {
        $builder = new Builder(HttpExtractorComponent::ALLOWED_PLACEHOLDER_METHODS);

        $this->expectException('Keboola\Code\Exception\UserScriptException');
        $this->expectExceptionMessage("Bad argument count for function 'strtodate'");

        $builder->run((object) [
            'function' => 'strtodate',
            'args' => [
                '-1 day',
            ],
        ]);
    }
}
