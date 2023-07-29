<?php

namespace AuroraWebSoftware\ACalendar\Commands;

use Illuminate\Console\Command;

class ACalendarCommand extends Command
{
    public $signature = 'acalendar';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
