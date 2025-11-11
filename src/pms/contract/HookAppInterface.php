<?php

namespace pms\contract;

/**
 * @method static mount
 * @method static run
 */
interface HookAppInterface{

    /**
     * 审计
     */
    public static function audit();
}