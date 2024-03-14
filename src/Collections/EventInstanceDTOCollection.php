<?php

namespace AuroraWebSoftware\ACalendar\Collections;

use AuroraWebSoftware\ACalendar\DTOs\EventInstanceDTO;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class EventInstanceDTOCollection extends Collection
{


    /**
     * Group and sort by Calendar Day view
     * @return Collection
     */
    public function byDay(): Collection
    {
        $byDayCollection = collect();

        $this->each(function (EventInstanceDTO $eventInstanceDTO) use ($byDayCollection) {

            $period = CarbonPeriod::create($eventInstanceDTO->start, $eventInstanceDTO->end ?? $eventInstanceDTO->start);

            foreach ($period as $item) {

                $key = $item->format('Y-m-d');

                if ($byDayCollection->get($key) == null) {
                    $byDayCollection->put($key, collect());
                }
                $byDayCollection->get($key)->push($eventInstanceDTO);
            }
        });

        return $byDayCollection;
    }
}
