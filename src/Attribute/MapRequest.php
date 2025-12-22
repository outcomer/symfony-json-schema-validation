<?php
/**
 * This file is part of the Outcomer Symfony Validation package.
 *
 * (c) David Evdoshchenko <773021792e@gmail.com>
 *
 * @package Outcomer\Validation
 */

declare(strict_types=1);

namespace Outcomer\ValidationBundle\Attribute;

use Outcomer\ValidationBundle\ArgumentResolver\MapRequestResolver;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;

/**
 * Attribute for validating HTTP requests using JSON Schema
 */
#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class MapRequest extends ValueResolver
{
	/**
	 * Creates a validated request attribute
	 */
	public function __construct(public readonly string $schema, string $resolver = MapRequestResolver::class, public readonly bool $die = true)
	{
		parent::__construct($resolver);
	}
}
