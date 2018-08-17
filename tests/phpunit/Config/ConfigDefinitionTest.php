<?php

declare(strict_types=1);

namespace Keboola\HttpExtractor\Tests\Config;

use Keboola\HttpExtractor\Config\ConfigDefinition;
use Keboola\HttpExtractor\HttpExtractorComponent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class ConfigDefinitionTest extends TestCase
{

    /**
     * @dataProvider provideValidConfigs
     */
    public function testValidConfigDefinition(array $inputConfig, array $expectedConfig): void
    {
        $definition = new ConfigDefinition();
        $processor = new Processor();

        $processedConfig = $processor->processConfiguration($definition, [$inputConfig]);

        $this->assertSame($expectedConfig, $processedConfig);
    }

    /**
     * @return mixed[][]
     */
    public function provideValidConfigs(): array
    {
        return [
            'minimal config' => [
                [
                    'parameters' => [
                        'baseUrl' => 'http://www.google.com',
                        'path' => 'path',
                    ],
                ],
                [
                    'parameters' => [
                        'baseUrl' => 'http://www.google.com',
                        'path' => 'path',
                        'placeholders' => [],
                    ],
                ],
            ],
            'placeholders with name attr' => [
                [
                    'parameters' => [
                        'baseUrl' => 'http://www.google.com',
                        'path' => 'path',
                        'placeholders' => [
                            [
                                'name' => 'NOW',
                                'function' => 'time',
                            ],
                        ],
                    ],
                ],
                [
                    'parameters' => [
                        'baseUrl' => 'http://www.google.com',
                        'path' => 'path',
                        'placeholders' => [
                            'NOW' => [
                                'function' => 'time',
                                'args' => [],
                            ],
                        ],
                    ],
                ],
            ],
            'placeholders as assoc. array' => [
                [
                    'parameters' => [
                        'baseUrl' => 'http://www.google.com',
                        'path' => 'path',
                        'placeholders' => [
                            'NOW' => [
                                'function' => 'time',
                                'args' => [],
                            ],
                        ],
                    ],
                ],
                [
                    'parameters' => [
                        'baseUrl' => 'http://www.google.com',
                        'path' => 'path',
                        'placeholders' => [
                            'NOW' => [
                                'function' => 'time',
                                'args' => [],
                            ],
                        ],
                    ],
                ],
            ],
            'placeholders with args' => [
                [
                    'parameters' => [
                        'baseUrl' => 'http://www.google.com',
                        'path' => 'path',
                        'placeholders' => [
                            [
                                'name' => 'NOW',
                                'function' => 'time',
                                'args' => ['value'],
                            ],
                        ],
                    ],
                ],
                [
                    'parameters' => [
                        'baseUrl' => 'http://www.google.com',
                        'path' => 'path',
                        'placeholders' => [
                            'NOW' => [
                                'function' => 'time',
                                'args' => ['value'],
                            ],
                        ],
                    ],
                ],
            ],
            'placeholders with srttodate' => [
                [
                    'parameters' => [
                        'baseUrl' => 'http://www.google.com',
                        'path' => 'path',
                        'placeholders' => [
                            [
                                'name' => 'NOW',
                                'function' => 'strtodate',
                                'args' => ['value', 'value2'],
                            ],
                        ],
                    ],
                ],
                [
                    'parameters' => [
                        'baseUrl' => 'http://www.google.com',
                        'path' => 'path',
                        'placeholders' => [
                            'NOW' => [
                                'function' => 'strtodate',
                                'args' => ['value', 'value2'],
                            ],
                        ],
                    ],
                ],
            ],
            'placeholders with assoc. args' => [
                [
                    'parameters' => [
                        'baseUrl' => 'http://www.google.com',
                        'path' => 'path',
                        'placeholders' => [
                            [
                                'name' => 'NOW',
                                'function' => 'time',
                                'args' => [
                                    '#cryptedProperty' => 'value',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'parameters' => [
                        'baseUrl' => 'http://www.google.com',
                        'path' => 'path',
                        'placeholders' => [
                            'NOW' => [
                                'function' => 'time',
                                'args' => [
                                    '#cryptedProperty' => 'value',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideInvalidConfigs
     */
    public function testInvalidConfigDefinition(
        array $inputConfig,
        string $expectedExceptionClass,
        string $expectedExceptionMessage
    ): void {
        $definition = new ConfigDefinition();
        $processor = new Processor();

        $this->expectException($expectedExceptionClass);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $processor->processConfiguration($definition, [$inputConfig]);
    }

    /**
     * @return mixed[][]
     */
    public function provideInvalidConfigs(): array
    {
        return [
            'empty parameters' => [
                [
                    'parameters' => [],
                ],
                InvalidConfigurationException::class,
                'The child node "baseUrl" at path "root.parameters" must be configured.',
            ],
            'missing url base' => [
                [
                    'parameters' => [
                        'path' => 'path',
                    ],
                ],
                InvalidConfigurationException::class,
                'The child node "baseUrl" at path "root.parameters" must be configured.',
            ],
            'missing url path' => [
                [
                    'parameters' => [
                        'baseUrl' => 'path',
                    ],
                ],
                InvalidConfigurationException::class,
                'The child node "path" at path "root.parameters" must be configured.',
            ],
            'unknown option' => [
                [
                    'parameters' => [
                        'baseUrl' => 'http://www.google.com',
                        'path' => 'path',
                        'other' => false,
                    ],
                ],
                InvalidConfigurationException::class,
                'Unrecognized option "other" under "root.parameters"',
            ],
            'invalid max redirects' => [
                [
                    'parameters' => [
                        'baseUrl' => 'http://www.google.com',
                        'path' => 'path',
                        'maxRedirects' => '',
                    ],
                ],
                InvalidConfigurationException::class,
                'Invalid configuration for path "root.parameters.maxRedirects": ' .
                'Max redirects must be positive integer',
            ],
            'placeholders without name' => [
                [
                    'parameters' => [
                        'baseUrl' => 'http://www.google.com',
                        'path' => 'path',
                        'placeholders' => [[]],
                    ],
                ],
                InvalidConfigurationException::class,
                'The attribute "name" must be set for path',
            ],
            'placeholders without function' => [
                [
                    'parameters' => [
                        'baseUrl' => 'http://www.google.com',
                        'path' => 'path',
                        'placeholders' => [[
                            'name' => 'MY_PLACEHOLDER',
                        ]],
                    ],
                ],
                InvalidConfigurationException::class,
                'The child node "function" at path "root.parameters.placeholders.',
            ],
            'placeholders invalid function' => [
                [
                    'parameters' => [
                        'baseUrl' => 'http://www.google.com',
                        'path' => 'path',
                        'placeholders' => [
                            'MY_PLACEHOLDER' => [
                                'function' => 'unlink',
                            ],
                        ],
                    ],
                ],
                InvalidConfigurationException::class,
                'Permissible values: "' . implode('", "', HttpExtractorComponent::ALLOWED_PLACEHOLDER_METHODS) . '"',
            ],
            'placeholders invalid args' => [
                [
                    'parameters' => [
                        'baseUrl' => 'http://www.google.com',
                        'path' => 'path',
                        'placeholders' => [
                            'MY_PLACEHOLDER' => [
                                'function' => 'md5',
                                'args' => 'some',
                            ],
                        ],
                    ],
                ],
                InvalidConfigurationException::class,
                '.args". Expected array, but got string',
            ],
        ];
    }
}
