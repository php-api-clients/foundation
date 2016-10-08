<?php declare(strict_types=1);

namespace ApiClients\Tests\Foundation;

use ApiClients\Foundation\CommandLocatorEvent;
use ApiClients\Tools\TestUtilities\TestCase;

final class CommandLocatorEventTest extends TestCase
{
    public function testName()
    {
        $this->assertSame(CommandLocatorEvent::NAME, CommandLocatorEvent::create()->getName());
    }

    public function testEvent()
    {
        $event = CommandLocatorEvent::create();

        $this->assertSame([], $event->getMap());

        $event->add(
            dirname(__DIR__) . DIRECTORY_SEPARATOR . 'test_app' . DIRECTORY_SEPARATOR . 'CommandBus',
            'ApiClients\TestApp\Foundation\CommandBus'
        );
        $this->assertSame([
            'ApiClients\TestApp\Foundation\CommandBus\Command' => 'ApiClients\TestApp\Foundation\CommandBus\Handler',
        ], $event->getMap());

        $event->add(
            dirname(__DIR__) . DIRECTORY_SEPARATOR . 'test_app' . DIRECTORY_SEPARATOR . 'CommandTruck',
            'ApiClients\TestApp\Foundation\CommandTruck'
        );
        $this->assertSame([
            'ApiClients\TestApp\Foundation\CommandBus\Command' => 'ApiClients\TestApp\Foundation\CommandBus\Handler',
            'ApiClients\TestApp\Foundation\CommandTruck\Command' => 'ApiClients\TestApp\Foundation\CommandTruck\Handler',
        ], $event->getMap());

        $event->add(
            dirname(__DIR__) . DIRECTORY_SEPARATOR . 'test_app' . DIRECTORY_SEPARATOR . 'CommandPlane',
            'ApiClients\TestApp\Foundation\CommandPlane'
        );
        $this->assertSame([
            'ApiClients\TestApp\Foundation\CommandBus\Command' => 'ApiClients\TestApp\Foundation\CommandBus\Handler',
            'ApiClients\TestApp\Foundation\CommandTruck\Command' => 'ApiClients\TestApp\Foundation\CommandTruck\Handler',
            'ApiClients\TestApp\Foundation\CommandPlane\Command' => 'ApiClients\TestApp\Foundation\CommandPlane\Handler',
        ], $event->getMap());

        $event->add(
            dirname(__DIR__) . DIRECTORY_SEPARATOR . 'test_app' . DIRECTORY_SEPARATOR . 'CommandSubmarine',
            'ApiClients\TestApp\Foundation\CommandSubmarine'
        );
        $this->assertSame([
            'ApiClients\TestApp\Foundation\CommandBus\Command' => 'ApiClients\TestApp\Foundation\CommandBus\Handler',
            'ApiClients\TestApp\Foundation\CommandTruck\Command' => 'ApiClients\TestApp\Foundation\CommandTruck\Handler',
            'ApiClients\TestApp\Foundation\CommandPlane\Command' => 'ApiClients\TestApp\Foundation\CommandPlane\Handler',
            'ApiClients\TestApp\Foundation\CommandSubmarine\Command' => 'ApiClients\TestApp\Foundation\CommandSubmarine\Handler',
        ], $event->getMap());

    }
}
