<?php

namespace WebId\Radis\Classes;

use Laravel\Forge\Forge as ForgeOriginal;

class Forge extends ForgeOriginal
{
    use ManagesSites;
}
