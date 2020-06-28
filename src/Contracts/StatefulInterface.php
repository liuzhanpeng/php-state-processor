<?php

namespace Lzpeng\StateProcess\Contracts;

use Lzpeng\StateProcess\State;

/**
 * 状态化接口
 * 需要状态流转的对象必须实现此接口
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
interface StatefulInterface
{
    /**
     * 返回状态
     *
     * @return \Lzpeng\StateProcess\State
     */
    public function state();

    /**
     * 设置状态
     *
     * @param \Lzpeng\StateProcess\State $state
     * @return void
     * @throws \Lzpeng\StateProcess\Exceptions\StateException
     */
    public function setState(State $state);
}
