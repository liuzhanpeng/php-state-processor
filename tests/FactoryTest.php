<?php

namespace Lzpeng\StateProcess\Tests;

use Lzpeng\StateProcess\Factory;
use Lzpeng\StateProcess\State;
use Lzpeng\StateProcess\Tests\Objects\Order;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testCreate()
    {
        $config = require_once 'config.php';

        $processor = Factory::create($config['transitions'], $config['tx_creator_class']);

        $order = new Order();
        $order->setState(new State('unknown'));

        $this->assertTrue($processor->can('init', $order));
        $this->assertFalse($processor->can('audit', $order));
        $this->assertFalse($processor->can('pay', $order));
        $this->assertFalse($processor->can('finish', $order));
        $this->assertFalse($processor->can('close', $order));

        $processor->execute('init', $order);
        $this->assertEquals($order->state()->id(), 'inited');

        $processor->execute('audit', $order);
        $this->assertEquals($order->state()->id(), 'audited');

        $toState = $processor->getToState('pay');
        $this->assertEquals('payed', $toState->id());
        $processor->execute('pay', $order);
        $this->assertEquals($order->state()->id(), 'payed');

        $processor->execute('finish', $order);
        $this->assertEquals($order->state()->id(), 'finished');
    }
}
