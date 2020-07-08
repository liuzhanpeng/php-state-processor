<?php

namespace Lzpeng\StateProcess;

use Lzpeng\StateProcess\Contracts\ActionInterface;
use Lzpeng\StateProcess\Contracts\TxCreatorInterface;
use Lzpeng\StateProcess\Exceptions\StateException;

/**
 * 状态流转处理器
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class Factory
{
    private function __construct()
    {
    }

    /**
     * 创建状态流转处理器
     *
     * @param array $transitions 流转配置
     * @param string $txCreatorClass 事务创建器类
     * @return \Lzpeng\StateProcess\Processor
     */
    public static function create(array $transitions, string $txCreatorClass): Processor
    {
        $processor = new Processor();

        $txCreatorClosure = static::makeTxCreatorClosure($txCreatorClass);

        foreach ($transitions as $k => $item) {
            $fromStates = [];
            foreach ($item['from'] as $state) {
                $fromStates[] = new State($state);
            }

            $actionClass = $item['action'];
            if (!class_exists($actionClass) || !in_array(ActionInterface::class, class_implements($actionClass))) {
                throw new StateException(sprintf('动作[%s]不存在或未实现ActionInterface', $actionClass));
            }

            $toState = new State($item['to']);

            $processor->addTransition($k, function () use ($fromStates, $toState, $actionClass, $txCreatorClosure) {
                return new Transition($fromStates, $toState, $actionClass, $txCreatorClosure);
            });

            if (isset($item['events'])) {
                foreach ($item['events'] as $event => $listeners) {
                    foreach ($listeners as $listener) {
                        $processor->addTransistionEvent($k, $event, $listener);
                    }
                }
            }
        }

        return $processor;
    }

    /**
     * 创建事务创建器闭包
     *
     * @param string $txCreatorClass 事务创建器类class
     * @return \Closure
     * @throws \Lzpeng\StateProcess\Exceptions\StateException
     */
    private static function makeTxCreatorClosure(string $txCreatorClass)
    {
        if (!class_exists($txCreatorClass) || !in_array(TxCreatorInterface::class, class_implements($txCreatorClass))) {
            throw new StateException(sprintf('事务创建器[%s]不存在或未实现\Lzpeng\StateProcess\Tx\TxCreatorInterface', $txCreatorClass));
        }

        return function () use ($txCreatorClass) {
            return new $txCreatorClass;
        };
    }
}
