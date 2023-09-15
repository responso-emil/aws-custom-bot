<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Chat;
use App\Repository\ChatRepositoryInterface;
use App\Utils\RandomStringGenerator;
use Aws\Connect\ConnectClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ApiGatewayController extends AbstractController
{
    #[Route('/api-gateway', name: 'app_api_gateway')]
    public function startChatContact(Request $request, ChatRepositoryInterface $chatRepository): Response
    {
        $data = $this->prepareRequestData($request);
        $client = $this->createConnectClient();
        $username = $data['Username'];

        $awsData = $this->startAwsChatContact($client, $data);
        $newData = $this->prepareResponseData($awsData);
        $this->startContactStreaming($client, $awsData['ContactId']);

        $this->createChat($newData, $username, $chatRepository);

        return new JsonResponse($newData);
    }

    private function prepareRequestData(Request $request): array
    {
        $data = $request->toArray();
        $data['Attributes'] = array_combine(
            array_map(static function ($key) {
                return str_replace(' ', '', $key);
            }, array_keys($data['Attributes'])),
            $data['Attributes']
        );
        return $data;
    }

    private function createConnectClient(): ConnectClient
    {
        return new ConnectClient([
            'version' => 'latest',
        ]);
    }

    private function startAwsChatContact(ConnectClient $client, array $data): array
    {
        return $client->startChatContact($data)->toArray();
    }

    private function prepareResponseData(array $awsData): array
    {
        return [
            'data' => [
                'persistedChatSession' => null,
                'featurePermissions' => [
                    'ATTACHMENTS' => true,
                ],
                'startChatResult' => [
                    'ContactId' => $awsData['ContactId'],
                    'ParticipantId' => $awsData['ParticipantId'],
                    'ParticipantToken' => $awsData['ParticipantToken'],
                ],
            ],
        ];
    }

    private function startContactStreaming(ConnectClient $client, string $contactId): void
    {
        $client->startContactStreaming([
            'ChatStreamingConfiguration' => [
                'StreamingEndpointArn' => $this->getParameter('streaming_endpoint_arn'),
            ],
            'ClientToken' => RandomStringGenerator::generate(),
            'ContactId' => $contactId,
            'InstanceId' => $this->getParameter('connect_instance_id'),
        ]);
    }

    private function createChat(array $newData, mixed $username, ChatRepositoryInterface $chatRepository): void
    {
        $chat = new Chat();
        $chat->setContactId($newData['data']['startChatResult']['ContactId']);
        $chat->setParticipantToken($newData['data']['startChatResult']['ParticipantToken']);
        $chat->setUsername($username);

        $chatRepository->save($chat);
    }
}
