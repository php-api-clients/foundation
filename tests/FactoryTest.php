<?php declare(strict_types=1);

namespace ApiClients\Tests\Foundation;

use ApiClients\Foundation\Client;
use ApiClients\Foundation\Events\CommandLocatorEvent;
use ApiClients\Foundation\Factory;
use ApiClients\Foundation\Options;
use ApiClients\Tools\TestUtilities\TestCase;
use League\Event\CallbackListener;
use League\Event\EmitterInterface;
use League\Tactician\Exception\MissingHandlerException;
use React\EventLoop\Factory as LoopFactory;
use Throwable;
use function Clue\React\Block\await;

final class FactoryTest extends TestCase
{
    public function testCreate()
    {
        $loop = LoopFactory::create();

        $client = Factory::create(
            $loop,
            [
                Options::HYDRATOR_OPTIONS => [],
                Options::TRANSPORT_OPTIONS => [],
            ]
        );

        $this->assertInstanceOf(Client::class, $client);

        $container = $client->getContainer();

        $called = false;
        $container->get(EmitterInterface::class)->addListener(
            CommandLocatorEvent::NAME,
            CallbackListener::fromCallable(
                function (CommandLocatorEvent $event) use (&$called) {
                    $called = true;
                }
            )
        );

        $this->assertFalse($called);

        try {
            await($client->handle(new class() {}), $loop);
        } catch (Throwable $exception) {

        }

        $this->assertTrue($called);
        $this->assertTrue(isset($exception));
        $this->assertInstanceOf(MissingHandlerException::class, $exception);
    }
}
