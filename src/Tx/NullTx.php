<?php

namespace Lzpeng\StateProcess\Tx;

use Lzpeng\StateProcess\Contracts\TxCreatorInterface;
use Lzpeng\StateProcess\Contracts\TxInterface;

class NullTx implements TxCreatorInterface, TxInterface
{

    public function begin(): TxInterface
    {
        return $this;
    }

    public function commit()
    {
    }

    public function rollback()
    {
    }
}
