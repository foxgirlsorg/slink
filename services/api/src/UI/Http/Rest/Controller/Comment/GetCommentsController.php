<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Comment;

use Slink\Comment\Application\Query\GetCommentsByImage\GetCommentsByImageQuery;
use Slink\Shared\Application\Query\QueryTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Mercure\Authorization;
use Symfony\Component\Routing\Attribute\Route;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/image/{imageId}/comments', name: 'get_comments', methods: ['GET'])]
final class GetCommentsController {
  use QueryTrait;

  public function __invoke(
    Request $request,
    Authorization $authorization,
    string $imageId,
    #[MapQueryParameter] int $page = 1,
    #[MapQueryParameter] int $limit = 20,
  ): ApiResponse {
    $query = new GetCommentsByImageQuery($imageId, max(1, $page), min(100, max(1, $limit)));
    $result = $this->ask($query);

    $authorization->setCookie($request, ["comments/image/{$imageId}"]);

    return ApiResponse::collection($result);
  }
}
