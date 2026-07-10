<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\MoveOAuthProvider;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Exception\OAuthProviderNotFoundException;
use Slink\User\Domain\Filter\OAuthProviderFilter;
use Slink\User\Domain\Repository\OAuthProviderRepositoryInterface;
use Slink\User\Domain\Repository\OAuthProviderStoreRepositoryInterface;
use Slink\User\Infrastructure\ReadModel\View\OAuthProviderView;

final readonly class MoveOAuthProviderHandler implements CommandHandlerInterface {
  public function __construct(
    private OAuthProviderStoreRepositoryInterface $providerStore,
    private OAuthProviderRepositoryInterface $repository,
  ) {}

  public function __invoke(MoveOAuthProviderCommand $command): void {
    $providers = $this->repository->getProviders(new OAuthProviderFilter(enabledOnly: false));

    $currentIndex = $this->findIndex($providers, $command->getId());

    if ($currentIndex === null) {
      throw new OAuthProviderNotFoundException($command->getId());
    }

    $targetIndex = min($command->getPosition(), count($providers) - 1);

    if ($targetIndex === $currentIndex) {
      return;
    }

    [$target] = array_splice($providers, $currentIndex, 1);
    array_splice($providers, $targetIndex, 0, [$target]);

    foreach ($providers as $index => $provider) {
      $this->applySortOrder($provider, (float) $index);
    }
  }

  /**
   * @param array<int, OAuthProviderView> $providers
   */
  private function findIndex(array $providers, string $id): ?int {
    foreach ($providers as $index => $provider) {
      if ($provider->getId() === $id) {
        return $index;
      }
    }

    return null;
  }

  private function applySortOrder(OAuthProviderView $provider, float $sortOrder): void {
    if ($provider->getSortOrder() === $sortOrder) {
      return;
    }

    $aggregate = $this->providerStore->get(ID::fromString($provider->getId()));
    $aggregate->update(sortOrder: $sortOrder);
    $this->providerStore->store($aggregate);
  }
}
