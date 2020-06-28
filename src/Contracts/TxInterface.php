<?php

namespace Lzpeng\StateProcess\Contracts;

/**
 * 事务接口
 * 业务对象的setState可能发生异常，需要事务保证原子性
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
interface TxInterface
{
    /**
     * 提交事务
     *
     * @return void
     */
    public function commit();

    /**
     * 回滚事务
     *
     * @return void
     */
    public function rollback();
}
