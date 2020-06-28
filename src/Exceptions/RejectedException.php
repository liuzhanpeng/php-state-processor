<?php

namespace Lzpeng\StateProcess\Exceptions;

use Lzpeng\StateProcess\State;

/**
 * 拒绝状态流转异常
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class RejectedException extends StateException
{
    /**
     * 当前状态
     *
     * @var \Lzpeng\StateProcess\State
     */
    private $currentState;

    /**
     * 目录状态
     *
     * @var \Lzpeng\StateProcess\State
     */
    private $toState;

    /**
     * 构造函数
     *
     * @param string $message 异常信息
     * @param State $currentState 当前状态
     * @param State $toState 目标状态
     */
    public function __construct($message, State $currentState, State $toState)
    {
        $this->currentState = $currentState;
        $this->toState = $toState;

        parent::__construct($message, -1);
    }

    /**
     * 返回当前状态
     *
     * @return \Lzpeng\StateProcess\State
     */
    public function currentState()
    {
        return $this->currentState;
    }

    /**
     * 返回目标状态
     *
     * @return \Lzpeng\StateProcess\State
     */
    public function toState()
    {
        return $this->toState;
    }
}
