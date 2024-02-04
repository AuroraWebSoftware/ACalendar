<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('acalendar_aevents', function (Blueprint $table) {
            $table->id();
            $table->string('event_type')->nullable(false)->index();
            $table->string('tag')->nullable(false)->index();
            $table->string('repeat_frequency')->nullable()->index();
            $table->unsignedInteger('repeat_period')->nullable()->index();
            $table->dateTime('repeat_until')->nullable()->index();
            $table->string('model_type')->nullable()->index();
            $table->unsignedInteger('model_id')->nullable()->index();
            $table->string('name')->nullable(false);
            $table->boolean('all_day')->default(false);
            $table->date('start_date')->nullable()->index();
            $table->date('end_date')->nullable()->index();
            $table->dateTime('start_datetime')->nullable()->index();
            $table->dateTime('end_datetime')->nullable()->index();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('acalendar_aevents');
    }
};
