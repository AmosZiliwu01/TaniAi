<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('crop_records', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('crop');
            $t->string('field_name');
            $t->decimal('area',8,2)->nullable();
            $t->date('planting_date')->nullable();
            $t->string('status')->default('Tumbuh Baik');
            $t->text('notes')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('crop_records'); }
};
