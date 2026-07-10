<?php

declare(strict_types=1);

namespace Unit\Slink\User\Application\Command\MoveOAuthProvider;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Application\Command\MoveOAuthProvider\MoveOAuthProviderCommand;
use Slink\User\Application\Command\MoveOAuthProvider\MoveOAuthProviderHandler;
use Slink\User\Domain\Exception\OAuthProviderNotFoundException;
use Slink\User\Domain\OAuthProvider;
use Slink\User\Domain\Repository\OAuthProviderRepositoryInterface;
use Slink\User\Domain\Repository\OAuthProviderStoreRepositoryInterface;
use Slink\User\Infrastructure\ReadModel\View\OAuthProviderView;

final class MoveOAuthProviderHandlerTest extends TestCase {

  #[Test]
  public function itMovesProviderToEarlierPosition(): void {
    $ids = $this->generateIds(4);
    $providers = $this->createOrderedProviders($ids);

    $repository = $this->createRepository($providers);
    $providerStore = $this->createProviderStore([
      $ids[2] => 0.0,
      $ids[0] => 1.0,
      $ids[1] => 2.0,
    ]);

    $handler = new MoveOAuthProviderHandler($providerStore, $repository);

    $handler(new MoveOAuthProviderCommand($ids[2], 0));
  }

  #[Test]
  public function itMovesProviderToLaterPosition(): void {
    $ids = $this->generateIds(4);
    $providers = $this->createOrderedProviders($ids);

    $repository = $this->createRepository($providers);
    $providerStore = $this->createProviderStore([
      $ids[1] => 0.0,
      $ids[2] => 1.0,
      $ids[0] => 2.0,
    ]);

    $handler = new MoveOAuthProviderHandler($providerStore, $repository);

    $handler(new MoveOAuthProviderCommand($ids[0], 2));
  }

  #[Test]
  public function itDoesNothingWhenPositionIsUnchanged(): void {
    $ids = $this->generateIds(3);
    $providers = $this->createOrderedProviders($ids);

    $repository = $this->createRepository($providers);

    $providerStore = $this->createMock(OAuthProviderStoreRepositoryInterface::class);
    $providerStore->expects($this->never())->method('get');
    $providerStore->expects($this->never())->method('store');

    $handler = new MoveOAuthProviderHandler($providerStore, $repository);

    $handler(new MoveOAuthProviderCommand($ids[1], 1));
  }

  #[Test]
  public function itClampsPositionBeyondEndToLast(): void {
    $ids = $this->generateIds(3);
    $providers = $this->createOrderedProviders($ids);

    $repository = $this->createRepository($providers);
    $providerStore = $this->createProviderStore([
      $ids[1] => 0.0,
      $ids[2] => 1.0,
      $ids[0] => 2.0,
    ]);

    $handler = new MoveOAuthProviderHandler($providerStore, $repository);

    $handler(new MoveOAuthProviderCommand($ids[0], 99));
  }

  #[Test]
  public function itThrowsWhenProviderIsNotFound(): void {
    $ids = $this->generateIds(2);
    $providers = $this->createOrderedProviders($ids);

    $repository = $this->createRepository($providers);

    $providerStore = $this->createMock(OAuthProviderStoreRepositoryInterface::class);
    $providerStore->expects($this->never())->method('get');
    $providerStore->expects($this->never())->method('store');

    $handler = new MoveOAuthProviderHandler($providerStore, $repository);

    $this->expectException(OAuthProviderNotFoundException::class);

    $handler(new MoveOAuthProviderCommand(ID::generate()->toString(), 0));
  }

  /**
   * @return array<int, string>
   */
  private function generateIds(int $count): array {
    return array_map(static fn(): string => ID::generate()->toString(), range(1, $count));
  }

  /**
   * @param array<int, string> $ids
   * @return array<int, OAuthProviderView>
   */
  private function createOrderedProviders(array $ids): array {
    return array_map(function (int $index) use ($ids): OAuthProviderView {
      $view = $this->createStub(OAuthProviderView::class);
      $view->method('getId')->willReturn($ids[$index]);
      $view->method('getSortOrder')->willReturn((float) $index);

      return $view;
    }, array_keys($ids));
  }

  /**
   * @param array<int, OAuthProviderView> $providers
   */
  private function createRepository(array $providers): OAuthProviderRepositoryInterface {
    $repository = $this->createMock(OAuthProviderRepositoryInterface::class);
    $repository->expects($this->once())
      ->method('getProviders')
      ->willReturn($providers);

    return $repository;
  }

  /**
   * @param array<string, float> $expectedSortOrders
   */
  private function createProviderStore(array $expectedSortOrders): OAuthProviderStoreRepositoryInterface {
    $aggregates = [];

    foreach ($expectedSortOrders as $id => $sortOrder) {
      $aggregate = $this->createMock(OAuthProvider::class);
      $aggregate->expects($this->once())
        ->method('update')
        ->with(null, null, null, null, null, null, null, null, null, null, $sortOrder);
      $aggregates[$id] = $aggregate;
    }

    $providerStore = $this->createMock(OAuthProviderStoreRepositoryInterface::class);
    $providerStore->expects($this->exactly(count($expectedSortOrders)))
      ->method('get')
      ->willReturnCallback(fn(ID $id) => $aggregates[$id->toString()] ?? $this->fail('Unexpected ID: ' . $id->toString()));
    $providerStore->expects($this->exactly(count($expectedSortOrders)))
      ->method('store');

    return $providerStore;
  }
}
