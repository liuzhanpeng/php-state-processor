<?php

namespace Lzpeng\StateProcess\Tests;

use Lzpeng\StateProcess\Exceptions\RejectedException;
use Lzpeng\StateProcess\State;
use Lzpeng\StateProcess\Tests\Actions\SubmitAction;
use Lzpeng\StateProcess\Tests\Objects\Order;
use Lzpeng\StateProcess\Tx\NullTx;
use Lzpeng\StateProcess\Transition;
use PHPUnit\Framework\TestCase;

class TransitionTest extends TestCase
{
    public function testRun()
    {
        $state1 = new State('unknown');
        $state2 = new State('inited');
        $state3 = new State('audited');
        $state4 = new State('payed');
        $state5 = new State('finished');

        $transition = new Transition([$state1], $state2, SubmitAction::class, function () {
            return new NullTx();
        });

        $order = new Order();
        $order->setState($state1);

        $this->assertTrue($transition->can($order));

        $transition->run($order);

        $this->assertEquals($order->state()->id(), $state2->id());
    }

    public function testRject()
    {
        $state1 = new State('unknown');
        $state2 = new State('inited');
        $state3 = new State('audited');
        $state4 = new State('payed');
        $state5 = new State('finished');

        $transition = new Transition([$state3], $state4, SubmitAction::class, function () {
            return new NullTx();
        });

        $order = new Order();
        $order->setState($state1);

        $this->assertFalse($transition->can($order));

        $this->expectException(RejectedException::class);

        $transition->run($order);
    }
}
