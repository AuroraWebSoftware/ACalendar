<?php

use AuroraWebSoftware\ACalendar\Enums\AEventTypeEnum;
use AuroraWebSoftware\ACalendar\Models\Eventable;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Artisan::call('migrate:fresh');

    $migration = include __DIR__.'/../database/migrations/create_acalendar_aevents_table.php';
    $migration->up();

    Schema::create('eventables', function (Blueprint $table) {
        $table->id();
        $table->string('name');

        $table->timestamps();
    });

    // $seeder = new SampleDataSeeder();
    // $seeder->run();
});

it('can create a date event', function () {

    $eventable = Eventable::query()->updateOrCreate(
        ['name' => 'date event']
    );

    $yesterday = Carbon::yesterday();
    $now = Carbon::now();
    $tomorrow = Carbon::tomorrow();

    $e = $eventable->updateOrCreateAEvent(
        eventType: AEventTypeEnum::DATE,
        eventTag: 'date',
        eventStartDate: $yesterday,
    );

    $e = $eventable->updateOrCreateAEvent(
        eventType: AEventTypeEnum::DATE,
        eventTag: 'date2',
        eventStartDate: $now,
        repeatFrequency: \AuroraWebSoftware\ACalendar\Enums\AEventRepeatFrequencyEnum::DAILY,
        repeatPeriod: 1
    );

    // dd($eventable->aevent()->where('tag', 'date2')->get());

    $eventable->allAEventSeries(['date', 'date2'], $yesterday, $tomorrow);

    // dd($eventable->allAEventSeries('date', $yesterday, $tomorrow)->get());

    // expect($e->toArray()['name'])->toBe('date event');
});
