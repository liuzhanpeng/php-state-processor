<?php

namespace Lzpeng\StateProcess\Tests\Actions;

use Lzpeng\StateProcess\Contracts\ActionInterface;

class AuditAction implements ActionInterface
{
    public function execute($domainObject)
    {
        echo 'audit...';
    }
}
