<?php

/**
 * This file is part of the Outcomer Symfony Validation package.
 *
 * (c) David Evdoshchenko <773021792e@gmail.com>
 *
 * @package Outcomer\Validation
 */

declare(strict_types=1);

namespace Outcomer\ValidationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration tree for the validation bundle
 */
final class Configuration implements ConfigurationInterface
{
    /**
     * Builds the configuration tree definition
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('outcomer_validation');
        $rootNode    = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('schemas_path')
                    ->defaultValue('%kernel.project_dir%/config/validation/schemas')
                    ->info('Path to JSON Schema files')
                    ->setDeprecated('outcomer/symfony-json-schema-validation', '4.0', 'The "%node%" option is deprecated, use "schemas" instead.')
                ->end()
                ->scalarNode('schema_domain')
                    ->defaultValue('https://outcomer.dev')
                    ->info('Domain for auto-generated schema IDs')
                    ->setDeprecated('outcomer/symfony-json-schema-validation', '4.0', 'The "%node%" option is deprecated, use "schemas" instead.')
                ->end()
                ->arrayNode('schemas')
                    ->info('Path/domain pairs to register - each is resolved the same way schemas_path/schema_domain used to be, for schemas that live in different directories')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('path')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('domain')->isRequired()->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                ->end()
                ->booleanNode('auto_cast_query')
                    ->defaultTrue()
                    ->info('Automatically cast numeric/boolean strings in query parameters before validation')
                ->end()
                ->booleanNode('auto_cast_path')
                    ->defaultTrue()
                    ->info('Automatically cast numeric/boolean strings in path parameters before validation')
                ->end()
                ->arrayNode('filters')
                    ->info('Filter name to class mapping')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->beforeNormalization()
                            ->ifString()
                            ->then(function ($v) {
                                return ['class' => $v];
                            })
                        ->end()
                        ->children()
                            ->scalarNode('class')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
