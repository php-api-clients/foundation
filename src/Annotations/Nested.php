<?php
declare(strict_types=1);

namespace WyriHaximus\ApiClient\Annotations;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Nested
{
    /**
     * @var array
     */
    protected $nestedObjects = [];

    /**
     * Nested constructor.
     * @param array $nestedObjects
     */
    public function __construct(array $nestedObjects)
    {
        $this->nestedObjects = $nestedObjects;
    }

    /**
     * @return array
     */
    public function properties(): array
    {
        return array_keys($this->nestedObjects);
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key): bool
    {
        return isset($this->nestedObjects[$key]);
    }

    /**
     * @param $key
     * @return string
     */
    public function get($key): string
    {
        if (!$this->has($key)) {
            throw new \InvalidArgumentException();
        }

        return $this->nestedObjects[$key];
    }
}
