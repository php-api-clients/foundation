<?php declare(strict_types=1);

namespace ApiClients\Foundation;

use ApiClients\Foundation\Hydrator\Factory as HydratorFactory;
use ApiClients\Foundation\Hydrator\Hydrator;
use ApiClients\Foundation\Transport\Client as TransportClient;
use ApiClients\Foundation\Transport\Factory as TransportFactory;
use ApiClients\Tools\CommandBus\CommandBus;
use ApiClients\Tools\CommandBus\Factory as CommandBusFactory;
use DI\ContainerBuilder;
use Interop\Container\ContainerInterface;
use League\Event\Emitter;
use League\Event\EmitterInterface;
use React\EventLoop\LoopInterface;

final class Factory
{
    public static function create(
        LoopInterface $loop = null,
        array $options = []
    ): Client {
        return new Client(
            self::createContainer($loop, $options)
        );
    }

    private static function createContainer(LoopInterface $loop, array $options): ContainerInterface
    {
        $container = new ContainerBuilder();

        $container->addDefinitions([
            EmitterInterface::class => new Emitter(),
            LoopInterface::class => $loop,
            TransportClient::class => function (ContainerInterface $container, LoopInterface $loop) use ($options) {
                return self::createTransport($container, $loop, $options);
            },
            Hydrator::class => function (ContainerInterface $container) use ($options) {
                return self::createHydrator($container, $options);
            },
            CommandBus::class => function (ContainerInterface $container) {
                return CommandBusFactory::create($container);
            },
        ]);

        return $container->build();
    }

    private static function createTransport(
        ContainerInterface $container,
        LoopInterface $loop = null,
        array $options = []
    ): TransportClient {
        return TransportFactory::create($container, $loop, $options[Options::TRANSPORT_OPTIONS]);
    }

    private static function createHydrator(ContainerInterface $container, array $options = [])
    {
        if (isset($options[Options::HYDRATOR]) && $options[Options::HYDRATOR] instanceof Hydrator) {
            return $options[Options::HYDRATOR];
        }

        if (!isset($options[Options::HYDRATOR_OPTIONS])) {
            throw new \Exception('Missing Hydrator options');
        }

        return HydratorFactory::create($container, $options[Options::HYDRATOR_OPTIONS]);
    }
}
