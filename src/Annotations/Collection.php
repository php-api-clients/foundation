<?php
declare(strict_types=1);

namespace WyriHaximus\ApiClient\Annotations;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Collection
{
    /**
     * @var array
     */
    protected $types = [];

    /**
     * Nested constructor.
     * @param array $types
     */
    public function __construct(array $types)
    {
        $this->types = $types;
    }

    /**
     * @return array
     */
    public function properties(): array
    {
        return array_keys($this->types);
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key): bool
    {
        return isset($this->types[$key]);
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

        return $this->types[$key];
    }
}
