<?php declare(strict_types=1);

namespace ApiClients\Tests\Foundation;

use ApiClients\Foundation\Client;
use ApiClients\Foundation\Factory;
use ApiClients\Foundation\Hydrator\Hydrator;
use ApiClients\Foundation\Transport\Client as TransportClient;
use ApiClients\Foundation\Options;
use ApiClients\Tools\TestUtilities\TestCase;
use InvalidArgumentException;
use League\Tactician\Exception\MissingHandlerException;
use React\EventLoop\Factory as LoopFactory;
use React\EventLoop\LoopInterface;
use Throwable;
use function Clue\React\Block\await;

final class FactoryTest extends TestCase
{
    public function testCreate()
    {
        $loop = LoopFactory::create();

        $stdClass = new \stdClass();
        $stdClass->foo = 'bar';

        $client = Factory::create(
            $loop,
            [
                Options::HYDRATOR_OPTIONS => [],
                Options::TRANSPORT_OPTIONS => [],
                Options::CONTAINER_DEFINITIONS => [
                    \stdClass::class => $stdClass,
                ],
            ]
        );

        $this->assertInstanceOf(Client::class, $client);

        $container = $client->getContainer();
        $this->assertInstanceOf(LoopInterface::class, $container->get(LoopInterface::class));
        $this->assertSame($loop, $container->get(LoopInterface::class));
        $this->assertInstanceOf(Hydrator::class, $container->get(Hydrator::class));
        $this->assertInstanceOf(TransportClient::class, $container->get(TransportClient::class));
        $this->assertInstanceOf(\stdClass::class, $container->get(\stdClass::class));
        $this->assertSame($stdClass, $container->get(\stdClass::class));
        $this->assertSame('bar', $container->get(\stdClass::class)->foo);

        try {
            await($client->handle(new class() {}), $loop);
        } catch (Throwable $exception) {

        }

        $this->assertTrue(isset($exception));
        $this->assertInstanceOf(MissingHandlerException::class, $exception);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Missing Hydrator options
     */
    public function testCreateMissingHydratorOptions()
    {
        Factory::create(
            LoopFactory::create(),
            [
                Options::TRANSPORT_OPTIONS => [],
            ]
        )->getContainer()->get(Hydrator::class);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Missing Transport options
     */
    public function testCreateMissingTransportOptions()
    {
        Factory::create(
            LoopFactory::create(),
            [
                Options::HYDRATOR_OPTIONS => [],
            ]
        )->getContainer()->get(TransportClient::class);
    }
}
