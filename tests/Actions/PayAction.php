<?php

namespace Lzpeng\StateProcess\Tests\Actions;

use Lzpeng\StateProcess\Contracts\ActionInterface;

class PayAction implements ActionInterface
{
    public function execute($domainObject)
    {
        echo 'pay...';
    }
}
