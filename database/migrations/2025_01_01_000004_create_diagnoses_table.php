<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('diagnoses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('crop');
            $t->string('image_path')->nullable();
            $t->string('disease');
            $t->float('confidence')->default(0);
            $t->string('risk_level')->default('Sedang');
            $t->text('description')->nullable();
            $t->json('recommendations')->nullable();
            $t->string('status')->default('done');
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('diagnoses'); }
};
