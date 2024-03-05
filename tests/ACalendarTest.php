<?php

use AuroraWebSoftware\ACalendar\Enums\Type;
use AuroraWebSoftware\ACalendar\Exceptions\EventParameterValidationException;
use AuroraWebSoftware\ACalendar\Models\Eventable;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Artisan::call('migrate:fresh');

    $migration = include __DIR__.'/../database/migrations/create_acalendar_events_table.php';
    $migration->up();

    Schema::create('eventables', function (Blueprint $table) {
        $table->id();
        $table->string('name');

        $table->timestamps();
    });

    // $seeder = new SampleDataSeeder();
    // $seeder->run();
});

it('can update or create a simple all day event', function () {

    $name = 'event10';
    $eventable = Eventable::query()->updateOrCreate(['name' => $name]);

    $e = $eventable->updateOrCreateEvent(
        key: 'key1',
        type: Type::DATE_ALL_DAY,
        start: Carbon::now(),
    );

    expect($e->key)->toBe('key1')
        ->and($e->title)->toBe($name);

    $name = 'event11';
    $eventable = Eventable::query()->updateOrCreate(['name' => $name]);

    $e = $eventable->updateOrCreateEvent(
        key: 'key1',
        type: Type::DATE_ALL_DAY,
        start: Carbon::now(),
    );

    expect($e->key)->toBe('key1')
        ->and($e->title)->toBe($name);
});

it('throws an exception without start date', function () {

    $name = 'event10';
    $eventable = Eventable::query()->updateOrCreate(['name' => $name]);

    $e = $eventable->updateOrCreateEvent(
        key: 'key1',
        type: Type::DATE_ALL_DAY,

    );

})->throws(EventParameterValidationException::class);

it('can update or create a simple date point event', function () {

    $name = 'event20';
    $eventable = Eventable::query()->updateOrCreate(['name' => $name]);

    $e = $eventable->updateOrCreateEvent(
        key: 'key1',
        type: Type::DATE_POINT,
        start: Carbon::now(),
    );

    expect($e->key)->toBe('key1')
        ->and($e->title)->toBe($name);

    $name = 'event21';
    $eventable = Eventable::query()->updateOrCreate(['name' => $name]);

    $e = $eventable->updateOrCreateEvent(
        key: 'key1',
        type: Type::DATE_POINT,
        start: Carbon::now(),
    );

    expect($e->key)->toBe('key1')
        ->and($e->title)->toBe($name);
});

it('can update or create a simple datetime point event', function () {

    $name = 'event30';
    $eventable = Eventable::query()->updateOrCreate(['name' => $name]);

    $e = $eventable->updateOrCreateEvent(
        key: 'key1',
        type: Type::DATETIME_POINT,
        start: Carbon::now(),
    );

    expect($e->key)->toBe('key1')
        ->and($e->title)->toBe($name);

    $name = 'event31';
    $eventable = Eventable::query()->updateOrCreate(['name' => $name]);

    $e = $eventable->updateOrCreateEvent(
        key: 'key1',
        type: Type::DATETIME_POINT,
        start: Carbon::now(),
    );

    expect($e->key)->toBe('key1')
        ->and($e->title)->toBe($name);
});

it('can update or create a simple date range event', function () {

    $name = 'event40';
    $eventable = Eventable::query()->updateOrCreate(['name' => $name]);

    $e = $eventable->updateOrCreateEvent(
        key: 'key1',
        type: Type::DATE_RANGE,
        start: Carbon::now(),
        end: Carbon::now()->addHour(),
    );

    expect($e->key)->toBe('key1')
        ->and($e->title)->toBe($name);

    $name = 'event41';
    $eventable = Eventable::query()->updateOrCreate(['name' => $name]);

    $e = $eventable->updateOrCreateEvent(
        key: 'key1',
        type: Type::DATE_RANGE,
        start: Carbon::now(),
        end: Carbon::now()->addHour(),
    );

    expect($e->key)->toBe('key1')
        ->and($e->title)->toBe($name);
});

it('throws exception when update or create a simple date range event without end date', function () {

    $name = 'event40';
    $eventable = Eventable::query()->updateOrCreate(['name' => $name]);

    $e = $eventable->updateOrCreateEvent(
        key: 'key1',
        type: Type::DATE_RANGE,
        start: Carbon::now(),
    );
})->throws(EventParameterValidationException::class);

it('can update or create a simple datetime range event', function () {

    $name = 'event40';
    $eventable = Eventable::query()->updateOrCreate(['name' => $name]);

    $e = $eventable->updateOrCreateEvent(
        key: 'key1',
        type: Type::DATETIME_RANGE,
        start: Carbon::now(),
        end: Carbon::now()->addHour(),
    );

    expect($e->key)->toBe('key1')
        ->and($e->title)->toBe($name);

    $name = 'event41';
    $eventable = Eventable::query()->updateOrCreate(['name' => $name]);

    $e = $eventable->updateOrCreateEvent(
        key: 'key1',
        type: Type::DATETIME_RANGE,
        start: Carbon::now(),
        end: Carbon::now()->addHour(),
    );

    expect($e->key)->toBe('key1')
        ->and($e->title)->toBe($name);
});

it('throws exception when update or create a simple datetime range event without end date', function () {

    $name = 'event40';
    $eventable = Eventable::query()->updateOrCreate(['name' => $name]);

    $e = $eventable->updateOrCreateEvent(
        key: 'key1',
        type: Type::DATETIME_RANGE,
        start: Carbon::now(),
    );
})->throws(EventParameterValidationException::class);

it('can get an event of an eventable using key or keys', function () {

    $name = 'event50';
    $eventable = Eventable::query()->updateOrCreate(['name' => $name]);

    $e = $eventable->updateOrCreateEvent(
        key: 'key1',
        type: Type::DATETIME_POINT,
        start: Carbon::now(),
    );

    expect($eventable->event('key1')->first()->key)->toBe('key1')
        ->and($eventable->event('key1')->first()->title)->toBe($name);

    $e2 = $eventable->updateOrCreateEvent(
        key: 'key2',
        type: Type::DATETIME_POINT,
        start: Carbon::now(),
    );

    $e3 = $eventable->updateOrCreateEvent(
        key: 'key3',
        type: Type::DATETIME_POINT,
        start: Carbon::now(),
    );

    expect($eventable->events()->count())->toBe(3);
    expect($eventable->events(['key1', 'key2'])->count())->toBe(2);

});

it('can delete an event of an eventable using key', function () {

    $name = 'event60';
    $eventable = Eventable::query()->updateOrCreate(['name' => $name]);

    $e = $eventable->updateOrCreateEvent(
        key: 'key1',
        type: Type::DATETIME_POINT,
        start: Carbon::now(),
    );

    expect($eventable->events()->count())->toBe(1);
    $eventable->deleteEvent('key1');
    expect($eventable->events()->count())->toBe(0);
});

// ----------------------------------------------------------------------------------------------------------------
// todo
// tüm creation'lar, event listesi, events, deletes
