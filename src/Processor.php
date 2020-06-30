<?php

namespace Lzpeng\StateProcess;

use Lzpeng\StateProcess\Contracts\ActionInterface;
use Lzpeng\StateProcess\Contracts\StatefulInterface;
use Lzpeng\StateProcess\Exceptions\StateException;
use Lzpeng\StateProcess\Contracts\TxCreatorInterface;
use Lzpeng\StateProcess\Event\Event;
use Lzpeng\StateProcess\Event\EventManager;

/**
 * 状态流转处理器
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class Processor
{
    /**
     * 流转创建闭包列表
     *
     * @var array<\Closure>
     */
    private $transitionClosures = [];

    /**
     * 事务创建器闭包
     *
     * @var \Closure
     */
    private $txCreatorClosure;

    /**
     * 保存事件列表
     *
     * @var array
     */
    private $events;

    /**
     * 构造函数
     *
     * @param string $txCreatorClass 事务创建器类
     * @throws \Lzpeng\StateProcess\Exceptions\StateException
     */
    public function __construct(string $txCreatorClass)
    {
        if (!class_exists($txCreatorClass) || !in_array(TxCreatorInterface::class, class_implements($txCreatorClass))) {
            throw new StateException(sprintf('事务创建器[%s]不存在或未实现\Lzpeng\StateProcess\Tx\TxCreatorInterface', $txCreatorClass));
        }

        $this->txCreatorClosure = function () use ($txCreatorClass) {
            return new $txCreatorClass;
        };
    }

    /**
     * 添加流转
     *
     * @param string $id 流转标识
     * @param array<State> $fromStates 支持的来源状态
     * @param State $toState 目标状态
     * @param string $actionClass 动作类
     * @return void
     * @throws \Lzpeng\StateProcess\Exceptions\StateException
     */
    public function addTransition(string $id, array $fromStates, State $toState, string $actionClass)
    {
        if (isset($this->transitionClosures[$id])) {
            throw new StateException(sprintf('流转[%s]已存在，不能重复添加', $id));
        }

        if (!class_exists($actionClass) || !in_array(ActionInterface::class, class_implements($actionClass))) {
            throw new StateException(sprintf('动作[%s]不存在或未实现ActionInterface', $actionClass));
        }

        $this->transitionClosures[$id] = function () use ($fromStates, $toState, $actionClass) {
            return new Transition($fromStates, $toState, $actionClass, $this->txCreatorClosure);
        };
    }

    /**
     * 移除流转
     * 
     * @param string $id 流转标识
     * @return void
     */
    public function removeTransition(string $id)
    {
        unset($this->transitionClosures[$id]);
        $this->events[$id] = [];
    }

    /**
     * 是否存在指定流转
     *
     * @param string $id 流转标识
     * @return boolean
     */
    public function hasTransition(string $id)
    {
        return isset($this->transitionClosures[$id]);
    }

    /**
     * 为指定流转附加事件
     *
     * @param string $id 流转名称
     * @param string $event 事件名称
     * @param string|callable $listener 事件监听器
     * @return void
     */
    public function addTransistionEvent(string $id, string $event, $listener)
    {
        if (!isset($this->transitionClosures[$id])) {
            throw new StateException(sprintf('流转[%s]不存在', $id));
        }

        $this->events[$id][$event][] = $listener;
    }

    /**
     * 删除流转事件
     *
     * @param string $id 流转名称
     * @param string $event 事件名称
     * @param string|callable|null $listener 如果为null, 即移除事件对应所有监听器
     * @return void
     */
    public function removeTransitionEvent(string $id, string $event, $listener)
    {
        if (!isset($this->transitionClosures[$id])) {
            throw new StateException(sprintf('流转[%s]不存在', $id));
        }

        if (!isset($this->events[$id][$event])) {
            return;
        }

        if (is_null($listener)) {
            $this->events[$id][$event] = [];
            return;
        }

        foreach ($this->events[$id][$event] as $key => $item) {
            if ($item === $listener) {

                unset($this->events[$id][$event][$key]);
                break;
            }
        }
    }

    /**
     * 判断业务对象当前状态是否能进行指定流转
     *
     * @param string $id 流转标识
     * @param StatefulInterface $domainObject 业务对象
     * @return boolean
     */
    public function can(string $id, StatefulInterface $domainObject)
    {
        if (!$this->hasTransition($id)) {
            return false;
        }

        $transition = $this->transitionClosures[$id]();

        return $transition->can($domainObject);
    }

    /**
     * 执行指定流转
     *
     * @param string $id 流转标识
     * @param StatefulInterface $domainObject 业务对象
     * @return void
     * @throws \Lzpeng\StateProcess\Exceptions\StateException
     */
    public function execute(string $id, StatefulInterface $domainObject)
    {
        if (!$this->hasTransition($id)) {
            throw new StateException(sprintf('流转[%s]不存在', $id));
        }

        $transition = $this->transitionClosures[$id]();
        if (isset($this->events[$id])) {
            $events = $this->events[$id];
            foreach ($events as $name => $listeners) {
                foreach ($listeners as $listener) {
                    $transition->addListener($name, $listener);
                }
            }
        }

        try {
            $transition->run($domainObject);
        } catch (StateException $ex) {
            throw $ex;
        }
    }
}
