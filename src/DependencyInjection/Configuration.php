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
				->end()
				->scalarNode('schema_domain')
					->defaultValue('https://outcomer.dev')
					->info('Domain for auto-generated schema IDs')
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
