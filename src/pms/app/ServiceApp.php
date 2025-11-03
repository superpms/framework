<?php

namespace pms\app;

use pms\contract\LifecycleInterface;

abstract class ServiceApp implements LifecycleInterface
{
    public static string $lifecycle;

    public static string $hookClass;

}