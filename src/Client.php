<?php declare(strict_types=1);

namespace ApiClients\Foundation;

use ApiClients\Tools\CommandBus\CommandBusInterface;
use Psr\Container\ContainerInterface;
use React\Promise\CancellablePromiseInterface;

final class Client implements ClientInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->commandBus = $this->container->get(CommandBusInterface::class);
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return mixed
     */
    public function getFromContainer(string $id)
    {
        return $this->container->get($id);
    }

    /**
     * @param $command
     * @return CancellablePromiseInterface
     */
    public function handle($command): CancellablePromiseInterface
    {
        return $this->commandBus->handle($command);
    }
}
