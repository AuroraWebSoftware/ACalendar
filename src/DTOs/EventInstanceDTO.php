<?php

namespace AuroraWebSoftware\ACalendar\DTOs;

use AuroraWebSoftware\ACalendar\Enums\Type;
use Carbon\Carbon;

/**
 * Represents an Instance of Event
 */
class EventInstanceDTO
{
    /**
     * Unique code for each event instance
     */
    public string $code;

    public ?string $modelType;

    public ?int $modelId;

    public string $key;

    public Type $type;

    public string $title;

    public ?Carbon $start;

    public ?Carbon $end;

    public function __construct(
        string $code,
        ?string $modelType,
        ?int $modelId,
        string $key,
        Type $type,
        string $title,
        ?Carbon $start = null,
        ?Carbon $end = null
    ) {
        $this->code = $code;
        $this->modelType = $modelType;
        $this->modelId = $modelId;
        $this->key = $key;
        $this->type = $type;
        $this->title = $title;
        $this->start = $start;
        $this->end = $end;
    }

    public function calendarStartDatetimePoint(): Carbon
    {
        return $this->start;
    }

    public function calendarEndDatetimePoint(): Carbon
    {
        return $this->end;
    }
}
