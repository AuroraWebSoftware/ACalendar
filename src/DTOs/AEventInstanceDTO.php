<?php

namespace AuroraWebSoftware\ACalendar\DTOs;

use AuroraWebSoftware\ACalendar\Contracts\ACalendarEventInstanceContract;
use AuroraWebSoftware\ACalendar\Enums\AEventTypeEnum;
use Carbon\Carbon;

/**
 * Represents an Instance of Event
 */
class AEventInstanceDTO implements ACalendarEventInstanceContract
{
    public int $eventId;

    public AEventTypeEnum $eventType;

    public string $tag;

    public ?string $modelType;

    public ?int $modelId;

    public string $name;

    public bool $allDay;

    public ?Carbon $startDate;

    public ?Carbon $endDate;

    public ?Carbon $startDatetime;

    public ?Carbon $endDatetime;

    public function __construct(
        $eventId, $eventType, $tag, $modelType, $modelId, $name,
        $allDay = false, $startDate = null, $endDate = null, $startDatetime = null, $endDatetime = null
    )
    {
        $this->eventId = $eventId;
        $this->eventType = $eventType;
        $this->tag = $tag;
        $this->modelType = $modelType;
        $this->modelId = $modelId;
        $this->name = $name;
        $this->allDay = $allDay;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->startDatetime = $startDatetime;
        $this->endDatetime = $endDatetime;
    }


}
