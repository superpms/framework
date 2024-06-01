<?php

namespace pms\contract;
interface ApplicationActionInterface{
    public function entry();
    public function file(mixed $data);
    public function success(mixed $data, $code = 200, array $other = []);
    public function error(mixed $message, $code = 500, array $other = []);
}