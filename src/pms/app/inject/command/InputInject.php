<?php

namespace pms\app\inject\command;

interface InputInject
{

    public function getArguments(string $name = null);

    public function getOptions(string $name = null);

}