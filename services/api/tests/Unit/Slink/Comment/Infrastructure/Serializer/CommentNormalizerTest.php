<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Comment\Infrastructure\Serializer;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Comment\Infrastructure\ReadModel\View\CommentView;
use Slink\Comment\Infrastructure\Serializer\CommentNormalizer;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Infrastructure\Serializer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class CommentNormalizerTest extends TestCase {
  private function createNormalizer(): CommentNormalizer {
    $inner = $this->createStub(NormalizerInterface::class);
    $inner->method('normalize')->willReturnCallback(static function (mixed $data): array {
      if ($data instanceof DateTime) {
        return new DateTimeNormalizer()->normalize($data);
      }

      return ['id' => 'comment-1'];
    });

    $normalizer = new CommentNormalizer();
    $normalizer->setNormalizer($inner);

    return $normalizer;
  }

  private function createComment(DateTime $createdAt, bool $deleted = false): CommentView {
    $comment = $this->createStub(CommentView::class);
    $comment->method('getCreatedAt')->willReturn($createdAt);
    $comment->method('isDeleted')->willReturn($deleted);

    return $comment;
  }

  private function normalizeEditable(CommentView $comment): mixed {
    $result = $this->createNormalizer()->normalize($comment);

    return $result['editable'] ?? null;
  }

  /**
   * @return array{formattedDate: string, timestamp: int}
   */
  private function expectedEditable(DateTime $createdAt): array {
    $deadline = DateTime::fromDateTimeImmutable($createdAt->modify('+24 hours'));

    return [
      'formattedDate' => $deadline->getDateString(),
      'timestamp' => $deadline->getUnixTimeStamp(),
    ];
  }

  #[Test]
  public function itEmitsEditableAsCreatedAtPlusEditWindow(): void {
    $createdAt = DateTime::fromString('2026-01-01 12:00:00');
    $comment = $this->createComment($createdAt);

    $this->assertSame($this->expectedEditable($createdAt), $this->normalizeEditable($comment));
  }

  #[Test]
  public function itEmitsEditableForDeletedComment(): void {
    $createdAt = DateTime::fromString('2026-01-01 12:00:00');
    $comment = $this->createComment($createdAt, deleted: true);

    $this->assertSame($this->expectedEditable($createdAt), $this->normalizeEditable($comment));
  }

  #[Test]
  public function itDoesNotEmitCanEdit(): void {
    $comment = $this->createComment(DateTime::now());

    $result = $this->createNormalizer()->normalize($comment);

    $this->assertArrayNotHasKey('canEdit', $result);
  }
}
