<?php

namespace pms\helper\hrb;

interface HRBClientInterface
{

    public function execute(HRBRequestInterface $request):mixed;

}