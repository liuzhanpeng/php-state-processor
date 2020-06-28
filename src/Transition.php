<?php

namespace Lzpeng\StateProcess;

use Lzpeng\StateProcess\Exceptions\RejectedException;

/**
 * 流转的抽象
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class Transition
{
    /**
     * 支持的来源状态
     *
     * @var array<State>
     */
    private $fromStates;

    /**
     * 目录状态
     *
     * @var State
     */
    private $toState;

    /**
     * 动作类
     *
     * @var string
     */
    private $actionClass;

    /**
     * 事务创建器闭包
     *
     * @var \Closure
     */
    private $txCreatorClosure;

    public function __construct(array $fromStates, State $toState, string $actionClass, \Closure $txCreatorClosure)
    {
        $this->fromStates = $fromStates;
        $this->toState = $toState;
        $this->actionClass = $actionClass;
        $this->txCreatorClosure = $txCreatorClosure;
    }

    /**
     * 返回支持的来源状态
     *
     * @return array<State>
     */
    public function fromStates()
    {
        return $this->fromStates;
    }

    /**
     * 返回目录状态
     *
     * @return State
     */
    public function toState()
    {
        return $this->toState;
    }

    /**
     * 运行流转
     *
     * @param mixed $domainObject 业务对象
     * @return void
     * @throws \Lzpeng\StateProcess\Exceptions\StateException
     */
    public function run($domainObject)
    {
        $currentState = $domainObject->state();
        if (!in_array($currentState, $this->fromStates)) {
            throw new RejectedException(sprintf('当前状态[%s]不能流转到[%s]', $currentState, $this->toState), $currentState, $this->toState);
        }

        $txCreator = $this->txCreatorClosure;
        $tx = $txCreator()->begin();

        $action = new $this->actionClass;
        try {
            $action->execute($domainObject);
            $domainObject->setState($this->toState);
            $tx->commit();
        } catch (\Exception $ex) {
            $tx->rollback();
            throw $ex;
        }
    }
}
