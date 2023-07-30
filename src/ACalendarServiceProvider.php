<?php

namespace AuroraWebSoftware\ACalendar;

use AuroraWebSoftware\ACalendar\Commands\ACalendarCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ACalendarServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('acalendar')
            ->hasConfigFile()
            //->hasViews()
            //->hasCommand(ACalendarCommand::class);
            ->hasMigration('create_acalendar_events_table');
    }

    public function boot(): void
    {
        parent::boot();
    }
}
