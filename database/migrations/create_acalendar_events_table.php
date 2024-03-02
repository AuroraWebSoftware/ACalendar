<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('acalendar_events', function (Blueprint $table) {
            $table->id();
            $table->string('model_type')->nullable()->index();
            $table->unsignedInteger('model_id')->nullable()->index();
            $table->string('key')->nullable(false)->index();
            $table->enum('type',
                ['date_point', 'datetime_point', 'date_all_day', 'date_range', 'datetime_range']
            )->default('date_point')->nullable(false)->index();
            $table->string('title')->nullable(false);
            $table->date('start_date')->nullable()->index();
            $table->date('end_date')->nullable()->index();
            $table->dateTime('start_datetime')->nullable()->index();
            $table->dateTime('end_datetime')->nullable()->index();
            $table->string('repeat_frequency')->nullable()->index();
            $table->unsignedInteger('repeat_period')->nullable()->index();
            $table->dateTime('repeat_until')->nullable()->index();
            $table->timestamps();

            $table->unique(['key', 'model_type', 'model_id']);
        });
    }

    public function down()
    {
        Schema::drop('acalendar_events');
    }
};
