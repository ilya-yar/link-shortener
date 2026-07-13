<?php

namespace App\Service;

use App\Entity\Link;
use App\Message\LinkMessage;
use App\Repository\LinkRepository;
use Symfony\Component\Messenger\MessageBusInterface;

class LinkService
{
    public function __construct(
        private readonly LinkRepository $linkRepository,
        private readonly MessageBusInterface $messageBus
    ) {
    }

    /**
     * Возвращает короткую ссылку по original_url,
     * либо создаёт задание на создание уникальной короткой ссылки.
     */
    public function findOrCreateByOriginalUrl(string $originalUrl): Link|null
    {
        $link = $this->linkRepository->findByOriginalUrl($originalUrl);

        if ($link !== null) {
            return $link;
        }

        $this->createMessage($originalUrl);

        return null;
    }

    /**
     * Создает задание на создание уникального хэша ссылки
     *
     * @param string $originalUrl
     * @return void
     */
    private function createMessage(string $originalUrl): void
    {
        $message = new LinkMessage($originalUrl);
        $this->messageBus->dispatch($message);
    }
}