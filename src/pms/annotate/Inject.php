<?php

namespace pms\annotate;

/**
 * @Annotation
 */
#[\Attribute] class Inject{
    public function __construct(string $classname){}
}