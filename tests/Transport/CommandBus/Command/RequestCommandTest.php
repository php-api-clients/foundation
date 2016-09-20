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
        $command = new RequestCommand($request, [
            RequestOptions::STREAM => true,
        ], $refresh);
        $this->assertSame($request, $command->getRequest());
        $this->assertSame([
            RequestOptions::STREAM => true,
        ], $command->getOptions());
        $this->assertSame($refresh, $command->getRefresh());
    }

    public function testCommandDefaults()
    {
        $request = $this->prophesize(RequestInterface::class)->reveal();
        $refresh = false;
        $command = new RequestCommand($request);
        $this->assertSame($request, $command->getRequest());
        $this->assertSame([], $command->getOptions());
        $this->assertSame($refresh, $command->getRefresh());
    }
}
