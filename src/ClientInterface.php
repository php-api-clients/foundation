<?php declare(strict_types=1);

namespace ApiClients\Foundation;

use Interop\Container\ContainerInterface;
use React\Promise\CancellablePromiseInterface;

interface ClientInterface
{
    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface;

    /**
     * @return mixed
     */
    public function getFromContainer(string $id);

    /**
     * @param $command
     * @return CancellablePromiseInterface
     */
    public function handle($command): CancellablePromiseInterface;
}
