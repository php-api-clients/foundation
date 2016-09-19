<?php declare(strict_types=1);

namespace ApiClients\Foundation\Transport\CommandBus\Handler;

use ApiClients\Foundation\Transport\Client;
use ApiClients\Foundation\Transport\CommandBus\Command\RequestCommandInterface;
use GuzzleHttp\Promise\PromiseInterface;

final class RequestHandler
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function handle(RequestCommandInterface $command): PromiseInterface
    {
        return $this->client->request($command->getRequest(), $command->getRefresh());
    }
}
