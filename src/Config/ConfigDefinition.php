<?php

declare(strict_types=1);

namespace Keboola\HttpExtractor\Config;

use function is_numeric;
use Keboola\Component\Config\BaseConfigDefinition;
use Keboola\HttpExtractor\HttpExtractorComponent;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class ConfigDefinition extends BaseConfigDefinition
{
    protected function getParametersDefinition(): ArrayNodeDefinition
    {
        $builder = new TreeBuilder();
        /** @var ArrayNodeDefinition $parametersNode */
        $parametersNode = $builder->root('parameters');
        // @formatter:off
        /** @noinspection NullPointerExceptionInspection */
        $parametersNode
            ->children()
                ->scalarNode('baseUrl')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('path')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('maxRedirects')
                    ->validate()
                        ->ifTrue(function ($value) {
                            return !is_numeric($value) || $value < 0;
                        })
                        ->thenInvalid('Max redirects must be positive integer')
                ->end()
            ->end()
            ->arrayNode("placeholders")
                ->useAttributeAsKey("name")
                    ->prototype('array')
                        ->children()
                            ->enumNode("function")
                                ->values(HttpExtractorComponent::ALLOWED_PLACEHOLDER_METHODS)
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->arrayNode("args")
                                ->scalarPrototype()
                                ->defaultValue([])
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->defaultValue([])
            ->end()

        ;
        // @formatter:on
        return $parametersNode;
    }
}
