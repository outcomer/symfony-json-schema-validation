<?php

/**
 * This file is part of the Outcomer Symfony Validation package.
 *
 * (c) David Evdoshchenko <773021792e@gmail.com>
 *
 * @package Outcomer\Validation
 */

declare(strict_types=1);

namespace Outcomer\ValidationBundle\Model;

/**
 * Interface for DTOs that can be created from validated payload
 */
interface ValidatedDtoInterface
{
    /**
     * Creates DTO instance from validated payload
     *
     * @param Payload $payload    The validated request payload.
     * @param array   $violations Validation violations if any.
     *
     * @return static
     */
    public static function fromPayload(Payload $payload, array $violations = []): static;

    /**
     * Checks if validation passed (no violations)
     */
    public function isValid(): bool;

    /**
     * Gets validation violations
     *
     * @return array
     */
    public function getViolations(): array;
}
