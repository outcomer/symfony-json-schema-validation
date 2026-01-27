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

use Outcomer\ValidationBundle\Enum\ValidationStatus;

/**
 * ValidatedRequest model containing validated payload and validation results
 */
class ValidatedRequest implements ValidatedDtoInterface
{
    public function __construct(private readonly Payload $payload, private readonly array $violations)
    {
    }

    /**
     * Creates ValidatedRequest from validated payload
     */
    public static function fromPayload(Payload $payload, array $violations = []): static
    {
        return new static($payload, $violations);
    }

    public function getPayload(): Payload
    {
        return $this->payload;
    }

    public function getViolations(): array
    {
        return $this->violations;
    }

    public function hasViolations(): bool
    {
        return !empty($this->violations);
    }

    public function isValid(): bool
    {
        return !$this->hasViolations();
    }

    public function getStatus(): ValidationStatus
    {
        return $this->hasViolations() ? ValidationStatus::INVALID : ValidationStatus::VALID;
    }
}
