<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Twig\Environment;

class ErrorController extends AbstractController
{
    public function __construct(
        private readonly Environment $twig
    ) {}

    public function show(\Throwable $exception): Response
    {
        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;
        $statusText = $exception instanceof HttpExceptionInterface ? $exception->getMessage() : 'Internal Server Error';

        return new Response(
            $this->twig->render('error/error.html.twig', [
                'status_code' => $statusCode,
                'status_text' => $statusText
            ]),
            $statusCode
        );
    }
}
