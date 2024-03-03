<?php

namespace AuroraWebSoftware\ACalendar\Contracts;

use AuroraWebSoftware\ACalendar\Collections\EventCollection;
use AuroraWebSoftware\ACalendar\DTOs\EventInstanceDTO;
use AuroraWebSoftware\ACalendar\Enums\RepeatFrequency;
use AuroraWebSoftware\ACalendar\Enums\Type;
use AuroraWebSoftware\ACalendar\Models\Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;

interface EventableModelContract
{
    public static function getModelType(): string;

    public function getModelId(): int;

    public function getEventTitle(): ?string;

    /**
     * returns the event with the given key with polymorphic relation
     *
     * @return MorphOne<Event>
     */
    public function event(string $key): MorphOne;

    /**
     * returns the events of the model with the given keys with polymorphic relation
     * returns all if $key is null
     *
     * @return MorphMany<Event>
     */
    public function events(?array $key = null): MorphMany;

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
     * gives all events and recurring occurrences between $start and $end and given keys for a model instance with polymorphic relation
     *
     * @param  array<string>|string|null  $key
     * @return EventCollection<EventInstanceDTO>
     */
    public function eventInstances(
        array|string|null $key,
        Carbon $start,
        Carbon $end,
    ): EventCollection;

    public function scopeAllEventInstances(
        Builder $query,
        array|string|null $key,
        Carbon $start,
        Carbon $end,
    ): EventCollection;
}
