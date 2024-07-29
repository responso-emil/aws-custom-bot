<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Chat;
use App\Repository\ChatRepositoryInterface;
use App\Utils\RandomStringGenerator;
use Aws\Connect\ConnectClient;
use Aws\ConnectParticipant\ConnectParticipantClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ChatController extends AbstractController
{
    private string $connectInstanceId;

    public function __construct(string $connectInstanceId)
    {
        $this->connectInstanceId = $connectInstanceId;
    }

    #[Route('/chat/{id}', name: 'app_chat')]
    public function __invoke(Request $request, Chat $chat, ChatRepositoryInterface $chatRepository): Response
    {
        if ($chat->getConnectionToken() === null) {
            $this->initiateChatConnection($chat, $chatRepository);
        }

        return $this->render('chat/index.html.twig', [
            'chat' => $chat,
        ]);
    }

    private function initiateChatConnection(Chat $chat, ChatRepositoryInterface $chatRepository): void
    {
        $connectClient = $this->createConnectClient();
        $participantToken = $this->createParticipant($connectClient, $chat);
        $connectionToken = $this->createParticipantConnection($participantToken);

        $chat->setConnectionToken($connectionToken);
        $chatRepository->save($chat);
    }

    private function createConnectClient(): ConnectClient
    {
        return new ConnectClient([
            'version' => 'latest',
            'region' => 'us-east-1',
        ]);
    }

    private function createParticipant(ConnectClient $client, Chat $chat): string
    {
        $response = $client->createParticipant([
            'ClientToken' => RandomStringGenerator::generate(),
            'ContactId' => $chat->getContactId(),
            'InstanceId' => $this->connectInstanceId,
            'ParticipantDetails' => [
                'DisplayName' => 'TestUser',
                'ParticipantRole' => 'CUSTOM_BOT',
            ],
        ])->toArray();

        return $response['ParticipantCredentials']['ParticipantToken'];
    }

    private function createParticipantConnection(string $participantToken): string
    {
        $client = new ConnectParticipantClient([
            'version' => 'latest',
            'region' => 'us-east-1',
        ]);

        $connection = $client->createParticipantConnection([
            'ParticipantToken' => $participantToken,
            'ConnectParticipant' => true,
            'Type' => ['CONNECTION_CREDENTIALS'],
        ]);

        return $connection->toArray()['ConnectionCredentials']['ConnectionToken'];
    }
}
