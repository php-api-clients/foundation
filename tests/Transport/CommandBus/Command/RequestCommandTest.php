<?php
declare(strict_types=1);

namespace ApiClients\Tests\Foundation\Transport\CommandBus\Command;

use ApiClients\Foundation\Transport\CommandBus\Command\RequestCommand;
use ApiClients\Tests\Foundation\TestCase;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;

class RequestCommandTest extends TestCase
{
    /**
     * @dataProvider provideTrueFalse
     */
    public function testCommand(bool $refresh)
    {
        $request = $this->prophesize(RequestInterface::class)->reveal();
        $command = new RequestCommand($request, $refresh, [
            RequestOptions::STREAM => true,
        ]);
        $this->assertSame($request, $command->getRequest());
        $this->assertSame($refresh, $command->getRefresh());
        $this->assertSame([
            RequestOptions::STREAM => true,
        ], $command->getOptions());
    }

    public function testCommandDefaults()
    {
        $request = $this->prophesize(RequestInterface::class)->reveal();
        $refresh = false;
        $command = new RequestCommand($request);
        $this->assertSame($request, $command->getRequest());
        $this->assertSame($refresh, $command->getRefresh());
        $this->assertSame([], $command->getOptions());
    }
}
