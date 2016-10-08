<?php declare(strict_types=1);

namespace ApiClients\TestApp\Foundation\CommandBus;

use ApiClients\Foundation\CommandInterface;
use function React\Promise\resolve;
use WyriHaximus\Tactician\CommandHandler\Annotations\Handler as HandlerAnnotation;


/**
 * @HandlerAnnotation("ApiClients\TestApp\Foundation\CommandBus\Handler")
 */
final class Command
{
    /**
     * @var array
     */
    private $array;

    /**
     * @param array $array
     */
    public function __construct(array $array)
    {
        $this->array = $array;
    }

    /**
     * @return array
     */
    public function getArray(): array
    {
        return $this->array;
    }
}
