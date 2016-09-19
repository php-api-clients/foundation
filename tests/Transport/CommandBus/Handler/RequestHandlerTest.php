<?php declare(strict_types=1);

namespace ApiClients\Tests\Foundation\Transport\CommandBus\Handler;

use ApiClients\Foundation\Transport\Client;
use ApiClients\Foundation\Transport\CommandBus\Command\SimpleRequestCommand;
use ApiClients\Foundation\Transport\CommandBus\Handler\RequestHandler;
use ApiClients\Tests\Foundation\TestCase;
use function Clue\React\Block\await;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use React\Promise\FulfilledPromise;

class RequestHandlerTest extends TestCase
{
    public function testHandler()
    {
        $path = '/foo/bar.json';
        $client = $this->prophesize(Client::class);
        $promise = new FulfilledPromise();
        $client->request(Argument::that(function (RequestInterface $request) use ($path) {
            return $request->getUri()->getPath() === $path;
        }), false)->willReturn($promise);
        $command = new SimpleRequestCommand($path);
        $handler = new RequestHandler($client->reveal());
        $this->assertSame($promise, $handler->handle($command));
    }
}
