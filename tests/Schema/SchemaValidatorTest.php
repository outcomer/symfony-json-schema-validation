<?php

/**
 * This file is part of the Outcomer Symfony Validation package.
 *
 * (c) David Evdoshchenko <773021792e@gmail.com>
 *
 * @package Outcomer\Validation
 */

declare(strict_types=1);

namespace Outcomer\ValidationBundle\Tests\Schema;

use Outcomer\ValidationBundle\Exception\ValidationException;
use Outcomer\ValidationBundle\Schema\SchemaValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * Test suite for SchemaValidator statelessness and $ref resolution via registerPrefix
 */
final class SchemaValidatorTest extends TestCase
{
    private string $schemasPath;
    private SchemaValidator $validator;

    protected function setUp(): void
    {
        $this->schemasPath = sys_get_temp_dir().'/outcomer_schema_validator_test';
        @mkdir($this->schemasPath, 0777, true);

        file_put_contents($this->schemasPath.'/main.json', json_encode([
            'type' => 'object',
            'properties' => [
                'email' => ['$ref' => 'https://test.local/schemas/email.json'],
            ],
        ]));
        file_put_contents($this->schemasPath.'/email.json', json_encode([
            'type' => 'string',
            'format' => 'email',
        ]));
        file_put_contents($this->schemasPath.'/other.json', json_encode([
            'type' => 'object',
            'properties' => [
                'age' => ['type' => 'integer'],
            ],
        ]));

        $this->validator = new SchemaValidator(new ServiceLocator([]), $this->schemasPath, 'https://test.local/schemas');
    }

    public function testRefIsResolvedViaRegisterPrefixWithoutExplicitRegistration(): void
    {
        $this->expectException(ValidationException::class);

        $this->validator->validateFileSchema((object) ['email' => 'not-an-email'], $this->schemasPath.'/main.json');
    }

    public function testValidDataPassesWithRefResolution(): void
    {
        $this->validator->validateFileSchema((object) ['email' => 'john@example.com'], $this->schemasPath.'/main.json');
        $this->addToAssertionCount(1);
    }

    public function testConsecutiveCallsWithDifferentSchemasDoNotLeakState(): void
    {
        try {
            $this->validator->validateFileSchema((object) ['email' => 'not-an-email'], $this->schemasPath.'/main.json');
            $this->fail('Expected ValidationException for invalid email');
        } catch (ValidationException) {
        }

        // A schema unrelated to the first call must validate independently
        $this->validator->validateFileSchema((object) ['age' => 5], $this->schemasPath.'/other.json');
        $this->addToAssertionCount(1);
    }

    public function testValidateAcceptsInlineArraySchema(): void
    {
        $this->expectException(ValidationException::class);

        $this->validator->validateInlineSchema((object) ['x' => 5], [
            'type' => 'object',
            'properties' => ['x' => ['type' => 'string']],
        ]);
    }
}
