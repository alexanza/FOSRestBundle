<?php

namespace FOS\RestBundle\Model;

use FOS\RestBundle\Model\Patch;

interface PatchAbleInterface
{
    public function patch(Patch $patch);
}
