<?php

namespace Lzpeng\StateProcess;

use Lzpeng\StateProcess\Contracts\StatefulInterface;
use Lzpeng\StateProcess\Event\Event;
use Lzpeng\StateProcess\Event\EventManager;
use Lzpeng\StateProcess\Exceptions\RejectedException;
use Lzpeng\StateProcess\Exceptions\StateException;

/**
 * 流转的抽象
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class Transition
{
    const EVENT_RUN_BEFORE = 'run_before';
    const EVENT_RUN_SUCCESS = 'run_success';
    const EVENT_RUN_FAILURE = 'run_failure';

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

    /**
     * 事件管理器
     *
     * @var \Lzpeng\StateProcess\Event\EventManager
     */
    private $eventManager;

    public function __construct(array $fromStates, State $toState, string $actionClass, \Closure $txCreatorClosure)
    {
        $this->fromStates = $fromStates;
        $this->toState = $toState;
        $this->actionClass = $actionClass;
        $this->txCreatorClosure = $txCreatorClosure;
    }

    /**
     * 返回事件管理器
     *
     * @return \Lzpeng\StateProcess\Event\EventManager
     */
    private function getEventManager()
    {
        if (is_null($this->eventManager)) {
            $this->eventManager = new EventManager();
        }

        return $this->eventManager;
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
     * 是否能运行流转
     *
     * @param StatefulInterface $domainObject 业务对象
     * @return boolean
     */
    public function can(StatefulInterface $domainObject)
    {
        if ($domainObject->state() === null) {
            return false;
        }

        foreach ($this->fromStates() as $state) {
            if ($domainObject->state()->id() === $state->id()) {
                return true;
            }
        }

        return false;
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
        $fromState = $domainObject->state();
        if (!$this->can($domainObject)) {
            throw new RejectedException(sprintf('当前状态[%s]不能流转到[%s]', $fromState->id(), $this->toState->id()), $fromState, $this->toState);
        }

        $txCreator = $this->txCreatorClosure;
        $tx = $txCreator()->begin();

        $action = new $this->actionClass;
        try {
            $this->getEventManager()->dispatch(self::EVENT_RUN_BEFORE, new Event([
                'domainObject' => $domainObject,
                'fromState' => $fromState,
                'toState' => $this->toState(),
            ]));

            $action->execute($domainObject, $fromState, $this->toState());
            $domainObject->setState($this->toState);
            $tx->commit();

            $this->getEventManager()->dispatch(self::EVENT_RUN_SUCCESS, new Event([
                'domainObject' => $domainObject,
                'fromState' => $fromState,
                'toState' => $this->toState(),
            ]));
        } catch (\Exception $ex) {
            $tx->rollback();

            $this->getEventManager()->dispatch(self::EVENT_RUN_FAILURE, new Event([
                'domainObject' => $domainObject,
                'fromState' => $fromState,
                'toState' => $this->toState(),
                'exception' => $ex,
            ]));
            throw $ex;
        }
    }

    /**
     * 注册事件监听器
     *
     * @param string $name 事件名称
     * @param string|callable $listener 事件监听器
     * @return void
     */
    public function addListener(string $name, $listener)
    {
        $this->getEventManager()->addListener($name, $listener);
    }

    /**
     * 移除事件监听器
     *
     * @param string $name
     * @param string|callable|null $listener 如果为null, 即移除事件对应所有监听器
     * @return void
     */
    public function removeListener(string $name, $listener = null)
    {
        $this->getEventManager()->removeListener($name, $listener);
    }
}
