<?php
/**
 * This file is part of the Outcomer Symfony Validation package.
 *
 * (c) David Evdoshchenko <773021792e@gmail.com>
 *
 * @package Outcomer\Validation
 */

declare(strict_types=1);

namespace Outcomer\ValidationBundle\Tests;

use Outcomer\ValidationBundle\DependencyInjection\OutcomerValidationExtension;
use Outcomer\ValidationBundle\OutcomerValidationBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * Test suite for OutcomerValidationBundle
 */
final class OutcomerValidationBundleTest extends TestCase
{
	public function testGetContainerExtension(): void
	{
		$bundle = new OutcomerValidationBundle();
		$extension = $bundle->getContainerExtension();

		$this->assertInstanceOf(ExtensionInterface::class, $extension);
		$this->assertInstanceOf(OutcomerValidationExtension::class, $extension);
	}

	public function testBundleCanBeInstantiated(): void
	{
		$bundle = new OutcomerValidationBundle();

		$this->assertInstanceOf(OutcomerValidationBundle::class, $bundle);
	}
}
