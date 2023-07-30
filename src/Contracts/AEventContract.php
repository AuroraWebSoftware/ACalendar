<?php

namespace AuroraWebSoftware\ACalendar\Contracts;

use AuroraWebSoftware\ACalendar\DTOs\AEventDTO;
use AuroraWebSoftware\ACalendar\Enums\AEventCollectionBreakdownEnum;
use AuroraWebSoftware\ACalendar\Enums\AEventRepeatFrequencyEnum;
use AuroraWebSoftware\ACalendar\Enums\AEventTypeEnum;
use AuroraWebSoftware\ACalendar\Models\AEvent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface AEventContract
{
    public static function getModelType(): string;

    public function getModelId(): int;

    public function getModelName(): ?string;

    public function aEvent(string $tag): ?AEvent;

    public function updateOrCreateAEvent(
        AEventTypeEnum $eventType,
        string $eventTag,
        bool $allDay = false,
        Carbon $eventStartDate = null,
        Carbon $eventEndDate = null,
        Carbon $eventStartDatetime = null,
        Carbon $eventEndDatetime = null,
        AEventRepeatFrequencyEnum $repeatFrequency = null,
        int $repeatPeriod = null,
        Carbon $repeatUntil = null,
    ): AEvent;

    /**
     * @return Collection<string, Collection<AEventDTO>>
     */
    public function scopeAllAEventSeriesCollection(Builder $query, string $tag, Carbon $fromDate, Carbon $toDate, AEventCollectionBreakdownEnum $breakdown = AEventCollectionBreakdownEnum::DAY): Collection;
}
