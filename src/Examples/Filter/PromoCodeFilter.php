<?php

/**
 * This file is part of the Outcomer Symfony Validation package.
 *
 * (c) David Evdoshchenko <773021792e@gmail.com>
 *
 * @package Outcomer\Validation
 */

declare(strict_types=1);

namespace Outcomer\ValidationBundle\Examples\Filter;

use Opis\JsonSchema\Filter;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\ValidationContext;

/**
 * Example $filters implementation demonstrating parameterized filters via $vars.
 *
 * Validates that a promo code has a required prefix and minimum length,
 * both configurable per schema through $vars.
 */
final class PromoCodeFilter implements Filter
{
    public function validate(ValidationContext $context, Schema $schema, array $args = []): bool
    {
        $value = $context->currentData();

        if (!is_string($value)) {
            return true; // Not our concern, let JSON Schema's "type" keyword handle it
        }

        $minLength = $args['minLength'] ?? 6;
        $prefix    = $args['prefix'] ?? null;

        if (strlen($value) < $minLength) {
            return false;
        }

        if (null !== $prefix && !str_starts_with($value, $prefix)) {
            return false;
        }

        return true;
    }
}
