<?php declare(strict_types=1);

namespace ApiClients\Foundation;

use League\Container\ContainerInterface;
use League\Tactician\CommandBus;

final class Client
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * Client constructor.
     * @param ContainerInterface $container
     * @param CommandBus $commandBus
     */
    public function __construct(ContainerInterface $container, CommandBus $commandBus)
    {
        $this->container = $container;
        $this->commandBus = $commandBus;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public function handle($command)
    {
        return $this->commandBus->handle($command);
    }
}
