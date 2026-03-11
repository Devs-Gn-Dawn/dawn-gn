<?php

namespace App\Controller;

use App\Service\HelloAssoWebhookService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HelloAssoWebhookController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

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

        $eventType = $payload['eventType'] ?? null;
        $data = $payload['data'] ?? [];
        $state = $data['state'] ?? null;
        $payloadShape = $this->getPayloadShape($eventType, $data);
        $this->logger->info('Webhook HelloAsso reçu', [
            'ip' => $clientIp,
            'eventType' => $eventType,
            'state' => $state,
            'payloadShape' => $payloadShape,
        ]);

        $webhookService->process($payload);

        // IMPORTANT : toujours répondre 200
        return new Response('OK', Response::HTTP_OK);
    }

    /**
     * Structure du payload reçu (sans PII) pour le debug.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function getPayloadShape(?string $eventType, array $data): array
    {
        $shape = ['dataKeys' => array_keys($data)];

        if ($eventType === 'Payment') {
            $order = $data['order'] ?? [];
            $items = $data['items'] ?? $order['items'] ?? [];
            $shape['orderId'] = $order['id'] ?? null;
            $shape['formSlug'] = $order['formSlug'] ?? null;
            $shape['formId'] = $order['form']['id'] ?? null;
            $shape['itemsCount'] = count($items);
            $shape['itemTypes'] = array_values(array_map(fn($i) => $i['type'] ?? null, $items));
        }

        if ($eventType === 'Order') {
            $shape['orderId'] = $data['id'] ?? null;
        }

        return $shape;
    }
}
