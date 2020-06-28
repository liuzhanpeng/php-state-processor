# php-state-processor

状态流转处理器

## 概念

- State 状态对象
- DomainObject 需要状态流转的业务域对象, 必须实现 \Lzpeng\StateProcess\Contracts\StatefulInterface
- Action 状态流转时执行的动作
- Transition 流转的抽象表示

## 使用方式

```php

$transitions = [
    'init' => [
        'from' => ['unknown'], // 来源状态
        'to' => 'inited', // 转换后的状态
        'action' => SubmitAction::class, // 状态转换时执行的动作
    ],
    'audit' => [
        'from' => ['inited'],
        'to' => 'audited',
        'action' => AuditAction::class,
    ], ...
];

// 动作与最终状态更改需要在同一事务中，保证原子性
// 通过 \Lzpeng\StateProcess\Contracts\TxInterface 实现自己的事务对象
$txCreator = NullTx::class; 

$processor = Factory::create($transitions, $txCreator);

// 业务域对象
$order = new Order();
$order->setState(new State('unknown'));

// 判断是否能执行指定流转
if ($processor->can('init', $order)) {
    // ...
}

// 执行流转
try {
    $processor->execut('init', $order);
} catch(StateException $ex) {
    // exception handle
}

```