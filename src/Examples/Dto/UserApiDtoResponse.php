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

use OpenApi\Attributes as OA;

/**
 * Response DTO for user API operations
 */
final readonly class UserApiDtoResponse
{
    public function __construct(
        #[OA\Property(
            description: "User's full name",
            example: 'John Doe'
        )]
        public string $name,
        #[OA\Property(
            description: "User's email address",
            example: 'john.doe@example.com'
        )]
        public string $email,
        #[OA\Property(
            description: "User's age",
            example: 30
        )]
        public ?int $age = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            age: $data['age'] ?? null
        );
    }
}
