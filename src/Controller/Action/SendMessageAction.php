<?php

declare(strict_types=1);

namespace App\Controller\Action;

use App\Entity\Chat;
use Aws\ConnectParticipant\ConnectParticipantClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class SendMessageAction extends AbstractController
{
    #[Route('/send-message/{id}', name: 'app_send_message')]
    public function __invoke(Chat $chat): Response
    {
        $client = new ConnectParticipantClient([
            'version' => 'latest',
        ]);

        $client->sendMessage([
            'Content' => 'Hello world!',
            'ContentType' => 'text/plain',
            'ConnectionToken' => $chat->getConnectionToken(),
        ]);

        $this->addFlash('success', 'Message sent!');

        return $this->redirectToRoute('app_chat', ['id' => $chat->getId()]);
    }
}
