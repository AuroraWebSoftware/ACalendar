<?php

use AuroraWebSoftware\ACalendar\Enums\AEventTypeEnum;
use AuroraWebSoftware\ACalendar\Exceptions\AEventParameterCompareException;
use AuroraWebSoftware\ACalendar\Exceptions\AEventParameterValidationException;
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

// date event

it('can create a date event', function () {

    $eventable = Eventable::query()->updateOrCreate(
        ['name' => 'date event']
    );

    $e = $eventable->updateOrCreateAEvent(
        eventType: AEventTypeEnum::DATE,
        eventTag: 'date',
        eventStartDate: Carbon::now(),
    );

    expect($e->toArray()['name'])->toBe('date event');
});

it('get exception with wrong parameters while creating a date event', function () {

    $eventable = Eventable::query()->updateOrCreate(
        ['name' => 'date event2']
    );

    $eventable->updateOrCreateAEvent(
        eventType: AEventTypeEnum::DATE,
        eventTag: 'date',
        eventStartDatetime: Carbon::now(),
    );

    expect(false)->toBeTrue();
})->expectException(AEventParameterValidationException::class);

// all day date event

it('can create all day date event', function () {

    $eventable = Eventable::query()->updateOrCreate(
        ['name' => 'all day event']
    );

    $e = $eventable->updateOrCreateAEvent(
        eventType: AEventTypeEnum::DATE,
        eventTag: 'all',
        allDay: true,
        eventStartDate: Carbon::now(),
    );

    expect($e->toArray()['name'])->toBe('all day event');
});

it('get exception while creating all day day date event with wrong parameters', function () {

    $eventable = Eventable::query()->updateOrCreate(
        ['name' => 'all day event2']
    );

    $e = $eventable->updateOrCreateAEvent(
        eventType: AEventTypeEnum::DATE,
        eventTag: 'all',
        allDay: true,
        eventEndDate: Carbon::now(),
    );

    expect($e->toArray()['name'])->toBe('all day event');
})->expectException(AEventParameterValidationException::class);

// datetime event

it('can create a datetime event', function () {

    $eventable = Eventable::query()->updateOrCreate(
        ['name' => 'datetime event']
    );

    $e = $eventable->updateOrCreateAEvent(
        eventType: AEventTypeEnum::DATETIME,
        eventTag: 'datetime',
        eventStartDatetime: Carbon::now(),
    );

    expect($e->toArray()['name'])->toBe('datetime event');
});

it('get exception with wrong parameters while creating a datetime event', function () {

    $eventable = Eventable::query()->updateOrCreate(
        ['name' => 'datetime event2']
    );

    $eventable->updateOrCreateAEvent(
        eventType: AEventTypeEnum::DATETIME,
        eventTag: 'date',
        eventEndDate: Carbon::yesterday(),
    );

    expect(false)->toBeTrue();
})->expectException(AEventParameterValidationException::class);

// date range event

it('can create a date range event', function () {

    $eventable = Eventable::query()->updateOrCreate(
        ['name' => 'date range event']
    );

    $now = Carbon::now();
    $yesterday = Carbon::yesterday();

    $e = $eventable->updateOrCreateAEvent(
        eventType: AEventTypeEnum::DATE_RANGE,
        eventTag: 'date range',
        eventStartDate: $yesterday,
        eventEndDate: $now,
    );

    expect($e->toArray()['name'])->toBe('date range event');
});

it('get exception with wrong parameters while creating a date range event', function () {

    $eventable = Eventable::query()->updateOrCreate(
        ['name' => 'date range event2']
    );

    $now = Carbon::now();
    $yesterday = Carbon::yesterday();

    $e = $eventable->updateOrCreateAEvent(
        eventType: AEventTypeEnum::DATE_RANGE,
        eventTag: 'date range',
        eventStartDate: $yesterday,
    );

    expect(false)->toBeTrue();
})->expectException(AEventParameterValidationException::class);

it('get exception with wrong dates while creating a date range event', function () {

    $eventable = Eventable::query()->updateOrCreate(
        ['name' => 'date range event3']
    );

    $now = Carbon::now();
    $yesterday = Carbon::yesterday();

    $e = $eventable->updateOrCreateAEvent(
        eventType: AEventTypeEnum::DATE_RANGE,
        eventTag: 'date range',
        eventStartDate: $now,
        eventEndDate: $yesterday
    );

    expect(false)->toBeTrue();
})->expectException(AEventParameterCompareException::class);

// datetime range event

it('can create a datetime range event', function () {

    $eventable = Eventable::query()->updateOrCreate(
        ['name' => 'datetime range event']
    );

    $now = Carbon::now();
    $tenMinBefore = Carbon::now()->subMinutes(10);

    $e = $eventable->updateOrCreateAEvent(
        eventType: AEventTypeEnum::DATETIME_RANGE,
        eventTag: 'date range',
        eventStartDatetime: $tenMinBefore,
        eventEndDatetime: $now,
    );

    expect($e->toArray()['name'])->toBe('datetime range event');
});

it('get exception with wrong parameters while creating a datetime range event', function () {

    $eventable = Eventable::query()->updateOrCreate(
        ['name' => 'datetime range event2']
    );

    $now = Carbon::now();
    $yesterday = Carbon::yesterday();

    $e = $eventable->updateOrCreateAEvent(
        eventType: AEventTypeEnum::DATETIME_RANGE,
        eventTag: 'date range',
        eventStartDatetime: $yesterday,
    );

    expect(false)->toBeTrue();
})->expectException(AEventParameterValidationException::class);

it('get exception with wrong dates while creating a datetime range event', function () {

    $eventable = Eventable::query()->updateOrCreate(
        ['name' => 'datetime range event3']
    );

    $now = Carbon::now();
    $yesterday = Carbon::yesterday();

    $e = $eventable->updateOrCreateAEvent(
        eventType: AEventTypeEnum::DATETIME_RANGE,
        eventTag: 'date range',
        eventStartDatetime: $now,
        eventEndDatetime: $yesterday
    );

    expect(false)->toBeTrue();
})->expectException(AEventParameterCompareException::class);
