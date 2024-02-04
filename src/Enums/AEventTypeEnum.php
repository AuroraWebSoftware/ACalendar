<?php

namespace AuroraWebSoftware\ACalendar\Enums;

/**
 * Event Types
 */
enum AEventTypeEnum: string
{
    case DATE = 'date';
    case DATETIME = 'datetime';
    case DATE_RANGE = 'date_range';
    case DATETIME_RANGE = 'datetime_range';

}
