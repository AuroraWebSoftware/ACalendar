<?php

namespace AuroraWebSoftware\ACalendar\Models;

use AuroraWebSoftware\ACalendar\Contracts\EventableModelContract;
use AuroraWebSoftware\ACalendar\Traits\HasEvents;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 *
 * @method static Builder|Eventable query()
 */
class Eventable extends Model implements EventableModelContract
{
    use HasEvents;

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

    public function getEventTitle(): ?string
    {
        return $this->name;
    }

    public function scopeAuthorized(Builder $query): void
    {
        $query->where('name', '=', 'event701');
    }

    public function getEventMetadata(): array
    {
        return ['key' => $this->name];
    }
}
