<?php

/**
 * This file is part of the Outcomer Symfony Validation package.
 *
 * (c) David Evdoshchenko <773021792e@gmail.com>
 *
 * @package Outcomer\Validation
 */

declare(strict_types=1);

namespace Outcomer\ValidationBundle;

use Outcomer\ValidationBundle\DependencyInjection\OutcomerValidationExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Bundle for JSON Schema based request validation using OPIS
 */
final class OutcomerValidationBundle extends Bundle
{
    /**
     * Gets the container extension for bundle configuration
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new OutcomerValidationExtension();
    }
}
