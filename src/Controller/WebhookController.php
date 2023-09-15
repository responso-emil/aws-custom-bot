<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ChatMessage;
use App\Repository\ChatMessageRepository;
use App\Repository\ChatRepositoryInterface;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/webhook', name: 'app_webhook')]
final class WebhookController extends AbstractController
{
    public function __invoke(Request $request, ChatRepositoryInterface $chatRepository, ChatMessageRepository $chatMessageRepository): Response
    {
        $data = $request->toArray();

        $this->handleSubscriptionConfirmation($data);
        $this->handleMessage($request, $chatRepository, $chatMessageRepository);

        return new Response();
    }

    private function handleSubscriptionConfirmation(array $data): void
    {
        if ($data['Type'] === 'SubscriptionConfirmation') {
            $client = new Client();
            $client->get($data['SubscribeURL']);
        }
    }

    private function handleMessage(Request $request, ChatRepositoryInterface $chatRepository, ChatMessageRepository $chatMessageRepository): void
    {
        $data = $this->getDecodedData($request);

        $chat = $chatRepository->findOneBy(['contactId' => $data['Message']['InitialContactId']]);

        $message = new ChatMessage();

        $message->setJson($request->getContent());
        $message->setData($data);

        $message->setChat($chat);

        $chatMessageRepository->save($message);
    }

    private function getDecodedData(Request $request): array
    {
        $data = $request->toArray();
        $data['Message'] = json_decode($data['Message'], true, 512, JSON_THROW_ON_ERROR);

        return $data;
    }
}
