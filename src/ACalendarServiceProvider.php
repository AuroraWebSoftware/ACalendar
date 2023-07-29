<?php

namespace AuroraWebSoftware\ACalendar;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use AuroraWebSoftware\ACalendar\Commands\ACalendarCommand;

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
            ->hasViews()
            ->hasMigration('create_acalendar_table')
            ->hasCommand(ACalendarCommand::class);
    }
}
