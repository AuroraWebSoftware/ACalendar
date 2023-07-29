<?php

namespace AuroraWebSoftware\ACalendar\DTOs;

use AuroraWebSoftware\ACalendar\Enums\AEventTypeEnum;
use Carbon\Carbon;

/**
 * Represents an Instance of Event
 */
class AEventDTO
{
    public int $eventId;

    public AEventTypeEnum $eventType;

    public string $eventSerieId;

    public string $tag;

    public ?string $modelType;

    public ?int $modelId;

    public string $name;

    public bool $allDay;

    public ?Carbon $startDate;

    public ?Carbon $endDate;

    public ?Carbon $startDatetime;

    public ?Carbon $endDatetime;
}
