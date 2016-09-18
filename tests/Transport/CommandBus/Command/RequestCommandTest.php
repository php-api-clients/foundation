<?php
declare(strict_types=1);

namespace ApiClients\Tests\Foundation\Transport\CommandBus\Command;

use ApiClients\Foundation\Transport\CommandBus\Command\RequestCommand;
use ApiClients\Tests\Foundation\Hydrator\TestCase;
use Psr\Http\Message\RequestInterface;

class RequestCommandTest extends TestCase
{
    public function testCommand()
    {
        $request = $this->prophesize(RequestInterface::class)->reveal();
        $refresh = true;
        $command = new RequestCommand($request, $refresh);
        $this->assertSame($request, $command->getRequest());
        $this->assertSame($refresh, $command->getRefresh());
    }

    public function testCommandDefaultRefresh()
    {
        $request = $this->prophesize(RequestInterface::class)->reveal();
        $refresh = false;
        $command = new RequestCommand($request);
        $this->assertSame($request, $command->getRequest());
        $this->assertSame($refresh, $command->getRefresh());
    }
}
