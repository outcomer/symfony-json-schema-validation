<?php

/**
 * This file is part of the Outcomer Symfony Validation package.
 *
 * (c) David Evdoshchenko <773021792e@gmail.com>
 *
 * @package Outcomer\Validation
 */

declare(strict_types=1);

namespace Outcomer\ValidationBundle\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * HTTP-transport wrapper around a domain ValidationException, thrown by
 * MapRequestResolver when a request fails schema validation.
 */
final class HttpValidationException extends HttpException
{
    public function __construct(private readonly ValidationException $inner, int $statusCode = Response::HTTP_BAD_REQUEST)
    {
        parent::__construct(statusCode: $statusCode, message: $inner->getMessage(), previous: $inner);
    }

    /**
     * Returns formatted validation errors from the wrapped domain exception
     */
    public function getValidationErrors(): array
    {
        return $this->inner->getValidationErrors();
    }
}
