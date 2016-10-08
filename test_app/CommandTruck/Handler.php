<?php declare(strict_types=1);

namespace ApiClients\TestApp\Foundation\CommandTruck;

use React\Promise\PromiseInterface;
use function React\Promise\resolve;

final class Handler
{
    public function handle(): PromiseInterface
    {
        return resolve([
            'follow' => 'https://twitter.com/another_clue',
        ]);
    }
}
