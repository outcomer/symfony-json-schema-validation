<?php

/**
 * This file is part of the Outcomer Symfony Validation package.
 *
 * (c) David Evdoshchenko <773021792e@gmail.com>
 *
 * @package Outcomer\Validation
 */

declare(strict_types=1);

namespace Outcomer\ValidationBundle\Examples\Controller;

use Nelmio\ApiDocBundle\Attribute\Areas;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Outcomer\ValidationBundle\Attribute\MapRequest;
use Outcomer\ValidationBundle\Examples\Dto\UserApiDtoRequest;
use Outcomer\ValidationBundle\Examples\Dto\UserApiDtoResponse;
use Outcomer\ValidationBundle\Model\ValidatedRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Example controller demonstrating JSON Schema validation
 *
 * These examples are only available in dev environment.
 * Access: http://your-app.local/_examples/validation/user
 */
#[Areas(['examples'])]
#[OA\Tag('Examples')]
#[Route('/_examples/validation')]
class ExampleValidationController extends AbstractController
{
    #[OA\Post(
        operationId: 'validateUser',
        summary: 'Validate user',
    )]
    #[Route('/user', name: '_example_validation_user', methods: ['POST'])]
    public function validateUser(#[MapRequest('../../../vendor/outcomer/symfony-json-schema-validation/src/Examples/Schemas/user-create.json')] ValidatedRequest $request): JsonResponse
    {
        $payload = $request->getPayload();
        $body    = $payload->getBody();

        return $this->json([
            'success' => true,
            'message' => 'User data is valid',
            'data'    => [
                'name'  => $body->name,
                'email' => $body->email,
                'age'   => $body->age ?? null,
            ],
            'example' => 'This uses ValidatedRequest (standard way)',
        ], 200);
    }

    #[OA\Post(
        operationId: 'validateUserDto',
        summary: 'DTO injection',
    )]
    #[Route('/user-dto', name: '_example_validation_user_dto', methods: ['POST'])]
    public function validateUserDto(#[MapRequest('../../../vendor/outcomer/symfony-json-schema-validation/src/Examples/Schemas/user-create.json')] UserApiDtoRequest $dto): JsonResponse
    {
        return $this->json([
            'success' => true,
            'message' => 'User data is valid',
            'data'    => [
                'name'  => $dto->name,
                'email' => $dto->email,
                'age'   => $dto->age,
            ],
            'example' => 'This uses automatic DTO injection via fromPayload method',
        ], 200);
    }

    #[OA\Post(
        operationId: 'createProfile',
        summary: 'Create profile',
    )]
    #[Route('/profile', name: '_example_validation_profile', methods: ['POST'])]
    public function createProfile(#[MapRequest('../../../vendor/outcomer/symfony-json-schema-validation/src/Examples/Schemas/user-create.json')] UserApiDtoRequest $profile): JsonResponse
    {
        return $this->json([
            'success' => true,
            'message' => 'Profile created successfully',
            'profile' => [
                'name'  => $profile->name,
                'email' => $profile->email,
                'age'   => $profile->age,
            ],
            'note'    => sprintf('This demonstrates DTO auto-injection: MapRequestResolver calls %1$s::fromPayload() automatically', UserApiDtoRequest::class),
        ], 201);
    }

    #[OA\Post(
        operationId: 'createUserWithDocs',
        summary: 'Documented endpoint',
        description: 'Demonstrates JSON Schema validation with OpenAPI documentation',
        responses: [
            new OA\Response(
                response: 200,
                description: 'User created successfully',
                content: new OA\JsonContent(
                    ref: new Model(type: UserApiDtoResponse::class)
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'errors', type: 'object'),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    #[Route('/api-user', name: '_example_validation_api_user', methods: ['POST'])]
    public function createUserWithDocs(#[MapRequest('../../../vendor/outcomer/symfony-json-schema-validation/src/Examples/Schemas/user-create.json')] UserApiDtoRequest $user): JsonResponse
    {
        $userData = UserApiDtoResponse::fromArray([
            'name'  => $user->name,
            'email' => $user->email,
            'age'   => $user->age,
        ]);

        return $this->json($userData, 200);
    }

    #[OA\Get(
        operationId: 'getExamplesInfo',
        summary: 'Examples info',
    )]
    #[Route('/info', name: '_example_validation_info', methods: ['GET'])]
    public function info(): JsonResponse
    {
        return $this->json([
            'message'   => 'Validation Examples',
            'endpoints' => [
                'POST /_examples/validation/user'     => 'Validate with ValidatedRequest (standard)',
                'POST /_examples/validation/user-dto' => 'Validate with automatic DTO injection (via fromPayload)',
                'POST /_examples/validation/profile'  => 'Another DTO injection example',
                'POST /_examples/validation/api-user' => 'OpenAPI documented endpoint with validation',
            ],
            'features'  => [
                'body validation'    => 'JSON request body validation',
                'query validation'   => 'URL query parameters validation',
                'headers validation' => 'HTTP headers validation',
                'path validation'    => 'URL path parameters validation',
            ],
            'testWith'  => 'curl -X POST http://your-app.local/_examples/validation/user-dto -H "Content-Type: application/json" -H "Authorization: Bearer test123" -H "X-API-Version: v1" -d \'{"name":"John","email":"test@example.com","age":25}\'',
        ]);
    }
}
