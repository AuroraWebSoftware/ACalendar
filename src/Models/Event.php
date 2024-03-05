<?php

namespace AuroraWebSoftware\ACalendar\Models;

use AuroraWebSoftware\ACalendar\Collections\EventInstanceDTOCollection;
use AuroraWebSoftware\ACalendar\Enums\RepeatFrequency;
use AuroraWebSoftware\ACalendar\Enums\Type;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static Builder|Event query()
 * @method EventInstanceDTOCollection get();
 * @property int $id
 * @property string $key
 * @property Type $type
 * @property RepeatFrequency $repeat_frequency
 * @property int $repeat_period
 * @property Carbon $repeat_until
 * @property string $model_type
 * @property ?int $model_id
 * @property ?string $title
 * @property ?Carbon $start_date
 * @property ?Carbon $end_date
 * @property ?Carbon $start_datetime
 * @property ?Carbon $end_datetime
 *
 * attributes
 * @property ?Carbon $start
 * @property ?Carbon $end
 *
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
        'type' => Type::class,
        'repeat_frequency' => RepeatFrequency::class,
    ];

    protected $fillable =
        ['key', 'type', 'repeat_frequency', 'repeat_period', 'repeat_until', 'model_type', 'model_id',
            'title', 'all_day', 'start_date', 'end_date', 'start_datetime', 'end_datetime'];

    /**
     * @throws Exception
     */
    public function getStartAttribute(): Carbon
    {
        if ($this->type == Type::DATE_ALL_DAY) {
            return $this->start_date;
        } elseif ($this->type == Type::DATE_POINT) {
            return $this->start_date;
        } elseif ($this->type == Type::DATETIME_POINT) {
            return $this->start_datetime;
        } elseif ($this->type == Type::DATE_RANGE) {
            return $this->start_date;
        } elseif ($this->type == Type::DATETIME_RANGE) {
            return $this->start_datetime;
        }
        throw new Exception('Invalid Event Type');
    }

    /**
     * @throws Exception
     */
    public function getEndAttribute(): ?Carbon
    {
        if ($this->type == Type::DATE_ALL_DAY) {
            return null;
        } elseif ($this->type == Type::DATE_POINT) {
            return null;
        } elseif ($this->type == Type::DATETIME_POINT) {
            return null;
        } elseif ($this->type == Type::DATE_RANGE) {
            return $this->end_date;
        } elseif ($this->type == Type::DATETIME_RANGE) {
            return $this->end_datetime;
        }
        throw new Exception('Invalid Event Type');
    }
}
