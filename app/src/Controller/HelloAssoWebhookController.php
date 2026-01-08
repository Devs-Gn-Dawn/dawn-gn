<?php

namespace App\Controller;

use App\Service\HelloAssoWebhookService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HelloAssoWebhookController extends AbstractController
{
    #[Route('/webhook/helloasso', name: 'helloasso_webhook', methods: ['POST'])]
    public function handle(
        Request $request,
        HelloAssoWebhookService $webhookService
    ): Response {
        $rawBody = $request->getContent();
        $signature = $request->headers->get('X-HelloAsso-Signature');

        if (!$webhookService->isSignatureValid($rawBody, $signature)) {
            return new Response('Invalid signature', Response::HTTP_UNAUTHORIZED);
        }

        $payload = json_decode($rawBody, true);

        if (!$payload) {
            return new Response('Invalid JSON', Response::HTTP_BAD_REQUEST);
        }

        $webhookService->process($payload);

        // IMPORTANT : toujours répondre 200
        return new Response('OK', Response::HTTP_OK);
    }
}
