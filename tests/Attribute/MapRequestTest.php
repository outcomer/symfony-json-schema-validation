<?php
/**
 * This file is part of the Outcomer Symfony Validation package.
 *
 * (c) David Evdoshchenko <773021792e@gmail.com>
 *
 * @package Outcomer\Validation
 */

declare(strict_types=1);

namespace Outcomer\ValidationBundle\Tests\Attribute;

use Outcomer\ValidationBundle\ArgumentResolver\MapRequestResolver;
use Outcomer\ValidationBundle\Attribute\MapRequest;
use PHPUnit\Framework\TestCase;

/**
 * Test suite for MapRequest attribute
 */
final class MapRequestTest extends TestCase
{
	public function testMapRequestWithDefaultParameters(): void
	{
		$attribute = new MapRequest('user-create.json');

		$this->assertSame('user-create.json', $attribute->schema);
		$this->assertTrue($attribute->die);
	}

	public function testMapRequestWithCustomParameters(): void
	{
		$attribute = new MapRequest('user-update.json', MapRequestResolver::class, false);

		$this->assertSame('user-update.json', $attribute->schema);
		$this->assertFalse($attribute->die);
	}

	public function testMapRequestIsAttribute(): void
	{
		$reflection = new \ReflectionClass(MapRequest::class);
		$attributes = $reflection->getAttributes(\Attribute::class);

		$this->assertNotEmpty($attributes);
	}
}
