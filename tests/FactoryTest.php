<?php declare(strict_types=1);

namespace ApiClients\Tests\Foundation;

use ApiClients\Foundation\Client;
use ApiClients\Foundation\Events\CommandLocatorEvent;
use ApiClients\Foundation\Factory;
use ApiClients\Foundation\Options;
use ApiClients\Tools\TestUtilities\TestCase;
use League\Container\Container;
use League\Event\CallbackListener;
use League\Event\EmitterInterface;
use League\Tactician\Exception\MissingHandlerException;
use React\EventLoop\LoopInterface;

final class FactoryTest extends TestCase
{
    public function testCreate()
    {
        $loop = $this->prophesize(LoopInterface::class);

        $client = Factory::create(
            $loop->reveal(),
            new Container(),
            [
                Options::HYDRATOR_OPTIONS => [],
            ]
        );

        $this->assertInstanceOf(Client::class, $client);

        $container = $client->getContainer();

        $called = false;
        $container->get(EmitterInterface::class)->addListener(
            CommandLocatorEvent::NAME,
            CallbackListener::fromCallable(
                function (CommandLocatorEvent $event) use (&$called) {
                    $this->assertSame([], $event->getMap());
                    $called = true;
                }
            )
        );

        $this->assertFalse($called);

        try {
            $client->handle(new class() {});
        } catch (\Throwable $exception) {

        }

        $this->assertTrue($called);
        $this->assertTrue(isset($exception));
        $this->assertInstanceOf(MissingHandlerException::class, $exception);
    }
}
