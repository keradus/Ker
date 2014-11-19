<?php

namespace Ker;

trait InnerClassCacheTrait
{
    protected $cache = [];

    public function clearCache()
    {
        $this->cache = [];
    }
}
