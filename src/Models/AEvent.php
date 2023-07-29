<?php

namespace AuroraWebSoftware\ACalendar\Models;


use AuroraWebSoftware\ACalendar\Enums\AEventRepeatFrequencyEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * @method static Builder|AEvent query()
 */
class AEvent extends Model
{
    use HasFactory;

    protected $table = 'acalendar_aevents';

    protected $fillable =
        ['event_type', 'tag', 'repeat_frequency', 'repeat_period', 'repeat_until', 'model_type', 'model_id',
            'name', 'all_day', 'start_date', 'end_date', 'start_datetime', 'end_datetime'];


    public int $id;
    public string $event_type;
    public string $tag;
    public ?AEventRepeatFrequencyEnum $repeat_frequency;
    public ?int $repeat_period;
    public ?Carbon $repeat_until;
    public ?string $model_type;
    public ?int $model_id;
    public string $name;
    public bool $all_day = false;
    public Carbon $start_date;
    public ?Carbon $end_date;
    public ?Carbon $start_datetime;
    public ?Carbon $end_datetime;




}