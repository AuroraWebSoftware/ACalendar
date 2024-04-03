<?php

namespace AuroraWebSoftware\ACalendar\Contracts;

use AuroraWebSoftware\ACalendar\Collections\EventInstanceDTOCollection;
use AuroraWebSoftware\ACalendar\DTOs\EventInstanceDTO;
use AuroraWebSoftware\ACalendar\Enums\RepeatFrequency;
use AuroraWebSoftware\ACalendar\Enums\Type;
use AuroraWebSoftware\ACalendar\Models\Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

interface EventableModelContract
{
    public static function getModelType(): string;

    public function getModelId(): int;

    public function getEventTitle(): ?string;

    public function updateOrCreateEvent(
        string $key,
        Type $type,
        ?Carbon $start = null,
        ?Carbon $end = null,
        ?RepeatFrequency $repeatFrequency = null,
        ?int $repeatPeriod = null,
        ?Carbon $repeatUntil = null,
    ): Event;

    /**
     * returns the event with the given key with polymorphic relation
     *
     * @param string $key
     * @return Event|Builder<Event>
     */
    public function event(string $key): Event|Builder;

    /**
     * returns the events of the model with the given keys with polymorphic relation
     * returns all if $key is null
     *
     * @param array|null $key
     * @return Event|Builder<Event>
     */
    public function events(?array $key = null): Event|Builder;

    public function deleteEvent(string $key): void;

    /**
     * gives all events and recurring occurrences between $start and $end and given keys for a model instance with polymorphic relation
     *
     * @return EventInstanceDTOCollection<EventInstanceDTO>
     */
    public function eventInstances(
        array|string|null $keyOrKeys,
        Carbon $start,
        Carbon $end,
    ): EventInstanceDTOCollection;

    /**
     * gives all events and recurring occurrences between $start and $end and given keys for a model instance with polymorphic relation
     *
     * @return EventInstanceDTOCollection<EventInstanceDTO>
     */
    public function scopeAllEventInstances(
        Builder $query,
        array|string|null $keyOrKeys,
        Carbon $start,
        Carbon $end,
    ): EventInstanceDTOCollection;
}
