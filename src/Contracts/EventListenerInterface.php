<?php

namespace Lzpeng\StateProcess\Contracts;

use Lzpeng\StateProcess\Event\Event;

/**
 * 事件监听器接口
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
interface EventListenerInterface
{
    /**
     * 处理事件
     *
     * @param \Lzpeng\StateProcess\Event\Event $event 事件对象
     * @return void
     * @throws \Lzpeng\StateProcess\Exceptions\EventException
     */
    public function handle(Event $event);
}
