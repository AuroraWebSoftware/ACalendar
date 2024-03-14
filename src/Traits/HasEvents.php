<?php

namespace AuroraWebSoftware\ACalendar\Traits;

use AuroraWebSoftware\ACalendar\Collections\EventInstanceDTOCollection;
use AuroraWebSoftware\ACalendar\DTOs\EventInstanceDTO;
use AuroraWebSoftware\ACalendar\Enums\RepeatFrequency;
use AuroraWebSoftware\ACalendar\Enums\Type;
use AuroraWebSoftware\ACalendar\Exceptions\EventParameterValidationException;
use AuroraWebSoftware\ACalendar\Models\Event;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

trait HasEvents
{
    /**
     * update or create event using the key
     * $key must be unique, only one event can be created with the same key
     *
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
            [
                'key' => $key,
                'model_type' => self::getModelType(),
                'model_id' => $this->getModelId(),
            ],
            [
                'key' => $key,
                'type' => $type,
                'model_type' => self::getModelType(),
                'model_id' => $this->getModelId(),
                'title' => $this->getEventTitle(),
                ...$data,
            ]
        );

        return Event::query()
            ->where('key', $key)
            ->where('model_type', self::getModelType())
            ->where('model_id', $this->getModelId())
            ->first();
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

    /**
     * Events and recurring occurrences between $start and $end and given keys for a model instance
     *
     * @throws Exception
     */
    public function eventInstances(
        array|string|null $keyOrKeys,
        \Illuminate\Support\Carbon $start,
        \Illuminate\Support\Carbon $end
    ): EventInstanceDTOCollection {
        if (is_string($keyOrKeys)) {
            $keyOrKeys = [$keyOrKeys];
        }

        $events = $this->events($keyOrKeys)
            ->where(function (Builder $q) use ($start, $end) {
                $q
                    ->where('start_date', '>=', $start->format('Y-m-d'))
                    ->orWhere('start_datetime', '>=', $start->format('Y-m-d H:i:s'))
                    ->orWhere('repeat_until', '<=', $end->format('Y-m-d H:i:s'))
                    ->orWhere(function (Builder $q) {
                        $q->whereNotNull('repeat_frequency')->whereNull('repeat_until');
                    });
            })
            ->where(function (Builder $q) use ($end) {
                $q
                    ->where('end_date', '<=', $end->format('Y-m-d'))
                    ->orWhere('end_datetime', '<=', $end->format('Y-m-d H:i:s'))
                    ->orWhere('repeat_until', '<=', $end->format('Y-m-d H:i:s'))
                    ->orWhere(function (Builder $q) {
                        $q->whereNotNull('repeat_frequency')->whereNull('repeat_until');
                    })
                    ->orWhere(function (Builder $q) use ($end) {
                        $q->whereNull('end_date')->where('start_date', '<=', $end->format('Y-m-d'));
                    })
                    ->orWhere(function (Builder $q) use ($end) {
                        $q->whereNull('end_datetime')->where('start_datetime', '<=', $end->format('Y-m-d H:i:s'));
                    });
            })
            ->orderBy('start_date')
            ->orderBy('start_datetime')
            ->get();

        $eventInstanceDTOCollection = EventInstanceDTOCollection::make();

        // loop all event models
        foreach ($events as $event) {

            // for not repeating event, return one event instance dto only
            if ($event->repeat_frequency == null) {

                $eventInstanceDTO = new EventInstanceDTO(
                    code: $event->id.'_'.$event->key.'_'.$event->start->format('Y-m-d H:i:s'),
                    modelType: $event->model_type,
                    modelId: $event->model_id,
                    key: $event->key,
                    type: $event->type,
                    title: $event->title,
                    start: $event->start,
                    end: $event->end
                );

                $eventInstanceDTOCollection->push($eventInstanceDTO);

            } else {

                $repeatFreqAdditionDay = 0;
                if ($event->repeat_frequency == RepeatFrequency::DAY) {
                    $repeatFreqAdditionDay = 1 * $event->repeat_period;
                } elseif ($event->repeat_frequency == RepeatFrequency::WEEK) {
                    $repeatFreqAdditionDay = 7 * $event->repeat_period;
                } elseif ($event->repeat_frequency == RepeatFrequency::MONTH) {
                    throw new Exception('not implemented yet');
                } elseif ($event->repeat_frequency == RepeatFrequency::YEAR) {
                    throw new Exception('not implemented yet');
                }

                $instanceStartDate = $event->start_date;
                $instanceStartDatetime = $event->start_datetime;
                $instanceEndDate = $event->end_date;
                $instanceEndDatetime = $event->end_datetime;

                while (true) {

                    $dtoCreation = true;

                    if ($instanceStartDate && $instanceStartDate->lt($start)) {
                        $dtoCreation = false;
                    }

                    if ($instanceStartDatetime && $instanceStartDatetime->lt($start)) {
                        $dtoCreation = false;
                    }

                    if ($instanceStartDate &&
                        ($instanceStartDate->gt($end) ||
                            ($event->repeat_until && $instanceStartDate->gt($event->repeat_until))
                        )
                    ) {
                        break;
                    }

                    if ($instanceStartDatetime &&
                        ($instanceStartDatetime->gt($end) ||
                            ($event->repeat_until && $instanceStartDatetime->gt($event->repeat_until))
                        )
                    ) {
                        break;
                    }

                    if ($dtoCreation) {

                        $code = $event->id.'_'.$event->key.'_'.
                            $instanceStartDate?->format('Y-m-d H:i:s').
                            $instanceStartDatetime?->format('Y-m-d H:i:s');

                        $eventInstanceDTO = new EventInstanceDTO(
                            code: $code,
                            modelType: $event->model_type,
                            modelId: $event->model_id,
                            key: $event->key,
                            type: $event->type,
                            title: $event->title,
                            start: $instanceStartDate?->clone() ?? $instanceStartDatetime?->clone(),
                            end: $instanceEndDate?->clone() ?? $instanceEndDatetime?->clone() ?? null
                        );

                        $eventInstanceDTOCollection->push($eventInstanceDTO);
                    }

                    if ($instanceStartDate) {
                        $instanceStartDate->addDays($repeatFreqAdditionDay);
                    }

                    if ($instanceStartDatetime) {
                        $instanceStartDatetime->addDays($repeatFreqAdditionDay);
                    }

                    if ($instanceEndDate) {
                        $instanceEndDate->addDays($repeatFreqAdditionDay);
                    }

                    if ($instanceEndDatetime) {
                        $instanceEndDatetime->addDays($repeatFreqAdditionDay);
                    }

                }
            }
        }

        return $eventInstanceDTOCollection;
    }

