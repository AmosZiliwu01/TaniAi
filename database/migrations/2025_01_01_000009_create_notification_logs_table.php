<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('notification_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->text('message');
            $t->string('type')->default('info');
            $t->timestamp('read_at')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('notification_logs'); }
};
