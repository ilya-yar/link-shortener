<?php

namespace App\Controller;

use App\Repository\LinkRepository;
use App\Service\LinkService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class LinkController extends AbstractController
{
    #[Route('/link/shortlink', name: 'api_shortlink', methods: ['GET'])]
    public function getShortLink(
        Request $request,
        LinkService $linkService,
        LinkRepository $linkRepository
    ): JsonResponse {
        $originalUrl = $request->query->get('url');

        if (!$originalUrl) {
            return $this->json(['error' => 'Parameter "url" is required'], 400);
        }

        if (!filter_var($originalUrl, FILTER_VALIDATE_URL)) {
            return $this->json(['error' => 'Invalid URL format'], 400);
        }

        $link = $linkRepository->findByOriginalUrl($originalUrl);

        if ($link) {
            return $this->json([
                'link_hash' => $link->getLinkHash(),
                'original_url' => $link->getOriginalUrl(),
            ]);
        }

        // Если ссылка не найдена в БД, создаем задачу на генерацию хэша
        $linkService->createTaskMessage($originalUrl);

        return $this->json([
            'message' => 'ссылка генерируется',
            'original_url' => $originalUrl,
        ],
            202,
            [],
            ['json_encode_options' => JSON_UNESCAPED_UNICODE]);
    }
}
