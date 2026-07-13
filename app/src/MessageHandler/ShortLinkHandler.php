<?php

namespace App\MessageHandler;

use App\Entity\Link;
use App\Message\LinkMessage;
use App\Repository\LinkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ShortLinkHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LinkRepository $linkRepository
    ) {
    }

    public function __invoke(LinkMessage $message)
    {
        $linkHash = $this->generateUniqueHash($message->getOriginalUrl(), $this->linkRepository);

        $link = new Link();
        $link->setOriginalUrl($message->getOriginalUrl());
        $link->setLinkHash($linkHash);

        $this->entityManager->persist($link);
        $this->entityManager->flush();

    }

    private function generateUniqueHash(string $originalUrl, LinkRepository $repository, int $length = 8): string
    {
        do {
            $hash = substr(
                md5($originalUrl . microtime() . random_bytes(4)),
                0,
                $length
            );
        } while ($repository->findByLinkHash($hash) !== null);

        return $hash;
    }
}