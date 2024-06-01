<?php

namespace pms\inject;
interface SafeParams{

    public function get(string $name, string $default = null):mixed;

    public function getAll(): array;
}