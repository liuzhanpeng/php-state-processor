<?php

namespace Lzpeng\StateProcess\Contracts;

use Lzpeng\StateProcess\State;

/**
 * 状态流转时的动作接口
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
interface ActionInterface
{
    /**
     * 执行
     *
     * @param mixed $domainObject 要状态流转的业务对象
     * @param State $fromState 来源状态
     * @param State $toState 目标状态
     * @return void
     * @throws \Lzpeng\StateProcess\Exceptions\StateException
     */
    public function execute($domainObject, State $fromState, State $toState);
}
