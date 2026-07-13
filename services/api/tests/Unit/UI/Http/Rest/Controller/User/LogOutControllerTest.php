<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Rest\Controller\User;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\User\Application\Command\LogOut\LogOutCommand;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mercure\Authorization;
use Symfony\Component\Mercure\HubRegistry;
use Symfony\Component\Mercure\Jwt\LcobucciFactory;
use Symfony\Component\Mercure\Jwt\StaticTokenProvider;
use Symfony\Component\Mercure\MockHub;
use UI\Http\Rest\Controller\User\LogOutController;
use UI\Http\Rest\Response\ApiResponse;

final class LogOutControllerTest extends TestCase {
  #[Test]
  public function itHandlesLogOutCommand(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $command = new LogOutCommand('refresh-token');

    $commandBus->expects($this->once())
      ->method('handle')
      ->with($command);

    $controller = new LogOutController();
    $controller->setCommandBus($commandBus);

    $response = $controller(new Request(), $this->createAuthorization(), $command);

    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(204, $response->getStatusCode());
  }

  #[Test]
  public function itClearsMercureAuthorizationCookie(): void {
    $commandBus = $this->createStub(CommandBusInterface::class);

    $controller = new LogOutController();
    $controller->setCommandBus($commandBus);

    $request = new Request();
    $controller($request, $this->createAuthorization(), new LogOutCommand('refresh-token'));

    /** @var array<string, Cookie> $cookies */
    $cookies = $request->attributes->get('_mercure_authorization_cookies', []);
    $cookie = array_values($cookies)[0] ?? null;

    $this->assertInstanceOf(Cookie::class, $cookie);
    $this->assertSame('mercureAuthorization', $cookie->getName());
    $this->assertSame('/sse', $cookie->getPath());
    $this->assertNull($cookie->getValue());
    $this->assertLessThan(time(), $cookie->getExpiresTime());
  }

  private function createAuthorization(): Authorization {
    $hub = new MockHub(
      'http://localhost:3333/.well-known/mercure',
      new StaticTokenProvider('test.jwt.token'),
      fn(): string => 'id',
      new LcobucciFactory('a-string-that-is-at-least-256-bits-long-for-hs256'),
      '/sse',
    );

    return new Authorization(new HubRegistry($hub), 1800);
  }
}
