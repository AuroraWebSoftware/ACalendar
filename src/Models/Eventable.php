<?php

namespace AuroraWebSoftware\ACalendar\Models;

use AuroraWebSoftware\ACalendar\Contracts\AEventContract;
use AuroraWebSoftware\ACalendar\DTOs\AEventInstanceDTO;
use AuroraWebSoftware\ACalendar\Enums\AEventCollectionBreakdownEnum;
use AuroraWebSoftware\ACalendar\Enums\AEventRepeatFrequencyEnum;
use AuroraWebSoftware\ACalendar\Enums\AEventTypeEnum;
use AuroraWebSoftware\ACalendar\Exceptions\AEventParameterCompareException;
use AuroraWebSoftware\ACalendar\Exceptions\AEventParameterValidationException;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

/**
 * @method static Builder|Eventable query()
 */
class Eventable extends Model implements AEventContract
{
    use HasFactory;

    protected $fillable = ['name'];

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
        return $this->name;
    }

    /*
    public function aEvent(string $tag): ?AEvent
    {
        throw new \Exception();
        // todo çalışmıyor bunun yerine has one gibi bir şey kullanılmalı
        return AEvent::query()
            ->where('model_type', self::getModelType())
            ->where('model_id', $this->getModelId())
            ->where('tag', $tag)
            ->first();
    }
    */

    /**
     * @throws AEventParameterValidationException
     * @throws AEventParameterCompareException
     */
    public function updateOrCreateAEvent(
        AEventTypeEnum            $eventType,
        string                    $eventTag,
        bool                      $allDay = false,
        Carbon                    $eventStartDate = null,
        Carbon                    $eventEndDate = null,
        Carbon                    $eventStartDatetime = null,
        Carbon                    $eventEndDatetime = null,
        AEventRepeatFrequencyEnum $repeatFrequency = null,
        int                       $repeatPeriod = null,
        Carbon                    $repeatUntil = null
    ): AEvent
    {

        if ($repeatFrequency) {
            if (!$repeatPeriod) {
                throw new AEventParameterValidationException('repeatPeriod is missing.');
            }
        } else {
            $repeatPeriod = null;
            $repeatUntil = null;
        }

        if ($allDay === true) {
            if (!$eventStartDate || $eventEndDate || $eventStartDatetime || $eventEndDatetime) {
                throw new AEventParameterValidationException('allDay Event should only have $eventStartDate');
            }
        }

        if ($eventType === AEventTypeEnum::DATE) {
            if (!$eventStartDate || $eventEndDate || $eventStartDatetime || $eventEndDatetime) {
                throw new AEventParameterValidationException('Date Event should only have $eventStartDate');
            }
        }

        if ($eventType === AEventTypeEnum::DATETIME) {
            if (!$eventStartDatetime || $eventEndDate || $eventStartDate || $eventEndDatetime) {
                throw new AEventParameterValidationException('Datetime Event should only have $eventStartDatetime');
            }
        }

        if ($eventType === AEventTypeEnum::DATE_RANGE) {
            if (!$eventStartDate || !$eventEndDate || $eventStartDatetime || $eventEndDatetime) {
                throw new AEventParameterValidationException('Date range Event should only have $eventStartDate and $eventStartDate');
            }

            if ($eventStartDate->gt($eventEndDate) || $eventStartDate->eq($eventEndDate) || $eventStartDate->diffInDays($eventEndDate) < 1) {
                throw new AEventParameterCompareException('$eventEndDate must be greater then $eventEndDate');
            }

        }

        if ($eventType === AEventTypeEnum::DATETIME_RANGE) {
            if (!$eventStartDatetime || !$eventEndDatetime || $eventStartDate || $eventEndDate) {
                throw new AEventParameterValidationException('Date time range Event should only have $eventStartDatetime and $eventStartDatetime');
            }

            if ($eventStartDatetime->gt($eventEndDatetime) || $eventStartDatetime->eq($eventEndDatetime) || $eventStartDatetime->diffInMinutes($eventEndDatetime) < 1) {
                throw new AEventParameterCompareException('$eventEndDatetime must be greater then $eventStartDatetime');
            }
        }

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

    /**
     * polymorphic relation object
     * @return MorphMany
     */
    public function aevent(): MorphMany
    {
        return $this->morphMany(AEvent::class, 'model');
    }

    /**
     * returns all events series with all occurrences (like repeating events' instances) by given parameters
     * @param array|string $tagOrTags
     * @param Carbon $fromDate
     * @param Carbon $toDate
     * @param AEventCollectionBreakdownEnum $breakdown
     * @return Collection
     * @throws Exception
     */
    public function allAEventSeries(
        array|string                  $tagOrTags,
        Carbon                        $fromDate, Carbon $toDate,
        AEventCollectionBreakdownEnum $breakdown = AEventCollectionBreakdownEnum::DAY
    ): Collection
    {
        if (is_string($tagOrTags)) {
            $tagOrTags = [$tagOrTags];
        }

        // aevents object
        $aevents = $this->aevent()
            ->whereIn('tag', $tagOrTags)
            ->where(function (Builder $q) use ($fromDate) {
                $q
                    ->where('start_date', '>=', $fromDate->format('Y-m-d'))
                    ->orWhere('start_datetime', '>=', $fromDate->format('Y-m-d H:i:s'));
            })
            ->where(function (Builder $q) use ($toDate) {
                $q
                    ->where('end_date', '<=', $toDate->format('Y-m-d'))
                    ->orWhere('end_datetime', '<=', $toDate->format('Y-m-d H:i:s'))
                    ->orWhereNull('end_datetime')
                    ->orWhere('repeat_until', '<=', $toDate->format('Y-m-d H:i:s'))
                    ->orWhere(function (Builder $q) {
                        $q->whereNotNull('repeat_frequency')->whereNull('repeat_until');
                    });
            })->get();

        // break down collection by day
        if ($breakdown == AEventCollectionBreakdownEnum::DAY) {

            // prepare the empty collection between given dates
            $datePeriod = CarbonPeriod::create($fromDate->format('Y-m-d'), $toDate->format('Y-m-d'));
            $eventSerieByDay = collect();

            /**
             * Collection shape for autocompletion and phpstan
             * @var Collection<string, Collection<AEventInstanceDTO>> $eventSerieByDay
             */

            foreach ($datePeriod as $date) {
                $eventSerieByDay->put($date->format('Y-m-d'), collect());
            }

            // loop all aevent models
            foreach ($aevents as $aevent) {

                /**
                 * @var AEvent $aevent
                 */

                // for not repeating event, return one event instance dto only
                if ($aevent['repeat_frequency'] == null) {

                    // collection key broken-down by day
                    $collectionKey =
                        $aevent['start_date']->format('Y-m-d') ??
                        $aevent['start_datetime']->format('Y-m-d');

                    $aeventInstanceDTO = new AEventInstanceDTO(
                        eventId: $aevent['id'],
                        eventType: $aevent['event_type'],
                        tag: $aevent['tag'],
                        modelType: $aevent['model_type'],
                        modelId: $aevent['model_id'],
                        name: $aevent['name'],
                        allDay: $aevent['all_day'],
                        startDate: $aevent['start_date'],
                        endDate: $aevent['end_date'],
                        startDatetime: $aevent['start_datetime'],
                        endDatetime: $aevent['end_datetime'],
                    );

                    // push aevent instance dto into the collection
                    $eventSerieByDay->get($collectionKey)->push($aeventInstanceDTO);

                } else {

                    $repeatFreqAdditionDay = 0;
                    if ($aevent['repeat_frequency'] == AEventRepeatFrequencyEnum::DAY) {
                        $repeatFreqAdditionDay = 1 * $aevent['repeat_period'];
                    } else if ($aevent['repeat_frequency'] == AEventRepeatFrequencyEnum::WEEK) {
                        $repeatFreqAdditionDay = 7 * $aevent['repeat_period'];
                    } else if ($aevent['repeat_frequency'] == AEventRepeatFrequencyEnum::MONTH) {
                        // todo
                        throw new Exception('not implemented yet');
                    } else if ($aevent['repeat_frequency'] == AEventRepeatFrequencyEnum::YEAR) {
                        // todo
                        throw new Exception('not implemented yet');
                    }

                    /**
                     * @var array{
                     *     start_date:Carbon|null,
                     *     start_datetime:Carbon|null,
                     *     end_date:Carbon|null,
                     *     end_datetime:Carbon|null,
                     *     repeat_until:Carbon|null,
                     *     } $aevent
                     */

                    $startDate = $aevent['start_date'];
                    $startDatetime = $aevent['start_datetime'];
                    $endDate = $aevent['end_date'];
                    $endDatetime = $aevent['end_datetime'];

                    while (true) {

                        $dtoCreation = true;

                        // if start date is less than given from date
                        if ($startDate && $startDate->lt($fromDate)) {
                            $dtoCreation = false;
                        }

                        if ($startDatetime && $startDatetime->lt($fromDate)) {
                            $dtoCreation = false;
                        }


                        // if start date greater than given to date or 'repeat_until',
                        // break this aevent and go next
                        if ($startDate &&
                            ($startDate->gt($toDate) ||
                                ($aevent['repeat_until'] && $startDate->gt($aevent['repeat_until']))
                            )
                        ) {
                            break;
                        }

                        if ($startDatetime &&
                            ($startDatetime->gt($toDate) ||
                                ($aevent['repeat_until'] && $startDatetime->gt($aevent['repeat_until']))
                            )
                        ) {
                            break;
                        }


                        if ($dtoCreation) {

                            /**
                             * @var AEvent $aevent
                             */
                            $collectionKey =
                                $startDate->format('Y-m-d') ??
                                $startDatetime->format('Y-m-d');

                            $aeventInstanceDTO = new AEventInstanceDTO(
                                eventId: $aevent['id'],
                                eventType: $aevent['event_type'],
                                tag: $aevent['tag'],
                                modelType: $aevent['model_type'],
                                modelId: $aevent['model_id'],
                                name: $aevent['name'],
                                allDay: $aevent['all_day'],
                                startDate: $startDate,
                                endDate: $endDate,
                                startDatetime: $startDatetime,
                                endDatetime: $endDatetime,
                            );

                            $eventSerieByDay->get($collectionKey)->push($aeventInstanceDTO);
                        }

                        //

                        if ($startDate) {
                            $startDate->addDays($repeatFreqAdditionDay);
                        }

                        if ($startDatetime) {
                            $startDatetime->addDays($repeatFreqAdditionDay);
                        }

                        if ($endDate) {
                            $endDate->addDays($repeatFreqAdditionDay);
                        }

                        if ($endDatetime) {
                            $endDatetime->addDays($repeatFreqAdditionDay);
                        }

                    }
                }
            }

            return $eventSerieByDay;
        } else {
            throw new Exception('not implemented yet');
        }
    }

    public function scopeAllAEventSeriesx(
        Builder                       $query,
        string                        $tag,
        Carbon                        $fromDate, Carbon $toDate,
        AEventCollectionBreakdownEnum $breakdown = AEventCollectionBreakdownEnum::DAY
    ): Collection
    {

        $modelWithAEvents = $query->with(['aevent' => function ($q) use ($tag, $fromDate, $toDate) {
            $q
                ->where('model_type', self::getModelType())
                ->where('model_id', $this->getModelId())
                ->where('tag', $tag)
                ->where(function (Builder $q) use ($fromDate) {
                    $q->where('start_date', '>', $fromDate->format('Y-m-d'))
                        ->orWhere('start_datetime', '>', $fromDate->format('Y-m-d H:i:s'));
                })
                ->where(function (Builder $q) use ($toDate) {
                    $q->where('end_date', '<', $toDate->format('Y-m-d'))
                        ->orWhere('end_datetime', '<', $toDate->format('Y-m-d H:i:s'))
                        ->orWhere('repeat_until', '<', $toDate->format('Y-m-d H:i:s'))
                        ->orWhere(function (Builder $q) {
                            $q->whereNotNull('repeat_frequency')->whereNull('repeat_until');
                        });
                });
        }]);

        dd($modelWithAEvents->get());
    }
}
