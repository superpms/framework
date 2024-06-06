<?php

namespace pms\contract;
interface AppInterface{
    /**
     * @return mixed|null|void 接口请求入口
     */

    public function entry();
}