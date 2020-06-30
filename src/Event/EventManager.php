<?php

namespace Lzpeng\StateProcess\Event;

use Lzpeng\StateProcess\Contracts\EventListenerInterface;
use Lzpeng\StateProcess\Exceptions\EventException;

/**
 * 事件管理器
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class EventManager
{
    private $events = [];

    /**
     * 为指定事件附加一个监听器
     *
     * @param string $name 事件名
     * @param string|callable $listener 监听器
     * @return void
     */
    public function addListener(string $name, $listener)
    {
        if (is_string($listener)) {
            if (!class_exists($listener) || !in_array(EventListenerInterface::class, class_implements($listener))) {
                throw new EventException(sprintf('事件监听器[%s]不存在或未实现\Lzpeng\StateProcess\Contracts\EventListenerInterface', $listener));
            }
            $this->events[$name][] = $listener;
        } else if (is_callable($listener)) {
            $this->events[$name][] = $listener;
        } else {
            throw new EventException('事件监听器创建者必实现Lzpeng\StateProcess\Contracts\EventListenerInterface或是callable对象');
        }
    }

    /**
     * 为指定事件移除监听器
     *
     * @param string $name 事件标识
     * @param string|callable|null $listenerCreator 事件监听器; 待移除的事件监听器，为null表示移除事件对应的所有监听器
     * @return void
     */
    public function removeListener(string $name, $listener = null)
    {
        if (!isset($this->events[$name])) {
            throw new EventException(sprintf('找不到待移除的事件[%s]', $name));
        }

        if (is_null($listener)) {
            $this->events[$name] = [];
            return;
        }

        foreach ($this->events[$name] as $key => $item) {
            if ($item === $listener) {

                unset($this->events[$name][$key]);
                break;
            }
        }
    }

    /**
     * 分发指定事件
     *
     * @param string $name 事件标识
     * @param Event $event 事件参数对象; 可为任意类型
     * @return void
     * @throws \Lzpeng\Auth\Exception\AuthException
     */
    public function dispatch(string $name, Event $event)
    {
        if (!isset($this->events[$name])) {
            return;
        }

        foreach ($this->events[$name] as $listener) {

            if (is_string($listener)) {
                $listenerObj = new $listener;
                $listenerObj->handle($event);
            } else {
                call_user_func($listener, $event);
            }

            if ($event->isStopped()) {
                // 事件已标识停止分发，直接返回
                break;
            }
        }
    }
}
