<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Comment\Infrastructure\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Comment\Domain\Enum\CommentEventType;
use Slink\Comment\Infrastructure\EventListener\CommentChangeListener;
use Slink\Comment\Infrastructure\ReadModel\View\CommentView;
use Slink\Shared\Domain\Service\ServerSentEventPublisherInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class CommentChangeListenerTest extends TestCase {
  #[Test]
  public function itPublishesPrivateUpdateOnPersist(): void {
    $comment = $this->createStub(CommentView::class);
    $comment->method('getImageId')->willReturn('image-123');

    $publisher = $this->createMock(ServerSentEventPublisherInterface::class);
    $publisher->expects($this->once())
      ->method('publish')
      ->with(
        'comments/image/image-123',
        $this->callback(fn(array $data): bool => $data['event'] === CommentEventType::Created->value),
        true,
      );

    $serializer = $this->createStub(NormalizerInterface::class);
    $serializer->method('normalize')->willReturn(['id' => 'comment-1']);

    $listener = new CommentChangeListener($publisher, $serializer);
    $listener->postPersist($comment, new PostPersistEventArgs($comment, $this->createStub(EntityManagerInterface::class)));
  }

  #[Test]
  public function itPublishesPrivateUpdateOnDelete(): void {
    $comment = $this->createStub(CommentView::class);
    $comment->method('getImageId')->willReturn('image-123');
    $comment->method('getId')->willReturn('comment-1');

    $publisher = $this->createMock(ServerSentEventPublisherInterface::class);
    $publisher->expects($this->once())
      ->method('publish')
      ->with(
        'comments/image/image-123',
        $this->callback(fn(array $data): bool => $data['event'] === CommentEventType::Deleted->value),
        true,
      );

    $unitOfWork = $this->createStub(UnitOfWork::class);
    $unitOfWork->method('getEntityChangeSet')->willReturn(['deletedAt' => [null, new \DateTimeImmutable()]]);

    $entityManager = $this->createStub(EntityManagerInterface::class);
    $entityManager->method('getUnitOfWork')->willReturn($unitOfWork);

    $listener = new CommentChangeListener($publisher, $this->createStub(NormalizerInterface::class));
    $listener->postUpdate($comment, new PostUpdateEventArgs($comment, $entityManager));
  }
}
