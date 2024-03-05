<?php

namespace AuroraWebSoftware\ACalendar\Models;

use AuroraWebSoftware\ACalendar\Collections\EventInstanceDTOCollection;
use AuroraWebSoftware\ACalendar\Contracts\EventableModelContract;
use AuroraWebSoftware\ACalendar\Enums\CollectionBreakdown;
use AuroraWebSoftware\ACalendar\Enums\RepeatFrequency;
use AuroraWebSoftware\ACalendar\Enums\Type;
use AuroraWebSoftware\ACalendar\Exceptions\EventParameterValidationException;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 *
 * @method static Builder|Eventable query()
 */
class Eventable extends Model implements EventableModelContract
{
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

    /**
     * returns all events series with all occurrences (like repeating events' instances) by given parameters
     *
     * @throws Exception
     */
    public function allAEventSeries(
        array|string $tagOrTags,
        Carbon $fromDate, Carbon $toDate,
        CollectionBreakdown $breakdown = CollectionBreakdown::DAY
    ): Collection {
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
        if ($breakdown == CollectionBreakdown::DAY) {

            // prepare the empty collection between given dates
            $datePeriod = CarbonPeriod::create($fromDate->format('Y-m-d'), $toDate->format('Y-m-d'));
            $eventSerieByDay = collect();

            /**
             * Collection shape for autocompletion and phpstan
             *
             * @var Collection<string, Collection<AEventInstanceDTO>> $eventSerieByDay
             */
            foreach ($datePeriod as $date) {
                $eventSerieByDay->put($date->format('Y-m-d'), collect());
            }

            // loop all aevent models
            foreach ($aevents as $aevent) {

                /**
                 * @var Event $aevent
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
                    if ($aevent['repeat_frequency'] == RepeatFrequency::DAY) {
                        $repeatFreqAdditionDay = 1 * $aevent['repeat_period'];
                    } elseif ($aevent['repeat_frequency'] == RepeatFrequency::WEEK) {
                        $repeatFreqAdditionDay = 7 * $aevent['repeat_period'];
                    } elseif ($aevent['repeat_frequency'] == RepeatFrequency::MONTH) {
                        // todo
                        throw new Exception('not implemented yet');
                    } elseif ($aevent['repeat_frequency'] == RepeatFrequency::YEAR) {
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
                             * @var Event $aevent
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
        Builder $query,
        string $tag,
        Carbon $fromDate, Carbon $toDate,
        CollectionBreakdown $breakdown = CollectionBreakdown::DAY
    ): Collection {

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

    public function getEventTitle(): ?string
    {
        return $this->name;
    }

    /**
     * @throws EventParameterValidationException
     */
    public function updateOrCreateEvent(string $key,
        Type $type,
        ?Carbon $start = null,
        ?Carbon $end = null,
        ?RepeatFrequency $repeatFrequency = null,
        ?int $repeatPeriod = null,
        ?Carbon $repeatUntil = null
    ): Event {
        if (! $start) {
            throw new EventParameterValidationException('start is missing.');
        }

        $data = [];

        if ($type == Type::DATE_ALL_DAY) {
            $data['start_date'] = $start->format('Y-m-d');
        } elseif ($type == Type::DATE_POINT) {
            $data['start_date'] = $start->format('Y-m-d');
        } elseif ($type == Type::DATETIME_POINT) {
            $data['start_datetime'] = $start->format('Y-m-d H:i:s');
        } elseif ($type == Type::DATE_RANGE) {

            if (! $end) {
                throw new EventParameterValidationException('end is missing.');
            }

            if ($start->gt($end)) {
                throw new EventParameterValidationException('start is greater than end.');
            }

            $data['start_date'] = $start->format('Y-m-d');
            $data['end_date'] = $end->format('Y-m-d');
        } elseif ($type == Type::DATETIME_RANGE) {

            if (! $end) {
                throw new EventParameterValidationException('end is missing.');
            }

            if ($start->gt($end)) {
                throw new EventParameterValidationException('start is greater than end.');
            }

            $data['start_datetime'] = $start->format('Y-m-d H:i:s');
            $data['end_datetime'] = $end->format('Y-m-d H:i:s');
        }

        if ($repeatFrequency && ! $repeatPeriod) {
            throw new EventParameterValidationException('repeatPeriod is missing.');
        }

        $data['repeat_frequency'] = $repeatFrequency?->value;
        $data['repeat_period'] = $repeatPeriod;

        if ($repeatUntil) {
            $data['repeat_until'] = $repeatUntil->format('Y-m-d H:i:s');
        }

        Event::query()->updateOrCreate(
            ['key' => $key],
            [
                'key' => $key,
                'type' => $type,
                'model_type' => self::getModelType(),
                'model_id' => $this->getModelId(),
                'title' => $this->getEventTitle(),
                ...$data,
            ]
        );

        return Event::query()->where('key', $key)->first();
    }

    /**
     * @return Event|Builder<Event>
     */
    public function event(string $key): Event|Builder
    {
        return Event::query()->where('key', $key)
            ->where('model_type', self::getModelType())
            ->where('model_id', $this->getModelId());
    }

    /**
     * @param  array<string>|null  $key
     * @return Event|Builder<Event>
     */
    public function events(?array $key = null): Event|Builder
    {
        if (! $key) {
            return Event::query()
                ->where('model_type', self::getModelType())
                ->where('model_id', $this->getModelId());
        }

        return Event::query()->whereIn('key', $key)
            ->where('model_type', self::getModelType())
            ->where('model_id', $this->getModelId());
    }

    public function deleteEvent(string $key): void
    {
        $this->event($key)->delete();
    }

    public function eventInstances(
        array|string|null $keyOrKeys,
        \Illuminate\Support\Carbon $start,
        \Illuminate\Support\Carbon $end
    ): EventInstanceDTOCollection {
        if (is_string($keyOrKeys)) {
            $keyOrKeys = [$keyOrKeys];
        }

        $events = $this->events($keyOrKeys)
            ->where(function (Builder $q) use ($start) {
                $q
                    ->where('start_date', '>=', $start->format('Y-m-d'))
                    ->orWhere('start_datetime', '>=', $start->format('Y-m-d H:i:s'));
            })
            ->where(function (Builder $q) use ($end) {
                $q
                    ->where('end_date', '<=', $end->format('Y-m-d'))
                    ->orWhere('end_datetime', '<=', $end->format('Y-m-d H:i:s'))
                    ->orWhereNull('end_datetime')
                    ->orWhere('repeat_until', '<=', $end->format('Y-m-d H:i:s'))
                    ->orWhere(function (Builder $q) {
                        $q->whereNotNull('repeat_frequency')->whereNull('repeat_until');
                    });
            })->get();

        // break down collection by day
        if ($breakdown == CollectionBreakdown::DAY) {

            // prepare the empty collection between given dates
            $datePeriod = CarbonPeriod::create($fromDate->format('Y-m-d'), $toDate->format('Y-m-d'));
            $eventSerieByDay = collect();

            /**
             * Collection shape for autocompletion and phpstan
             *
             * @var Collection<string, Collection<AEventInstanceDTO>> $eventSerieByDay
             */
            foreach ($datePeriod as $date) {
                $eventSerieByDay->put($date->format('Y-m-d'), collect());
            }

            // loop all aevent models
            foreach ($aevents as $aevent) {

                /**
                 * @var Event $aevent
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
                    if ($aevent['repeat_frequency'] == RepeatFrequency::DAY) {
                        $repeatFreqAdditionDay = 1 * $aevent['repeat_period'];
                    } elseif ($aevent['repeat_frequency'] == RepeatFrequency::WEEK) {
                        $repeatFreqAdditionDay = 7 * $aevent['repeat_period'];
                    } elseif ($aevent['repeat_frequency'] == RepeatFrequency::MONTH) {
                        // todo
                        throw new Exception('not implemented yet');
                    } elseif ($aevent['repeat_frequency'] == RepeatFrequency::YEAR) {
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
                             * @var Event $aevent
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

    public function scopeAllEventInstances(Builder $query, array|string|null $key, \Illuminate\Support\Carbon $start, \Illuminate\Support\Carbon $end): EventInstanceDTOCollection
    {

    }
}
