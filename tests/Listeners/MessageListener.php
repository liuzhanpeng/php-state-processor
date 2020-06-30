<?php

namespace Lzpeng\StateProcess\Tests\Listeners;

use Lzpeng\StateProcess\Contracts\EventListenerInterface;

class MessageListener implements EventListenerInterface
{
    public function handle(\Lzpeng\StateProcess\Event\Event $event)
    {
        echo '支付成功，MessageListener发送模析消息...';
    }
}
