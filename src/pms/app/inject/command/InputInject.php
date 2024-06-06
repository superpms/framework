<?php

namespace pms\app\inject\command;

interface InputInject
{

    public function getArgument(string $name = null);

    public function getOption(string $name = null);

}