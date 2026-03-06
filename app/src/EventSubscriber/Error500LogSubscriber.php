<?php

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Log les erreurs HTTP 5xx dans un fichier dédié (prod_errors.log) en production.
 */
class Error500LogSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $error500Logger
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 0],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $statusCode = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : 500;

        if ($statusCode < 500) {
            return;
        }

        $request = $event->getRequest();
        $context = [
            'status_code' => $statusCode,
            'url' => $request->getRequestUri(),
            'method' => $request->getMethod(),
            'exception_class' => $exception::class,
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ];

        $this->error500Logger->error(
            sprintf(
                '[%s] %s %s - %s: %s',
                $statusCode,
                $request->getMethod(),
                $request->getRequestUri(),
                $exception::class,
                $exception->getMessage()
            ),
            $context
        );
    }
}
