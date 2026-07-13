<?php

declare(strict_types=1);

namespace Slink\Comment\Application\Query\GetCommentsByImage;

use Slink\Comment\Domain\Enum\CommentAccess;
use Slink\Comment\Domain\Repository\CommentRepositoryInterface;
use Slink\Shared\Application\Http\Collection;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class GetCommentsByImageHandler implements QueryHandlerInterface {
  public function __construct(
    private CommentRepositoryInterface $commentRepository,
    private AuthorizationCheckerInterface $access,
  ) {
  }

  public function __invoke(GetCommentsByImageQuery $query): Collection {
    $imageId = $query->getImageId();

    if (!$this->access->isGranted(CommentAccess::View, $imageId)) {
      throw new NotFoundException();
    }

    $paginator = $this->commentRepository->findByImageId(
      $imageId,
      $query->getPage(),
      $query->getLimit(),
    );

    $comments = iterator_to_array($paginator);
    $total = $paginator->count();

    $items = array_map(fn($comment) => Item::fromEntity($comment), $comments);

    return new Collection(
      $query->getPage(),
      $query->getLimit(),
      $total,
      $items,
    );
  }
}
