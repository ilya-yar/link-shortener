<?php

namespace App\Repository;

use App\Entity\Link;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Link>
 */
class LinkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Link::class);
    }

    public function findByOriginalUrl(string $original_url): ?Link
    {
        return $this->findOneBy(['original_url' => $original_url]);
    }

    public function findByLinkHash(string $link_hash): ?Link
    {
        return $this->findOneBy(['link_hash' => $link_hash]);
    }
}