    public function scopeAllEventInstances(
        Builder $query, array|string|null $keyOrKeys,
        \Illuminate\Support\Carbon $start,
        \Illuminate\Support\Carbon $end
    ): EventInstanceDTOCollection {

        if (is_string($keyOrKeys)) {
            $keyOrKeys = [$keyOrKeys];
        }

        $eventBuilder = null;

        if (! $keyOrKeys) {
            $eventBuilder = Event::query()
                ->where('model_type', self::getModelType());
        } else {
            $eventBuilder = Event::query()->whereIn('key', $keyOrKeys)
                ->where('model_type', self::getModelType());
        }

        $events = $eventBuilder
            ->where(function (Builder $q) use ($start, $end) {
                $q
                    ->where('start_date', '>=', $start->format('Y-m-d'))
                    ->orWhere('start_datetime', '>=', $start->format('Y-m-d H:i:s'))
                    ->orWhere('repeat_until', '<=', $end->format('Y-m-d H:i:s'))
                    ->orWhere(function (Builder $q) {
                        $q->whereNotNull('repeat_frequency')->whereNull('repeat_until');
                    });
            })
            ->where(function (Builder $q) use ($end) {
                $q
                    ->where('end_date', '<=', $end->format('Y-m-d'))
                    ->orWhere('end_datetime', '<=', $end->format('Y-m-d H:i:s'))
                    ->orWhere('repeat_until', '<=', $end->format('Y-m-d H:i:s'))
                    ->orWhere(function (Builder $q) {
                        $q->whereNotNull('repeat_frequency')->whereNull('repeat_until');
                    })
                    ->orWhere(function (Builder $q) use ($end) {
                        $q->whereNull('end_date')->where('start_date', '<=', $end->format('Y-m-d'));
                    })
                    ->orWhere(function (Builder $q) use ($end) {
                        $q->whereNull('end_datetime')->where('start_datetime', '<=', $end->format('Y-m-d H:i:s'));
                    });
            })
            ->orderBy('start_date', 'asc')
            ->orderBy('start_datetime', 'asc')
            ->get();

        $eventInstanceDTOCollection = EventInstanceDTOCollection::make();

        // loop all event models
        foreach ($events as $event) {

            // for not repeating event, return one event instance dto only
            if ($event->repeat_frequency == null) {

                $eventInstanceDTO = new EventInstanceDTO(
                    code: $event->id.'_'.$event->key.'_'.$event->start->format('Y-m-d H:i:s'),
                    modelType: $event->model_type,
                    modelId: $event->model_id,
                    key: $event->key,
                    type: $event->type,
                    title: $event->title,
                    start: $event->start,
                    end: $event->end
                );

                $eventInstanceDTOCollection->push($eventInstanceDTO);

            } else {

                $repeatFreqAdditionDay = 0;
                if ($event->repeat_frequency == RepeatFrequency::DAY) {
                    $repeatFreqAdditionDay = 1 * $event->repeat_period;
                } elseif ($event->repeat_frequency == RepeatFrequency::WEEK) {
                    $repeatFreqAdditionDay = 7 * $event->repeat_period;
                } elseif ($event->repeat_frequency == RepeatFrequency::MONTH) {
                    throw new Exception('not implemented yet');
                } elseif ($event->repeat_frequency == RepeatFrequency::YEAR) {
                    throw new Exception('not implemented yet');
                }

                $instanceStartDate = $event->start_date;
                $instanceStartDatetime = $event->start_datetime;
                $instanceEndDate = $event->end_date;
                $instanceEndDatetime = $event->end_datetime;

                while (true) {

                    $dtoCreation = true;

                    if ($instanceStartDate && $instanceStartDate->lt($start)) {
                        $dtoCreation = false;
                    }

                    if ($instanceStartDatetime && $instanceStartDatetime->lt($start)) {
                        $dtoCreation = false;
                    }

                    if ($instanceStartDate &&
                        ($instanceStartDate->gt($end) ||
                            ($event->repeat_until && $instanceStartDate->gt($event->repeat_until))
                        )
                    ) {
                        break;
                    }

                    if ($instanceStartDatetime &&
                        ($instanceStartDatetime->gt($end) ||
                            ($event->repeat_until && $instanceStartDatetime->gt($event->repeat_until))
                        )
                    ) {
                        break;
                    }

                    if ($dtoCreation) {

                        $code = $event->id.'_'.$event->key.'_'.
                            $instanceStartDate?->format('Y-m-d H:i:s').
                            $instanceStartDatetime?->format('Y-m-d H:i:s');

                        $eventInstanceDTO = new EventInstanceDTO(
                            code: $code,
                            modelType: $event->model_type,
                            modelId: $event->model_id,
                            key: $event->key,
                            type: $event->type,
                            title: $event->title,
                            start: $instanceStartDate?->clone() ?? $instanceStartDatetime?->clone(),
                            end: $instanceEndDate?->clone() ?? $instanceEndDatetime?->clone() ?? null
                        );

                        $eventInstanceDTOCollection->push($eventInstanceDTO);
                    }

                    if ($instanceStartDate) {
                        $instanceStartDate->addDays($repeatFreqAdditionDay);
                    }

                    if ($instanceStartDatetime) {
                        $instanceStartDatetime->addDays($repeatFreqAdditionDay);
                    }

                    if ($instanceEndDate) {
                        $instanceEndDate->addDays($repeatFreqAdditionDay);
                    }

                    if ($instanceEndDatetime) {
                        $instanceEndDatetime->addDays($repeatFreqAdditionDay);
                    }

                }
            }

        }

        return $eventInstanceDTOCollection;
    }
}
