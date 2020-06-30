<?php

use Lzpeng\StateProcess\Tests\Actions\AuditAction;
use Lzpeng\StateProcess\Tests\Actions\CloseAction;
use Lzpeng\StateProcess\Tests\Actions\FinishAction;
use Lzpeng\StateProcess\Tests\Actions\PayAction;
use Lzpeng\StateProcess\Tests\Actions\SubmitAction;
use Lzpeng\StateProcess\Transition;
use Lzpeng\StateProcess\Tx\NullTx;

return [
    'tx_creator_class' => NullTx::class,
    'transitions' => [
        'init' => [
            'from' => ['unknown'], // 来源状态
            'to' => 'inited', // 转换后的状态
            'action' => SubmitAction::class, // 状态转换时执行的动作
        ],
        'audit' => [
            'from' => ['inited'],
            'to' => 'audited',
            'action' => AuditAction::class,
            'events' => [
                Transition::EVENT_RUN_SUCCESS => [
                    function ($event) {
                        echo '审核成功....';
                    }
                ],
            ]
        ],
        'pay' => [
            'from' => ['audited'],
            'to' => 'payed',
            'action' => PayAction::class,
        ],
        'finish' => [
            'from' => ['payed'],
            'to' => 'finished',
            'action' => FinishAction::class,
        ],
        'close' => [
            'from' => ['inited', 'aduited', 'payed', 'finished'],
            'to' => 'closed',
            'action' => CloseAction::class,
        ],
    ]
];
