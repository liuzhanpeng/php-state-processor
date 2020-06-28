<?php

namespace Lzpeng\StateProcess\Tests\Objects;

use Lzpeng\StateProcess\Contracts\StatefulInterface;
use Lzpeng\StateProcess\State;

class Order implements StatefulInterface
{
    private $state;
    public $step;

    public function __construct()
    {
    }

    public function state(): State
    {
        return $this->state;
    }

    public function setState(\Lzpeng\StateProcess\State $state)
    {
        $this->state = $state;
    }
}
