<?php declare(strict_types=1);

namespace ApiClients\Foundation;

use InvalidArgumentException;
use League\Container\ContainerInterface;
use League\Tactician\CommandBus;

final class Client
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Client constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        if (!$this->container->has(CommandBus::class)) {
            throw new InvalidArgumentException();
        }
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
        return $this->container->get(CommandBus::class)->handle($command);
    }
}
