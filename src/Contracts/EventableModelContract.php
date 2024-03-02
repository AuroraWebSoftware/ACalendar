<?php

namespace AuroraWebSoftware\ACalendar\Contracts;

use AuroraWebSoftware\ACalendar\Collections\EventCollection;
use AuroraWebSoftware\ACalendar\Enums\RepeatFrequency;
use AuroraWebSoftware\ACalendar\Enums\Type;
use AuroraWebSoftware\ACalendar\Models\Event;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface EventableModelContract
{
    public static function getModelType(): string;

    public function getModelId(): int;

    public function getEventTitle(): ?string;

    public function event(string $key): ?Event;

    /**
     * @return Collection<?Event>
     */
    public function events(?array $key = null): Collection;

    public function createOrUpdateEvent(
        string $key,
        Type $type,
        ?Carbon $start = null,
        ?Carbon $end = null,
        ?RepeatFrequency $repeatFrequency = null,
        ?int $repeatPeriod = null,
        ?Carbon $repeatUntil = null,
    ): Event;

    public function deleteEvent(string $key): void;

    /**
     * @param  array<string>|string|null  $key
     */
    public function eventInstances(
        array|string|null $key,
        Carbon $start,
        Carbon $end,
    ): EventCollection;

    public static function allEventInstances(
        array|string|null $key,
        Carbon $start,
        Carbon $end,
    ): EventCollection;
}
