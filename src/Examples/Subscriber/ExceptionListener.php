<?php

/**
 * This file is part of the Outcomer Symfony Validation package.
 *
 * (c) David Evdoshchenko <773021792e@gmail.com>
 *
 * @package Outcomer\Validation\Examples
 */

declare(strict_types=1);

namespace Outcomer\ValidationBundle\Examples\Subscriber;

use Outcomer\ValidationBundle\Examples\Handler\ValidationExceptionHandler;
use Outcomer\ValidationBundle\Exception\ValidationException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Example event subscriber for handling ValidationException
 *
 * This is an example implementation showing how to catch and handle validation
 * exceptions in your Symfony application. Copy this to your src/Subscriber/
 * directory and adjust the namespace to App\Subscriber.
 *
 * The subscriber catches ValidationException and delegates handling to the
 * ValidationExceptionHandler, which formats the error response as JSON.
 */
#[AsEventListener(event: KernelEvents::EXCEPTION, method: 'handleException', priority: 0)]
class ExceptionListener
{
    public function __construct(private readonly ValidationExceptionHandler $validationBundleHandler)
    {
    }

    /**
     * Delegates exception handling to the appropriate handler.
     */
    public function handleException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        match (true) {
            $exception instanceof ValidationException  => $this->validationBundleHandler->handleValidationBundleException($exception, $event),
            default => null,
        };
    }
}
