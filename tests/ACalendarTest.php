<?php

use AuroraWebSoftware\ACalendar\Enums\RepeatFrequency;
use AuroraWebSoftware\ACalendar\Enums\Type;
use AuroraWebSoftware\ACalendar\Exceptions\EventParameterValidationException;
use AuroraWebSoftware\ACalendar\Models\Eventable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Artisan::call('migrate:fresh');

    //$migration = include __DIR__.'/../database/migrations/create_acalendar_events_table.php';
    //$migration->up();

    Schema::create('eventables', function (Blueprint $table) {
        $table->id();
        $table->string('name');

        $table->timestamps();
    });

    // $seeder = new SampleDataSeeder();
    // $seeder->run();
});

it('can update or create a simple all day event', function () {

    $name1 = 'event10';
    $eventable1 = Eventable::query()->updateOrCreate(['name' => $name1]);

    $e = $eventable1->updateOrCreateEvent(
        key: 'key1',
        type: Type::DATE_ALL_DAY,
        start: Carbon::now(),
    );

    expect($e->key)->toBe('key1')
        ->and($e->title)->toBe($name1);

    $name2 = 'event11';
    $eventable2 = Eventable::query()->updateOrCreate(['name' => $name2]);

    $e = $eventable2->updateOrCreateEvent(
        key: 'key1',
        type: Type::DATE_ALL_DAY,
        start: Carbon::now(),
    );

    expect($e->key)->toBe('key1')
        ->and($e->title)->toBe($name2);
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

it('can get an event instances of an eventable and nonrepeating events using key or keys and conditions', function () {

    $name = 'event70';
    $eventable = Eventable::query()->updateOrCreate(['name' => $name]);

    $eventable->updateOrCreateEvent(
        key: 'key1',
        type: Type::DATE_ALL_DAY,
        start: Carbon::now(),
    );

    expect($eventable->eventInstances('key1',
        Carbon::now()->addDays(-10),
        Carbon::now()->addDays(-3)))
        ->toHaveCount(0);

    $eventable->updateOrCreateEvent(
        key: 'key2',
        type: Type::DATE_ALL_DAY,
        start: Carbon::tomorrow(),
    );

    expect($eventable->eventInstances('key2',
        Carbon::now(),
        Carbon::now()->addDays(3)))
        ->toHaveCount(1);

    $eventable->updateOrCreateEvent(
        key: 'key3',
        type: Type::DATE_POINT,
        start: Carbon::yesterday(),
    );

    expect($eventable->eventInstances('key3',
        Carbon::now(),
        Carbon::now()->addDays(3)))
        ->toHaveCount(0)
        ->and($eventable->eventInstances('key3',
            Carbon::yesterday(),
            Carbon::yesterday()))
        ->toHaveCount(1)
        ->and($eventable->eventInstances('key3',
            Carbon::yesterday(),
            Carbon::tomorrow()))
        ->toHaveCount(1)
        ->and($eventable->eventInstances('key3',
            Carbon::tomorrow(),
            Carbon::tomorrow()))
        ->toHaveCount(0)
        ->and($eventable->eventInstances('key3',
            Carbon::tomorrow(),
            Carbon::now()->addDays(10)))
        ->toHaveCount(0)
        ->and($eventable->eventInstances(['key1', 'key2', 'key3'],
            Carbon::yesterday(),
            Carbon::tomorrow()))
        ->toHaveCount(3)
        ->and($eventable->eventInstances(['key1', 'key2', 'key3'],
            Carbon::now(),
            Carbon::tomorrow()->addDays(5)))
        ->toHaveCount(2);

});

it('can get an event instances of an eventable and repeating events using key or keys', function () {

    $name = 'event200';
    $eventable = Eventable::query()->updateOrCreate(['name' => $name]);

    $eventable->updateOrCreateEvent(
        key: 'key1',
        type: Type::DATETIME_POINT,
        start: Carbon::now(),
        repeatFrequency: RepeatFrequency::DAY,
        repeatPeriod: 1
    );

    expect($eventable->eventInstances('key1', Carbon::now()->addDays(-3), Carbon::now()->addDays(3)))
        ->toHaveCount(4)
        ->and($eventable->eventInstances('key1', Carbon::now()->addDays(-10), Carbon::now()->addDays(-3)))
        ->toHaveCount(0)
        ->and($eventable->eventInstances('key1', Carbon::now()->addDay(), Carbon::now()->addDays(20)))
        ->toHaveCount(19);

    $eventable->updateOrCreateEvent(
        key: 'key2',
        type: Type::DATETIME_RANGE,
        start: Carbon::now(),
        end: Carbon::now()->addMinute(),
        repeatFrequency: RepeatFrequency::DAY,
        repeatPeriod: 1
    );

    expect($eventable->eventInstances('key2', Carbon::now()->addDays(-3), Carbon::now()->addDays(3)))
        ->toHaveCount(4)
        ->and($eventable->eventInstances('key2', Carbon::now()->addDays(-10), Carbon::now()->addDays(-3)))
        ->toHaveCount(0)
        ->and($eventable->eventInstances('key2', Carbon::now()->addDay(), Carbon::now()->addDays(20)))
        ->toHaveCount(19);

    $eventable->updateOrCreateEvent(
        key: 'key3',
        type: Type::DATETIME_RANGE,
        start: Carbon::now(),
        end: Carbon::now()->addMinute(),
        repeatFrequency: RepeatFrequency::DAY,
        repeatPeriod: 1,
        repeatUntil: Carbon::now()->addDays(3)
    );

    expect($eventable->eventInstances('key3', Carbon::now()->addDays(-3), Carbon::now()->addDays(3)))
        ->toHaveCount(4)
        ->and($eventable->eventInstances('key3', Carbon::now()->addDays(-10), Carbon::now()->addDays(-3)))
        ->toHaveCount(0)
        ->and($eventable->eventInstances('key3', Carbon::now()->addDay(), Carbon::now()->addDays(20)))
        ->toHaveCount(2)
        ->and($eventable->eventInstances(['key1', 'key2', 'key3'], Carbon::now()->addDays(-3), Carbon::now()->addDays(3)))
        ->toHaveCount(12)
        ->and($eventable->eventInstances(['key1', 'key2', 'key3'], Carbon::now()->addDays(-10), Carbon::now()->addDays(-3)))
        ->toHaveCount(0)
        ->and($eventable->eventInstances(['key1', 'key2', 'key3'], Carbon::now()->addDay(), Carbon::now()->addDays(20)))
        ->toHaveCount(40);

    $eventable->updateOrCreateEvent(
        key: 'key4',
        type: Type::DATE_ALL_DAY,
        start: Carbon::now(),
        end: Carbon::now()->addMinute(),
        repeatFrequency: RepeatFrequency::DAY,
        repeatPeriod: 1
    );

    expect($eventable->eventInstances('key4', Carbon::now()->addDays(-3), Carbon::now()->addDays(3)))
        ->toHaveCount(4)
        ->and($eventable->eventInstances('key4', Carbon::now()->addDays(-10), Carbon::now()->addDays(-3)))
        ->toHaveCount(0)
        ->and($eventable->eventInstances('key4', Carbon::now()->addDay(), Carbon::now()->addDays(20)))
        ->toHaveCount(19);

    $eventable->updateOrCreateEvent(
        key: 'key5',
        type: Type::DATE_ALL_DAY,
        start: Carbon::now(),
        end: Carbon::now()->addMinute(),
        repeatFrequency: RepeatFrequency::WEEK,
        repeatPeriod: 1
    );

    expect($eventable->eventInstances('key5', Carbon::now(), Carbon::now()->addWeeks(3)))
        ->toHaveCount(3);

    $eventable->updateOrCreateEvent(
        key: 'key5',
        type: Type::DATE_ALL_DAY,
        start: Carbon::now(),
        end: Carbon::now()->addMinute(),
        repeatFrequency: RepeatFrequency::WEEK,
        repeatPeriod: 2
    );

    expect($eventable->eventInstances('key5', Carbon::now(), Carbon::now()->addWeeks(4)))
        ->toHaveCount(2);

    $eventable->updateOrCreateEvent(
        key: 'key5',
        type: Type::DATE_ALL_DAY,
        start: Carbon::now(),
        end: Carbon::now()->addMinute(),
        repeatFrequency: RepeatFrequency::DAY,
        repeatPeriod: 1,
        repeatUntil: Carbon::now()->addDays(3)
    );

    expect($eventable->eventInstances('key5', Carbon::now(), Carbon::now()->addDays(10)))
        ->toHaveCount(3);

    $eventable->updateOrCreateEvent(
        key: 'key5',
        type: Type::DATE_ALL_DAY,
        start: Carbon::now(),
        end: Carbon::now()->addMinute(),
        repeatFrequency: RepeatFrequency::WEEK,
        repeatPeriod: 1,
        repeatUntil: Carbon::now()->addWeeks(5)
    );

    expect($eventable->eventInstances('key5', Carbon::now(), Carbon::now()->addWeeks(10)))
        ->toHaveCount(5);

});

it('can get all event instances of an eventable and nonrepeating events using key or keys and conditions', function () {

    $name = 'event300';
    $eventable1 = Eventable::query()->updateOrCreate(['name' => $name]);

    $name = 'event301';
    $eventable2 = Eventable::query()->updateOrCreate(['name' => $name]);

    $eventable1->updateOrCreateEvent(
        key: 'key1',
        type: Type::DATE_ALL_DAY,
        start: Carbon::now(),
    );

    $eventable2->updateOrCreateEvent(
        key: 'key1',
        type: Type::DATE_ALL_DAY,
        start: Carbon::now(),
    );

    expect(Eventable::allEventInstances('key1',
        Carbon::now()->addDays(-10),
        Carbon::now()->addDays(-3)))
        ->toHaveCount(0)
        ->and(Eventable::allEventInstances('key1',
            Carbon::now(),
            Carbon::now()->addDays(20)))
        ->toHaveCount(2);

    $eventable1->updateOrCreateEvent(
        key: 'key2',
        type: Type::DATETIME_RANGE,
        start: Carbon::now(),
        end: Carbon::now()->addMinute(),
    );

    $eventable2->updateOrCreateEvent(
        key: 'key3',
        type: Type::DATE_ALL_DAY,
        start: Carbon::now(),
    );

    expect(Eventable::allEventInstances(['key1', 'key2', 'key3'],
        Carbon::now()->addDays(-10),
        Carbon::now()->addDays(-3)))
        ->toHaveCount(0)
        ->and(Eventable::allEventInstances(['key1', 'key2', 'key3'],
            Carbon::now(),
            Carbon::now()->addDays(20)))
        ->toHaveCount(4);

    $eventable2->updateOrCreateEvent(
        key: 'key3',
        type: Type::DATE_ALL_DAY,
        start: Carbon::now(),
        repeatFrequency: RepeatFrequency::DAY,
        repeatPeriod: 1
    );

    expect(Eventable::allEventInstances(['key1', 'key2', 'key3'],
        Carbon::now()->addDays(-10),
        Carbon::now()->addDays(-3)))
        ->toHaveCount(0)
        ->and(Eventable::allEventInstances(['key1', 'key2', 'key3'],
            Carbon::now(),
            Carbon::now()->addDays(20)))
        ->toHaveCount(23);
});

it('can break event instances down into days with collection', function () {

    $name = 'event500';
    $eventable = Eventable::query()->updateOrCreate(['name' => $name]);

    $eventable->updateOrCreateEvent(
        key: 'key1',
        type: Type::DATE_ALL_DAY,
        start: Carbon::now(),
        end: Carbon::now()->addHour()
    );

    $eventable->updateOrCreateEvent(
        key: 'key2',
        type: Type::DATE_ALL_DAY,
        start: Carbon::now(),
        end: Carbon::now()->addHour()
    );

    $eventable->updateOrCreateEvent(
        key: 'key3',
        type: Type::DATE_ALL_DAY,
        start: Carbon::tomorrow(),
        end: Carbon::tomorrow()->addHour()
    );

    $byDay = $eventable->eventInstances(['key1', 'key2', 'key3'], Carbon::now(), Carbon::now()->addDays(10))
        ->byDay();

    expect($byDay->get(Carbon::now()->format('Y-m-d')))
        ->toHaveCount(2)
        ->and($byDay->get(Carbon::tomorrow()->format('Y-m-d')))
        ->toHaveCount(1)
        ->and($byDay->get(Carbon::yesterday()->format('Y-m-d')))
        ->toBeFalsy();

    $eventable->updateOrCreateEvent(
        key: 'key4',
        type: Type::DATE_ALL_DAY,
        start: Carbon::yesterday(),
        end: Carbon::yesterday()->addHour(),
        repeatFrequency: RepeatFrequency::DAY,
        repeatPeriod: 1
    );

    $byDay = $eventable->eventInstances(['key1', 'key2', 'key3', 'key4'], Carbon::yesterday(), Carbon::now()->addDays(10))
        ->byDay();

    expect($byDay->get(Carbon::now()->format('Y-m-d')))
        ->toHaveCount(3)
        ->and($byDay->get(Carbon::tomorrow()->format('Y-m-d')))
        ->toHaveCount(2)
        ->and($byDay->get(Carbon::yesterday()->format('Y-m-d')))
        ->toHaveCount(1)
        ->and($byDay->get(Carbon::tomorrow()->addDays(2)->format('Y-m-d')))
        ->toHaveCount(1)
        ->and($byDay->get(Carbon::tomorrow()->addDays(3)->format('Y-m-d')))
        ->toHaveCount(1);
});

it('can filter events using builder', function () {

    $name1 = 'event601';
    $eventable1 = Eventable::query()->updateOrCreate(['name' => $name1]);

    $eventable1->updateOrCreateEvent(
        key: 'key1',
        type: Type::DATETIME_POINT,
        start: Carbon::now(),
    );

    $name2 = 'event602';
    $eventable2 = Eventable::query()->updateOrCreate(['name' => $name2]);

    $eventable2->updateOrCreateEvent(
        key: 'key1',
        type: Type::DATETIME_POINT,
        start: Carbon::now(),
    );

    expect(
        Eventable::query()
            ->allEventInstances('key1', Carbon::now(), Carbon::now())
    )->toHaveCount(2);

    expect(
        Eventable::query()
            ->where('name', '=', $name1)
            ->allEventInstances('key1', Carbon::now(), Carbon::now())
    )->toHaveCount(1);

});

it('can get only authorized events using scope', function () {

    $name1 = 'event701';
    $eventable1 = Eventable::query()->updateOrCreate(['name' => $name1]);

    $eventable1->updateOrCreateEvent(
        key: 'key1',
        type: Type::DATETIME_POINT,
        start: Carbon::now(),
    );

    $name2 = 'event702';
    $eventable2 = Eventable::query()->updateOrCreate(['name' => $name2]);

    $eventable2->updateOrCreateEvent(
        key: 'key1',
        type: Type::DATETIME_POINT,
        start: Carbon::now(),
    );

    expect(
        Eventable::query()->authorized()
            ->allEventInstances('key1', Carbon::now(), Carbon::now())
    )->toHaveCount(1);

});
