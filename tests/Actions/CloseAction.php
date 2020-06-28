<?php

namespace Lzpeng\StateProcess\Tests\Actions;

use Lzpeng\StateProcess\Contracts\ActionInterface;

class CloseAction implements ActionInterface
{
    public function execute($domainObject)
    {
        echo 'close...';
    }
}
