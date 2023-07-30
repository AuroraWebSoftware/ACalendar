<?php

use AuroraWebSoftware\ACalendar\Enums\AEventTypeEnum;
use AuroraWebSoftware\ACalendar\Models\Eventable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Artisan::call('migrate:fresh');

    $migration = include __DIR__.'/../database/migrations/create_acalendar_aevents_table.php';
    $migration->up();

    Schema::create('eventables', function (Blueprint $table) {
        $table->id();
        $table->string('title');

        $table->timestamps();
    });

    // $seeder = new SampleDataSeeder();
    // $seeder->run();
});

it('can test', function () {

    $eventable = Eventable::query()->updateOrCreate(
        ['title' => 'asd']
    );

    //dd($eventable);

    $eventable->updateOrCreateAEvent(
        eventType: AEventTypeEnum::DATE,
        eventTag: 'date',
        eventStartDate: \Carbon\Carbon::now()
    );

    dd($eventable->aEvent('date'));

    expect(true)->toBeTrue();
});
