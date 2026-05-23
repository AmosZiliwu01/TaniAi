<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('market_prices', function (Blueprint $t) {
            $t->id();
            $t->string('commodity');
            $t->string('region');
            $t->decimal('price', 10, 2);
            $t->string('unit')->default('kg');
            $t->decimal('change_percent', 6, 2)->default(0);
            $t->dateTime('recorded_at');
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('market_prices'); }
};
