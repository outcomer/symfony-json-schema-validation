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

use Outcomer\ValidationBundle\Attribute\MapRequest;
use Outcomer\ValidationBundle\Model\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Example controller demonstrating JSON Schema validation
 *
 * These examples are only available in dev environment.
 * Access: http://your-app.local/_examples/validation/user
 */
#[Route('/_examples/validation')]
class ExampleValidationController extends AbstractController
{
	#[Route('/user', name: '_example_validation_user', methods: ['POST'])]
	public function validateUser(#[MapRequest(__DIR__.'/../Schemas/user-create.json')] Request $request): JsonResponse
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
			'example' => 'This is a working example from outcomer/symfony-json-schema-validation bundle',
		], 200);
	}

	#[Route('/info', name: '_example_validation_info', methods: ['GET'])]
	public function info(): JsonResponse
	{
		return $this->json([
			'message'   => 'Validation Examples',
			'endpoints' => ['POST /_examples/validation/user' => 'Validate user data with JSON Schema'],
			'testWith'  => 'curl -X POST http://your-app.local/_examples/validation/user -H "Content-Type: application/json" -d \'{"name":"John","email":"test@example.com","age":25}\'',
		]);
	}
}
