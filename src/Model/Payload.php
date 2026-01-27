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
 * Container for validated HTTP request data (body, query, path, headers)
 */
final class Payload
{
    /**
     * Creates a request payload with validated data (body can be object, array, or null)
     */
    public function __construct(private object|array|null $body, private object $query, private object $path, private object $headers)
    {
    }

    public function getPath(): object
    {
        return $this->path;
    }

    public function getQuery(): object
    {
        return $this->query;
    }

    public function getBody(): object|array|null
    {
        return $this->body;
    }

    public function getHeaders(): object
    {
        return $this->headers;
    }
}
