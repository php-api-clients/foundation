<?php declare(strict_types=1);

namespace ApiClients\Foundation\Transport\CommandBus\Handler;

use ApiClients\Foundation\Transport\CommandBus\Command\JsonEncodeCommand;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use function WyriHaximus\React\futureFunctionPromise;

final class JsonEncodeHandler
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
     * @param JsonEncodeCommand $command
     * @return PromiseInterface
     */
    public function handle(JsonEncodeCommand $command): PromiseInterface
    {
        return futureFunctionPromise($this->loop, $command->getJson(), function ($json) {
            return json_encode($json);
        });
    }
}
