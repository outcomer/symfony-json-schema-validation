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

use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Errors\ValidationError;
use RuntimeException;
use Throwable;

/**
 * Domain exception thrown when JSON Schema validation fails.
 *
 * Deliberately not tied to HTTP - see HttpValidationException for the
 * HTTP-transport wrapper used by MapRequestResolver.
 */
final class ValidationException extends RuntimeException
{
    private array $validationErrors;

    /**
     * Creates validation exception from OPIS validation error
     */
    public function __construct(ValidationError $error, ?Throwable $previous = null)
    {
        $this->validationErrors = $this->reportResult($error);
        parent::__construct('Request data is invalid', 0, $previous);
    }

    /**
     * Returns formatted validation errors
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    /**
     * Formatter for result. Create propper array with errors.
     */
    private function reportResult(ValidationError $error): array
    {
        $formatter = new ErrorFormatter();

        $format = fn(ValidationError $error): array => [
            'keyword'  => $error->keyword(),
            'dataPath' => $formatter->formatErrorKey($error),
            'message'  => $formatter->formatErrorMessage($error),
            'expected' => $error->schema()->info()->data(),
            'recieved' => $error->data()->value(),
        ];

        $formatKey = function (ValidationError $error) use ($formatter): string {
            $path = $formatter->formatErrorKey($error);

            return $path;
        };

        $formatErr = fn(ValidationError $error, ?string $message): array => [
            'expected' => $formatter->formatErrorMessage($error, $message),
            'recieved' => $error->data()->value(),
        ];

        $formatNested = function (ValidationError $error, ?array $subErrors = null) use ($formatter) {
            if ($subErrors) {
                $return = [
                    //'keyword' => $error->keyword(),
                    'message' => $formatter->formatErrorMessage(error: $error),
                ];
            } else {
                $return = [
                    //'keyword' => $error->keyword(),
                    'message'  => $formatter->formatErrorMessage(error: $error),
                    'recieved' => $error->data()->value(),
                ];
            }

            if ($subErrors) {
                $return['subErrors'] = $subErrors;
            }

            return $return;
        };

        return $formatter->format($error, true, $formatErr);
        // return $formatter->formatKeyed($error, $format, $formatKey);
        // return $formatter->formatNested($error, $formatNested);
        // return $formatter->formatOutput($error, "flag");
        // return $formatter->formatOutput($error, "basic");
        // return $formatter->formatOutput($error, "detailed");
        // return $formatter->formatOutput($error, "verbose");
    }
}
