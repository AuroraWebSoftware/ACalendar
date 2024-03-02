<?php

namespace AuroraWebSoftware\ACalendar\Contracts;

use AuroraWebSoftware\ACalendar\Collections\EventCollection;
use AuroraWebSoftware\ACalendar\DTOs\EventInstanceDTO;
use AuroraWebSoftware\ACalendar\Enums\CollectionBreakdown;
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
     * @param array|null $key
     * @return Collection<?Event>
     */
    public function events(?array $key = null): Collection;

    public function createOrUpdateEvent(
        string          $key,
        Type            $type,
        Carbon          $start = null,
        Carbon          $end = null,
        RepeatFrequency $repeatFrequency = null,
        int             $repeatPeriod = null,
        Carbon          $repeatUntil = null,
    ): Event;

    public function deleteEvent(string $key): void;


    /**
     * @param array<string>|string|null $key
     * @param Carbon $start
     * @param Carbon $end
     */
    public function eventInstances(
        array|string|null $key,
        Carbon            $start,
        Carbon            $end,
    ): EventCollection;

    /**
     * @param array|string|null $key
     * @param Carbon $start
     * @param Carbon $end
     */
    public static function allEventInstances(
        array|string|null   $key,
        Carbon              $start,
        Carbon              $end,
    ): EventCollection;
}
