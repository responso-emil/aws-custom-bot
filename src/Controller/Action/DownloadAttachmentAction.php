<?php

declare(strict_types=1);

namespace App\Controller\Action;

use App\Entity\Chat;
use Aws\ConnectParticipant\ConnectParticipantClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DownloadAttachmentAction extends AbstractController
{
    #[Route('/download-attachment/{id}/{attachmentId}', name: 'app_download_attachment')]
    public function __invoke(Chat $chat, string $attachmentId): Response
    {
        $client = new ConnectParticipantClient([
            'version' => 'latest',
            'region' => 'us-east-1',
        ]);

        $result = $client->getAttachment([
            'AttachmentId' => $attachmentId,
            'ConnectionToken' => $chat->getConnectionToken(),
        ])->toArray();

        return $this->redirect($result['Url']);
    }
}
