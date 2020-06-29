<?php

namespace Lzpeng\StateProcess\Tests\Actions;

use Lzpeng\StateProcess\Contracts\ActionInterface;
use Lzpeng\StateProcess\State;

class AuditAction implements ActionInterface
{
    public function execute($domainObject, State $fromState, State $toState)
    {
        echo 'audit...';
    }
}
