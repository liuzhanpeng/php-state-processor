<?php

namespace Lzpeng\StateProcess\Tests\Actions;

use Lzpeng\StateProcess\Contracts\ActionInterface;

class SubmitAction implements ActionInterface
{
    public function execute($domainObject)
    {
        $domainObject->step = 'submit';
    }
}
