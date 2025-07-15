<?php

namespace pms\contract;
interface AppInterface{

    /**
     * @return mixed|null|void 应用入口函数
     */
    public function entry();
}