<?php

namespace App\Service;

use App\Message\LinkMessage;
use Symfony\Component\Messenger\MessageBusInterface;

class LinkService
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {
    }

    /**
     * Создает задание на создание уникального хэша ссылки
     *
     * @param string $originalUrl
     * @return void
     */
    public function createTaskMessage(string $originalUrl): void
    {
        $message = new LinkMessage($originalUrl);
        $this->messageBus->dispatch($message);
    }
}