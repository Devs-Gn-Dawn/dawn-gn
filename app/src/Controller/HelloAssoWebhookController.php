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
        $clientIp = $request->getClientIp();

        if (!$webhookService->isClientIpAllowed($clientIp)) {
            return new Response('Unauthorized', Response::HTTP_UNAUTHORIZED);
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
