<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add parent_id to comments for nested/reply support
        if (!Schema::hasColumn('comments', 'parent_id')) {
            Schema::table('comments', function (Blueprint $table) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('community_post_id');
                $table->foreign('parent_id')->references('id')->on('comments')->onDelete('cascade');
            });
        }

        // Add flagged column to community_posts for moderation
        if (!Schema::hasColumn('community_posts', 'flagged')) {
            Schema::table('community_posts', function (Blueprint $table) {
                $table->boolean('flagged')->default(false)->after('category');
            });
        }

        // Add image_path to community_posts (may already exist from original migration)
        if (!Schema::hasColumn('community_posts', 'image_path')) {
            Schema::table('community_posts', function (Blueprint $table) {
                $table->string('image_path')->nullable()->after('content');
            });
        }

        // Add soil_condition to diagnoses (replaces humidity for user-friendly input)
        if (!Schema::hasColumn('diagnoses', 'soil_condition')) {
            Schema::table('diagnoses', function (Blueprint $table) {
                $table->string('soil_condition')->nullable()->after('image_path');
            });
        }
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });

        Schema::table('community_posts', function (Blueprint $table) {
            $table->dropColumn(['flagged', 'image_path']);
        });

        Schema::table('diagnoses', function (Blueprint $table) {
            $table->dropColumn('soil_condition');
        });
    }
};
