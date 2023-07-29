<?php

namespace AuroraWebSoftware\ACalendar\Models;

use AuroraWebSoftware\ACalendar\Contracts\AEventContract;
use AuroraWebSoftware\ACalendar\Enums\AEventCollectionBreakdownEnum;
use AuroraWebSoftware\ACalendar\Enums\AEventRepeatFrequencyEnum;
use AuroraWebSoftware\ACalendar\Enums\AEventTypeEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @method static Builder|Eventable query()
 */
class Eventable extends Model implements AEventContract
{
    use HasFactory;

    protected $fillable = ['title'];

    public static function getModelType(): string
    {
        return 'AuroraWebSoftware\ACalendar\Models\Eventable';
    }

    public function getModelId(): int
    {
        return $this->id;
    }

    public function getModelName(): ?string
    {
        return 'name';
    }

    public function aEvent(string $tag): ?AEvent
    {
        return AEvent::query()
            ->where('model_type', self::getModelType())
            ->where('model_id', $this->getModelId())
            ->where('tag', $tag)
            ->first();
    }

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
        Carbon $repeatUntil = null
    ): AEvent {
        // todo excepitonlar ve konteoller
        return AEvent::query()->updateOrCreate(
            ['tag' => $eventTag],
            [
                'event_type' => $eventType->value,
                'tag' => $eventTag,
                'repeat_frequency' => $repeatFrequency?->value,
                'repeat_period' => $repeatPeriod,
                'repeat_until' => $repeatUntil?->format('Y-m-d H:i:s'),
                'model_type' => self::getModelType(),
                'model_id' => $this->getModelId(),
                'name' => $this->getModelName(),
                'all_day' => $allDay,
                'start_date' => $eventStartDate?->format('Y-m-d'),
                'end_date' => $eventEndDate?->format('Y-m-d'),
                'start_datetime' => $eventStartDatetime?->format('Y-m-d H:i:s'),
                'end_datetime' => $eventEndDatetime?->format('Y-m-d H:i:s'),
            ]
        );
    }

    public function scopeAllAEventSeriesCollection(
        Builder $query, string $tag, Carbon $fromDate, Carbon $toDate,
        AEventCollectionBreakdownEnum $breakdown = AEventCollectionBreakdownEnum::DAY): Collection
    {
        $modelWithAEvents = $query->with('acalendar_aevents')
            ->where('model_type', self::getModelType())
            ->where('model_id', $this->getModelId())
            ->where('tag', $tag)
            ->where(function (Builder $q) use ($fromDate) {
                $q->where('start_date', '>', $fromDate)
                    ->orWhere('start_datetime', '>', $fromDate);
            })
            ->where(function (Builder $q) use ($toDate) {
                $q->where('end_date', '<', $toDate)
                    ->orWhere('end_datetime', '<', $toDate)
                    ->orWhere('repeat_until', '<', $toDate)
                    ->orWhereNull('repeat_until');
            });
    }
}
