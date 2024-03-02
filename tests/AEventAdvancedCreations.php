<?php

use AuroraWebSoftware\ACalendar\Enums\RepeatFrequency;
use AuroraWebSoftware\ACalendar\Enums\Type;
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
});

it('can create and get one or more date events', function () {

    $eventable = Eventable::query()->updateOrCreate(
        ['name' => 'date event']
    );

    $yesterday = Carbon::yesterday();
    $today = Carbon::now();
    $tomorrow = Carbon::tomorrow();

    $eventable->updateOrCreateAEvent(
        eventType: Type::DATE,
        eventTag: 'date_yesterday1',
        eventStartDate: $yesterday,
    );

    $eventSerie1 = $eventable->allAEventSeries('date_yesterday1', $yesterday, $yesterday);

    expect($eventSerie1->get($yesterday->format('Y-m-d')))
        ->toBeCollection()
        ->toHaveCount(1);

    $eventable->updateOrCreateAEvent(
        eventType: Type::DATE,
        eventTag: 'date_yesterday2',
        eventStartDate: $yesterday,
    );

    $eventSerie2 = $eventable->allAEventSeries(['date_yesterday1', 'date_yesterday2'], $yesterday, $yesterday);

    expect($eventSerie2->get($yesterday->format('Y-m-d')))
        ->toBeCollection()
        ->toHaveCount(2);

    $eventSerie3 = $eventable->allAEventSeries(['date_yesterday1', 'date_yesterday2'], $today, $today);

    expect($eventSerie3->get($yesterday->format('Y-m-d')))
        ->toBeNull();

    $eventable->updateOrCreateAEvent(
        eventType: Type::DATE,
        eventTag: 'date_today1',
        eventStartDate: $today,
    );

    $eventSerie4 = $eventable->allAEventSeries(['date_today1'], $today, $today);

    expect($eventSerie4->get($today->format('Y-m-d')))
        ->toBeCollection()
        ->toHaveCount(1);

    $eventSerie5 = $eventable->allAEventSeries(['date_today1', 'date_yesterday1', 'date_yesterday2'], $yesterday, $today);

    expect($eventSerie5->get($today->format('Y-m-d')))
        ->toBeCollection()
        ->toHaveCount(1)
        ->and($eventSerie5->get($yesterday->format('Y-m-d')))
        ->toBeCollection()
        ->toHaveCount(2);

});

it('can create a repeatable date event', function () {

    $eventable = Eventable::query()->updateOrCreate(
        ['name' => 'repeating date event']
    );

    $yesterday = Carbon::yesterday();
    $today = Carbon::now();
    $tomorrow = Carbon::tomorrow();
    $tenDaysLater = Carbon::now()->addDays(10);

    $eventable->updateOrCreateAEvent(
        eventType: Type::DATE,
        eventTag: 'date repeating',
        eventStartDate: $yesterday,
        repeatFrequency: RepeatFrequency::DAY,
        repeatPeriod: 1
    );

    $eventable->updateOrCreateAEvent(
        eventType: Type::DATE,
        eventTag: 'date repeating2',
        eventStartDate: $tomorrow,
        repeatFrequency: RepeatFrequency::DAY,
        repeatPeriod: 1
    );

    $eventSerie1 = $eventable->allAEventSeries(['date repeating'], $yesterday, $tomorrow);

    expect($eventSerie1->get($today->format('Y-m-d')))
        ->toBeCollection()
        ->toHaveCount(1)
        ->and($eventSerie1->get($yesterday->format('Y-m-d')))
        ->toBeCollection()
        ->toHaveCount(1)
        ->and($eventSerie1->get($tomorrow->format('Y-m-d')))
        ->toBeCollection()
        ->toHaveCount(2);
});

// todo
// date range
// all day events
// repeating periods
