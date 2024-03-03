<?php

namespace AuroraWebSoftware\ACalendar\Collections;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class EventCollection extends Collection
{
    public function groupAndSortByDay(): Collection
    {
        // sortby, sortbykeys dakullanılmalı

        return $this->groupBy(function (Model $event) {
            return $event->start_date;
        });
    }
}
