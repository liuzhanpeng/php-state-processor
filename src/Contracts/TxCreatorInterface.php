<?php

namespace Lzpeng\StateProcess\Contracts;

/**
 * 事务创建器接口
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
interface TxCreatorInterface
{
    /**
     * 开始事务
     *
     * @return TxInterface
     */
    public function begin(): TxInterface;
}
