<?php

namespace AG\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class AGUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
