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
    public int $code;

    public ?string $modelType;

    public ?int $modelId;

    public int $key;

    public Type $type;

    public string $title;

    public ?Carbon $start;

    public ?Carbon $end;

    public function __construct(
        int $code,
        ?string $modelType,
        ?int $modelId,
        int $key,
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
