<?php

namespace Lzpeng\StateProcess\Tests;

use Lzpeng\StateProcess\Processor;
use Lzpeng\StateProcess\State;
use Lzpeng\StateProcess\Tests\Actions\AuditAction;
use Lzpeng\StateProcess\Tests\Actions\CloseAction;
use Lzpeng\StateProcess\Tests\Actions\FinishAction;
use Lzpeng\StateProcess\Tests\Actions\PayAction;
use Lzpeng\StateProcess\Tests\Actions\SubmitAction;
use Lzpeng\StateProcess\Tests\Listeners\MessageListener;
use Lzpeng\StateProcess\Tests\Objects\Order;
use Lzpeng\StateProcess\Transition;
use Lzpeng\StateProcess\Tx\NullTx;
use PHPUnit\Framework\TestCase;

class ProcessorTest extends TestCase
{
    public function testExecute()
    {
        $state1 = new State('unknown');
        $state2 = new State('inited');
        $state3 = new State('audited');
        $state4 = new State('payed');
        $state5 = new State('finished');
        $state6 = new State('closed');

        $processor = new Processor();
        $txCreateorClosure = function () {
            return new NullTx();
        };
        $processor->addTransition('init', function () use ($state1, $state2, $txCreateorClosure) {
            return new Transition([$state1], $state2, SubmitAction::class, $txCreateorClosure);
        });
        $processor->addTransition('audit', function () use ($state2, $state3, $txCreateorClosure) {
            return new Transition([$state2], $state3, AuditAction::class, $txCreateorClosure);
        });
        $processor->addTransition('pay', function () use ($state3, $state4, $txCreateorClosure) {
            return new Transition([$state3], $state4, PayAction::class, $txCreateorClosure);
        });
        $processor->addTransition('finish', function () use ($state4, $state5, $txCreateorClosure) {
            return new Transition([$state4], $state5, FinishAction::class, $txCreateorClosure);
        });
        $processor->addTransition('close', function () use ($state2, $state3, $state4, $state5, $state6, $txCreateorClosure) {
            return new Transition([$state2, $state3, $state4, $state5], $state6, CloseAction::class, $txCreateorClosure);
        });
        $processor->addTransistionEvent('pay', Transition::EVENT_RUN_SUCCESS, function ($event) {
            echo '支付成功，闭包发送模析消息...';
        });
        $processor->addTransistionEvent('pay', Transition::EVENT_RUN_SUCCESS, MessageListener::class);

        $order = new Order();
        $order->setState($state1);

        $this->assertTrue($processor->can('init', $order));
        $this->assertFalse($processor->can('audit', $order));
        $this->assertFalse($processor->can('pay', $order));
        $this->assertFalse($processor->can('finish', $order));
        $this->assertFalse($processor->can('close', $order));

        $this->assertEquals($processor->getToState('init'), $state2);
        $processor->execute('init', $order);
        $this->assertEquals($order->state(), $state2);

        $processor->execute('audit', $order);
        $this->assertEquals($order->state(), $state3);

        $processor->execute('pay', $order);
        $this->assertEquals($order->state(), $state4);

        $processor->execute('finish', $order);
        $this->assertEquals($order->state(), $state5);
    }

    public function testClose()
    {
        $state1 = new State('unknown');
        $state2 = new State('inited');
        $state3 = new State('audited');
        $state4 = new State('payed');
        $state5 = new State('finished');
        $state6 = new State('closed');

        $processor = new Processor();
        $txCreateorClosure = function () {
            return new NullTx();
        };
        $processor->addTransition('init', function () use ($state1, $state2, $txCreateorClosure) {
            return new Transition([$state1], $state2, SubmitAction::class, $txCreateorClosure);
        });
        $processor->addTransition('audit', function () use ($state2, $state3, $txCreateorClosure) {
            return new Transition([$state2], $state3, AuditAction::class, $txCreateorClosure);
        });
        $processor->addTransition('pay', function () use ($state3, $state4, $txCreateorClosure) {
            return new Transition([$state3], $state4, PayAction::class, $txCreateorClosure);
        });
        $processor->addTransition('finish', function () use ($state4, $state5, $txCreateorClosure) {
            return new Transition([$state4], $state5, FinishAction::class, $txCreateorClosure);
        });
        $processor->addTransition('close', function () use ($state2, $state3, $state4, $state5, $state6, $txCreateorClosure) {
            return new Transition([$state2, $state3, $state4, $state5], $state6, CloseAction::class, $txCreateorClosure);
        });
        $order = new Order();
        $order->setState($state1);

        $this->assertTrue($processor->can('init', $order));
        $this->assertFalse($processor->can('audit', $order));
        $this->assertFalse($processor->can('pay', $order));
        $this->assertFalse($processor->can('finish', $order));
        $this->assertFalse($processor->can('close', $order));

        $processor->execute('init', $order);
        $this->assertEquals($order->state(), $state2);

        $processor->execute('audit', $order);
        $this->assertEquals($order->state(), $state3);

        $processor->execute('pay', $order);
        $this->assertEquals($order->state(), $state4);

        $processor->execute('close', $order);
        $this->assertEquals($order->state(), $state6);
    }
}
