<?php

/**
 * This file is part of the Outcomer Symfony Validation package.
 *
 * (c) David Evdoshchenko <773021792e@gmail.com>
 *
 * @package Outcomer\Validation\Examples
 */

declare(strict_types=1);

namespace Outcomer\ValidationBundle\Examples\Handler;

use Symfony\Component\HttpFoundation\JsonResponse;
use Outcomer\ValidationBundle\Exception\ValidationException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

/**
 * Example exception handler for ValidationException
 *
 * This is an example implementation showing how to handle validation exceptions
 * in your application. Copy this to your src/Exception/Handler/ directory and
 * adjust the namespace to App\Exception\Handler.
 */
class ValidationExceptionHandler
{
	public function handleValidationBundleException(ValidationException $exception, ExceptionEvent $event): void
	{
		$errors = $exception->getValidationErrors();

		$response = new JsonResponse(
			data: [
				'message' => $exception->getMessage(),
				'errors'  => $errors,
			],
			status: $exception->getStatusCode()
		);

		$event->setResponse($response);
	}
}
