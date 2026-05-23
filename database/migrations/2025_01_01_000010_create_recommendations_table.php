<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('recommendations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('crop')->nullable();
            $t->string('title');
            $t->text('content');
            $t->string('priority')->default('medium');
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('recommendations'); }
};
