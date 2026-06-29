<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Image;

use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

final class ImageListContractTest extends HttpTestCase {
  /**
   * @var array<int, string>
   */
  private const array EXPECTED_ROW_KEYS = [
    'id',
    'owner',
    'url',
    'attributes',
    'metadata',
    'license',
    'bookmarkCount',
    'isBookmarked',
  ];

  /**
   * @return array{0: int, 1: array<string, mixed>}
   */
  private function getImageList(string $token, int $limit): array {
    $status = $this->apiRequest('GET', \sprintf('/api/images?limit=%d', $limit), $token);
    self::assertSame(200, $status, 'Image list failed: ' . (string) $this->client->getResponse()->getContent());

    /** @var array<string, mixed> $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    return [$status, $payload];
  }

  #[Test]
  public function imageListResponseContractIsStable(): void {
    $tokens = [];
    $uploadedIds = [];
    for ($i = 1; $i <= 3; $i++) {
      $slug = "imglist-lock-owner{$i}";
      $this->createUser("{$slug}@local.test", $slug, self::PASSWORD);
      $token = $this->login($slug, self::PASSWORD);
      $tokens[] = $token;
      $uploadedIds[] = $this->uploadImage($token, true);
    }

    [, $payload] = $this->getImageList($tokens[0], 50);

    self::assertArrayHasKey('meta', $payload);
    self::assertArrayHasKey('data', $payload);

    /** @var array<string, mixed> $meta */
    $meta = $payload['meta'];
    self::assertSame(['size', 'total'], \array_keys($meta), 'Unexpected meta key set for a single-page cursor list.');
    self::assertSame(50, $meta['size'], 'meta.size must echo the requested limit.');
    self::assertSame(3, $meta['total'], 'meta.total must reflect the seeded public image count.');

    /** @var array<int, array<string, mixed>> $rows */
    $rows = $payload['data'];
    self::assertCount(3, $rows);

    foreach ($rows as $row) {
      self::assertSame(
        self::EXPECTED_ROW_KEYS,
        \array_keys($row),
        'Each row must expose exactly the public/bookmark/license field set.',
      );
    }

    $returnedIds = \array_map(static fn(array $row): mixed => $row['id'], $rows);
    self::assertSame(
      \array_values($returnedIds),
      $returnedIds,
      'Row ids must be a plain ordered list.',
    );
    self::assertEqualsCanonicalizing(
      $uploadedIds,
      $returnedIds,
      'The list must return exactly the seeded image ids, no more, no less.',
    );

    $this->assertOrderedByCreatedAtThenUuidDesc($rows);

    [, $repeatPayload] = $this->getImageList($tokens[0], 50);
    /** @var array<int, array<string, mixed>> $repeatRows */
    $repeatRows = $repeatPayload['data'];
    $repeatIds = \array_map(static fn(array $row): mixed => $row['id'], $repeatRows);
    self::assertSame($returnedIds, $repeatIds, 'Row ordering must be stable across identical requests.');
  }

  /**
   * @param array<int, array<string, mixed>> $rows
   */
  private function assertOrderedByCreatedAtThenUuidDesc(array $rows): void {
    $previous = null;

    foreach ($rows as $row) {
      /** @var array{createdAt: array{timestamp: int}} $attributes */
      $attributes = $row['attributes'];
      $timestamp = $attributes['createdAt']['timestamp'];
      /** @var string $uuid */
      $uuid = $row['id'];
      $current = ['timestamp' => $timestamp, 'uuid' => $uuid];

      if ($previous !== null) {
        $inOrder = $previous['timestamp'] > $current['timestamp']
          || ($previous['timestamp'] === $current['timestamp'] && $previous['uuid'] >= $current['uuid']);

        self::assertTrue(
          $inOrder,
          'Rows must be ordered by createdAt DESC then uuid DESC.',
        );
      }

      $previous = $current;
    }
  }
}
