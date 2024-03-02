<?php

namespace AuroraWebSoftware\ACalendar\Models;

use AuroraWebSoftware\ACalendar\Enums\RepeatFrequency;
use AuroraWebSoftware\ACalendar\Enums\Type;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static Builder|Event query()
 */
class Event extends Model
{
    protected $table = 'acalendar_events';

    protected $casts = [
        'repeat_until' => 'datetime',
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'event_type' => Type::class,
        'repeat_frequency' => RepeatFrequency::class,
    ];

    protected $fillable =
        ['key', 'type', 'repeat_frequency', 'repeat_period', 'repeat_until', 'model_type', 'model_id',
            'title', 'all_day', 'start_date', 'end_date', 'start_datetime', 'end_datetime'];

    public int $id;

    public string $tag;

    public string $type;

    public ?RepeatFrequency $repeat_frequency;

    public ?int $repeat_period;

    public ?Carbon $repeat_until;

    public ?string $model_type;

    public ?int $model_id;

    public string $title;
    public ?Carbon $start_date;

    public ?Carbon $end_date;

    public ?Carbon $start_datetime;

    public ?Carbon $end_datetime;

    /**
     * @throws Exception
     */
    public function start(): Carbon
    {
        if ($this->type == Type::DATE_POINT->value) {
            return $this->start_date;
        } elseif ($this->type == Type::DATETIME_POINT->value) {
            return $this->start_datetime;
        } elseif ($this->type == Type::DATE_ALL_DAY->value) {
            return $this->start_date;
        } elseif ($this->type == Type::DATE_RANGE->value) {
            return $this->start_date;
        } elseif ($this->type == Type::DATETIME_RANGE->value) {
            return $this->start_datetime;
        }
        throw new Exception('Invalid Event Type');
    }

    /**
     * @throws Exception
     */
    public function end(): ?Carbon
    {
        if ($this->type == Type::DATE_POINT->value) {
            return null;
        } elseif ($this->type == Type::DATETIME_POINT->value) {
            return null;
        } elseif ($this->type == Type::DATE_ALL_DAY->value) {
            return null;
        } elseif ($this->type == Type::DATE_RANGE->value) {
            return $this->end_date;
        } elseif ($this->type == Type::DATETIME_RANGE->value) {
            return $this->end_datetime;
        }
        throw new Exception('Invalid Event Type');
    }

}
