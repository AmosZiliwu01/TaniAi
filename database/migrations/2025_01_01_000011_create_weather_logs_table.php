<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('weather_logs', function (Blueprint $t) {
            $t->id();
            $t->string('location');
            $t->float('temperature');
            $t->float('humidity');
            $t->float('rainfall')->default(0);
            $t->string('condition');
            $t->date('forecast_date');
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('weather_logs'); }
};
