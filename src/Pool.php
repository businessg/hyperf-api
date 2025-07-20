<?php

namespace BusinessG\HyperfApi;

class Pool
{
    private $options;

    public function __construct(array ...$options)
    {
        $this->options = $options;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}