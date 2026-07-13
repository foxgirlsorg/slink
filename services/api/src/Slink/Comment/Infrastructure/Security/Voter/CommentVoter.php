<?php

declare(strict_types=1);

namespace Slink\Comment\Infrastructure\Security\Voter;

use Slink\Collection\Domain\Enum\CollectionScopedImageAccess;
use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;
use Slink\Collection\Domain\ValueObject\CollectionScopedImageAccessContext;
use Slink\Comment\Domain\Enum\CommentAccess;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Application\Security\Viewer;
use Slink\Shared\Infrastructure\Security\Voter\GuestAccessVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class CommentVoter extends Voter {
  public function __construct(
    private readonly ImageRepositoryInterface $imageRepository,
    private readonly CollectionItemRepositoryInterface $collectionItemRepository,
    private readonly AuthorizationCheckerInterface $access,
  ) {}

  /**
   * @param mixed $attribute
   */
  protected function supports(mixed $attribute, mixed $subject): bool {
    return $attribute instanceof CommentAccess && is_string($subject);
  }

  /**
   * @param mixed $attribute
   */
  protected function voteOnAttribute(mixed $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool {
    if (!$attribute instanceof CommentAccess || !is_string($subject)) {
      return false;
    }

    $imageView = $this->imageRepository->oneById($subject);

    if (Viewer::fromToken($token)->owns($imageView)) {
      return true;
    }

    if ($imageView->getAttributes()->isPublic() && $this->access->isGranted(GuestAccessVoter::GUEST_VIEW_ALLOWED)) {
      return true;
    }

    return $this->hasCollectionShareAccess($subject, $imageView);
  }

  private function hasCollectionShareAccess(string $imageId, ImageView $imageView): bool {
    $collectionIds = $this->collectionItemRepository->getCollectionIdsByImageIds([$imageId])[$imageId] ?? [];

    foreach ($collectionIds as $collectionId) {
      $context = new CollectionScopedImageAccessContext($collectionId, $imageId, $imageView);

      if ($this->access->isGranted(CollectionScopedImageAccess::View, $context)) {
        return true;
      }
    }

    return false;
  }
}
