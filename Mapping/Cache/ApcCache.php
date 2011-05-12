<?php

namespace Symfony\Component\Validator\Mapping\Cache;

use Symfony\Component\Validator\Mapping\ClassMetadata;

class ApcCache implements CacheInterface
{
    private $prefix;

    public function __construct($prefix)
    {
        if (!extension_loaded('apc')) {
            throw new \RuntimeException('First you need to enable APC extension in your php.ini. In meanwhile you can just remove "cache" option from application configuration.');
        }

        $this->prefix = $prefix;
    }

    public function has($class)
    {
        return apc_exists($this->prefix.$class);
    }

    public function read($class)
    {
        return apc_fetch($this->prefix.$class);
    }

    public function write(ClassMetadata $metadata)
    {
        apc_store($this->prefix.$metadata->getClassName(), $metadata);
    }
}