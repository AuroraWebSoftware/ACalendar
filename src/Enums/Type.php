<?php

namespace AuroraWebSoftware\ACalendar\Enums;

/**
 * Event Types
 */
enum Type: string
{
    case DATE_ALL_DAY = 'date_all_day';
    case DATE_POINT = 'date_point';
    case DATETIME_POINT = 'datetime_point';
    case DATE_RANGE = 'date_range';
    case DATETIME_RANGE = 'datetime_range';

}
