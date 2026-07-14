<?php

namespace App\MessageHandler;

use App\Entity\Link;
use App\Message\LinkMessage;
use App\Repository\LinkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ShortLinkHandler
{
    private const LOCK_TTL = 10.0;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LinkRepository $linkRepository,
        private readonly LockFactory $lockFactory
    ) {
    }

    public function __invoke(LinkMessage $message)
    {
        // Создаем хэш блокировки без проверки уникальности в БД (для экономии времени)
        $lockHash = md5($message->getOriginalUrl());

        $lock = $this->lockFactory->createLock(
            'link-hash-' . $lockHash,
            self::LOCK_TTL
        );

        if (!$lock->acquire(false)) {
            // Другой воркер уже обрабатывает сообщение с таким же хэшем, завершаем задачу.
            return;
        }
        try {
            // Ищем существующую ссылку в БД
            $existLink = $this->linkRepository->findByOriginalUrl($message->getOriginalUrl());
            if ($existLink) {
                return;
            }
            $linkHash = $this->generateUniqueHash($message->getOriginalUrl(), $this->linkRepository);
            $link = new Link();
            $link->setOriginalUrl($message->getOriginalUrl());
            $link->setLinkHash($linkHash);

            $this->entityManager->persist($link);
            $this->entityManager->flush();
        } finally {
            $lock->release();
        }

    }

    /**
     * По ТЗ длина хэша от 4 до 8 символов
     *
     * @param string $originalUrl
     * @param LinkRepository $repository
     * @param int $length
     * @return string
     * @throws \Random\RandomException
     */
    private function generateUniqueHash(string $originalUrl, LinkRepository $repository, int $minLength = 4, int $maxLength = 8): string
    {
        // генеригуем уникальный хэш строки до тех пор пока не найдем свободный (которого нет в БД)
        // при таком подходе мы получаем больше возможных уникальных значений, чем при использовании
        // части более блинного хэша или детерминированного хеша с сжатием
        do {
            $hash = substr(
                md5($originalUrl . microtime() . random_bytes(4)),
                0,
                rand($minLength, $maxLength)
            );
        } while ($repository->findByLinkHash($hash) !== null);
        sleep(10);

        return $hash;
    }
}