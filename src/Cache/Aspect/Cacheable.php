<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Cache\Aspect;

use Max\Aop\Contract\AspectInterface;
use Max\Aop\JoinPoint;
use Psr\Container\ContainerExceptionInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Cacheable implements AspectInterface
{
    public function __construct(
        protected string $prefix = '',
        protected ?string $key = null,
        protected ?int $ttl = null,
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException|\ReflectionException
     */
    public function process(JoinPoint $joinPoint, \Closure $next): mixed
    {
        return make(CacheInterface::class)->remember($this->getKey($joinPoint), fn () => $next($joinPoint), $this->ttl);
    }

    protected function getKey(JoinPoint $joinPoint): string
    {
        $key = $this->key ?? ($joinPoint->class . ':' . $joinPoint->method . ':' . serialize(array_filter($joinPoint->parameters->getArrayCopy(), fn ($item) => ! is_object($item))));
        return $this->prefix ? ($this->prefix . ':' . $key) : $key;
    }
}
