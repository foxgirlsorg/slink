import type { Page } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';

interface CommentEvent {
  event: string;
  comment?: {
    id?: string;
    content?: string;
    author?: { displayName?: string };
    editable?: unknown;
  };
}

const topicFor = (imageId: string) => `comments/image/${imageId}`;

async function subscribeToComments(page: Page, imageId: string): Promise<void> {
  await page.evaluate((topic) => {
    const w = window as Window & { __sseEvents?: unknown[] };
    w.__sseEvents = [];

    const url = new URL('/sse', window.location.origin);
    url.searchParams.append('topic', topic);

    const source = new EventSource(url.toString(), { withCredentials: true });
    source.onmessage = (event) => {
      w.__sseEvents?.push(JSON.parse(event.data));
    };

    return new Promise<void>((resolve) => {
      source.onopen = () => resolve();
      source.onerror = () => resolve();
      setTimeout(resolve, 3000);
    });
  }, topicFor(imageId));
}

function receivedEvents(page: Page): Promise<CommentEvent[]> {
  return page.evaluate(
    () => (window as Window & { __sseEvents?: unknown[] }).__sseEvents ?? [],
  ) as Promise<CommentEvent[]>;
}

test.describe('Comment SSE authorization', () => {
  test(
    'anonymous viewer without a grant gets 404 on comments and no SSE events',
    { tag: '@anonymous' },
    async ({ page, actor }) => {
      const owner = await actor('owner');
      const imageId = await owner.content.uploadImage({ isPublic: false });

      await page.goto('/explore');

      const commentsResponse = await page.request.get(
        `/api/image/${imageId}/comments`,
      );
      expect(commentsResponse.status()).toBe(404);

      await subscribeToComments(page, imageId);
      await owner.content.createComment(imageId, `Private note ${Date.now()}`);

      await page.waitForTimeout(3000);
      expect(await receivedEvents(page)).toEqual([]);
    },
  );

  test('owner receives the created comment event', async ({ page, api }) => {
    const imageId = await api.content.uploadImage({ isPublic: false });

    await page.goto('/explore');

    const grant = await page.request.get(`/api/image/${imageId}/comments`);
    expect(grant.ok()).toBeTruthy();

    await subscribeToComments(page, imageId);

    const content = `Owner comment ${Date.now()}`;
    await api.content.createComment(imageId, content);

    await expect
      .poll(async () => (await receivedEvents(page)).length, {
        timeout: 10000,
      })
      .toBe(1);

    const [event] = await receivedEvents(page);
    expect(event.event).toBe('comment_created');
    expect(event.comment?.id).toBeTruthy();
    expect(event.comment?.content).toBe(content);
    expect(event.comment?.author).toBeTruthy();
    expect(event.comment).toHaveProperty('editable');
  });

  test(
    'anonymous collection share viewer receives comment events',
    { tag: '@anonymous' },
    async ({ page, actor }) => {
      const owner = await actor('owner');
      const imageId = await owner.content.uploadImage({ isPublic: false });
      const collectionId = await owner.content.createCollection({
        name: `Shared collection ${Date.now()}`,
      });
      await owner.content.addImageToCollection(collectionId, imageId);
      await owner.shares.publishCollectionShare(collectionId);

      await page.goto('/explore');

      await expect
        .poll(
          async () => {
            const response = await page.request.get(
              `/api/image/${imageId}/comments`,
            );
            return response.status();
          },
          { timeout: 15000 },
        )
        .toBe(200);

      await subscribeToComments(page, imageId);

      const content = `Shared comment ${Date.now()}`;
      await owner.content.createComment(imageId, content);

      await expect
        .poll(async () => (await receivedEvents(page)).length, {
          timeout: 10000,
        })
        .toBe(1);

      const [event] = await receivedEvents(page);
      expect(event.event).toBe('comment_created');
      expect(event.comment?.content).toBe(content);
    },
  );
});
