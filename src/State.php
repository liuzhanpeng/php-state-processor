<?php

namespace Lzpeng\StateProcess;

/**
 * 表示一个状态
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class State
{
    /**
     * 状态标识
     *
     * @var integer|string
     */
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * 返回状态标识
     *
     * @return void
     */
    public function id()
    {
        return $this->id;
    }
}
