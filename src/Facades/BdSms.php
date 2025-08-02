<?php

namespace RmdMostakim\BdSms\Facades;

use Illuminate\Support\Facades\Facade;

class BdSms extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'bdsms';
    }
}
