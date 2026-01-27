<?php

/**
 * This file is part of the Outcomer Symfony Validation package.
 *
 * (c) David Evdoshchenko <773021792e@gmail.com>
 *
 * @package Outcomer\Validation
 */

declare(strict_types=1);

namespace Outcomer\ValidationBundle\Examples\Dto;

use Outcomer\ValidationBundle\Model\Payload;
use Outcomer\ValidationBundle\Model\ValidatedDtoInterface;

/**
 * Example DTO that demonstrates automatic injection via fromPayload method
 */
readonly class UserApiDtoRequest implements ValidatedDtoInterface
{
    public function __construct(public string $name, public string $email, public ?int $age = null, public array $violations = [])
    {
    }

    /**
     * Creates DTO from validated payload - this method is called automatically by MapRequestResolver
     */
    public static function fromPayload(Payload $payload, array $violations = []): static
    {
        $body = $payload->getBody();

        return new self(
            name: $body->name,
            email: $body->email,
            age: $body->age ?? null,
            violations: $violations
        );
    }

    public function isValid(): bool
    {
        return empty($this->violations);
    }

    public function getErrors(): array
    {
        return $this->violations;
    }

    public function getViolations(): array
    {
        return $this->violations;
    }
}
