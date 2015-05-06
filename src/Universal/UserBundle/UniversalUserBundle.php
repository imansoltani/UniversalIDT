<?php

namespace Universal\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class UniversalUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
