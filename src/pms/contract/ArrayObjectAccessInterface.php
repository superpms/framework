<?php

namespace pms\contract;
use ArrayAccess;
use Iterator;
use JsonSerializable;
interface ArrayObjectAccessInterface extends JsonSerializable,Iterator,ArrayAccess
{

}