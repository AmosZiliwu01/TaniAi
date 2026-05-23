<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('community_posts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->longText('content');
            $t->string('image_path')->nullable();
            $t->integer('likes')->default(0);
            $t->string('category')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('community_posts'); }
};
