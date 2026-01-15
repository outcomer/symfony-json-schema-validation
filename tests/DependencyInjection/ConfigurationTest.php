<?php
/**
 * This file is part of the Outcomer Symfony Validation package.
 *
 * (c) David Evdoshchenko <773021792e@gmail.com>
 *
 * @package Outcomer\Validation
 */

declare(strict_types=1);

namespace Outcomer\ValidationBundle\Tests\DependencyInjection;

use Outcomer\ValidationBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

/**
 * Test suite for Configuration
 */
final class ConfigurationTest extends TestCase
{
	private Configuration $configuration;
	private Processor $processor;

	protected function setUp(): void
	{
		$this->configuration = new Configuration();
		$this->processor = new Processor();
	}

	public function testDefaultConfiguration(): void
	{
		$config = $this->processor->processConfiguration($this->configuration, []);

		$this->assertSame('%kernel.project_dir%/config/validation/schemas', $config['schemas_path']);
		$this->assertSame('https://outcomer.dev', $config['schema_domain']);
		$this->assertIsArray($config['filters']);
		$this->assertEmpty($config['filters']);
	}

	public function testCustomConfiguration(): void
	{
		$configs = [
			'outcomer_validation' => [
				'schemas_path' => '/custom/path',
				'schema_domain' => 'https://custom.domain',
				'filters' => [
					'uuid' => 'App\Filter\UuidFilter',
				],
			],
		];

		$config = $this->processor->processConfiguration($this->configuration, $configs);

		$this->assertSame('/custom/path', $config['schemas_path']);
		$this->assertSame('https://custom.domain', $config['schema_domain']);
		$this->assertArrayHasKey('uuid', $config['filters']);
		$this->assertSame(['class' => 'App\Filter\UuidFilter'], $config['filters']['uuid']);
	}

	public function testFiltersNormalization(): void
	{
		$configs = [
			'outcomer_validation' => [
				'filters' => [
					'uuid' => 'App\Filter\UuidFilter',
				],
			],
		];

		$config = $this->processor->processConfiguration($this->configuration, $configs);

		$this->assertIsArray($config['filters']['uuid']);
		$this->assertArrayHasKey('class', $config['filters']['uuid']);
		$this->assertSame('App\Filter\UuidFilter', $config['filters']['uuid']['class']);
	}
}
