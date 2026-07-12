<?php

namespace App\Controller;

use App\Entity\Link;
use App\Repository\LinkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class LinkController extends AbstractController
{
    #[Route('/link', name: 'app_link')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/LinkController.php',
        ]);
    }

    #[Route('/link/shortlink', name: 'api_shortlink', methods: ['GET'])]
    public function getShortLink(
        Request $request,
        LinkRepository $linkRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $originalUrl = $request->query->get('url');

        if (!$originalUrl) {
            return $this->json(['error' => 'Parameter "url" is required'], 400);
        }

        if (!filter_var($originalUrl, FILTER_VALIDATE_URL)) {
            return $this->json(['error' => 'Invalid URL format'], 400);
        }

        // Проверяем, есть ли уже такая ссылка
        $link = $linkRepository->findByOriginalUrl($originalUrl);

        if ($link) {
            return $this->json([
                'link_hash' => $link->getLinkHash(),
                'original_url' => $link->getOriginalUrl(),
            ]);
        }

        // Генерируем уникальный хэш
        $linkHash = $this->generateUniqueHash($originalUrl, $linkRepository);

        $link = new Link();
        $link->setOriginalUrl($originalUrl);
        $link->setLinkHash($linkHash);

        $em->persist($link);
        $em->flush();

        return $this->json([
            'link_hash' => $link->getLinkHash(),
            'original_url' => $link->getOriginalUrl(),
        ], 201);
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
