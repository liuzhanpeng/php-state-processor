<?php

namespace Lzpeng\StateProcess;

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
    public static function create(array $transitions, string $txCreatorClass)
    {
        $processor = new Processor($txCreatorClass);

        foreach ($transitions as $k => $item) {
            $fromStates = [];
            foreach ($item['form'] as $state) {
                $fromStates[] = new State($state);
            }

            $toState = new State($item['to']);

            $processor->addTransition($k, $fromStates, $toState, $item['action']);
        }

        return $processor;
    }
}
