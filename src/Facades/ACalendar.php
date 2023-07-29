<?php

namespace AuroraWebSoftware\ACalendar\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \AuroraWebSoftware\ACalendar\ACalendar
 */
class ACalendar extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \AuroraWebSoftware\ACalendar\ACalendar::class;
    }
}
