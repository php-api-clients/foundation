<?php declare(strict_types=1);

namespace ApiClients\Foundation\Transport\CommandBus\Handler;

use ApiClients\Foundation\Transport\CommandBus\Command\JsonDecodeCommand;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use function WyriHaximus\React\futureFunctionPromise;

final class JsonDecodeHandler
{
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @param LoopInterface $loop
     */
    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    /**
     * @param JsonDecodeCommand $command
     * @return PromiseInterface
     */
    public function handle(JsonDecodeCommand $command): PromiseInterface
    {
        return futureFunctionPromise($this->loop, $command->getJson(), function ($json) {
            return json_decode($json, true);
        });
    }
}
