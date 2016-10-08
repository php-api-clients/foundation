<?php declare(strict_types=1);

namespace ApiClients\Foundation;

use League\Event\AbstractEvent;
use WyriHaximus\Tactician\CommandHandler\Mapper;

final class CommandLocatorEvent extends AbstractEvent
{
    const NAME = 'api-clients.foundation.command-locator';

    /**
     * @var array
     */
    private $map = [];

    /**
     * @return CommandLocatorEvent
     */
    public static function create(): CommandLocatorEvent
    {
        return new self();
    }

    private function __construct()
    {
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @param string $path
     * @param string $namespace
     */
    public function add(string $path, string $namespace)
    {
        $this->map += Mapper::map($path, $namespace);
    }

    /**
     * @return array
     */
    public function getMap(): array
    {
        return $this->map;
    }
}
