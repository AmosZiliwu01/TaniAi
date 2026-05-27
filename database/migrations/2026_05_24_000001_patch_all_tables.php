<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // image_hash on diagnoses for deduplication
        if (Schema::hasTable('diagnoses') && !Schema::hasColumn('diagnoses', 'image_hash')) {
            Schema::table('diagnoses', function (Blueprint $t) {
                $t->string('image_hash', 32)->nullable()->after('image_path');
                $t->index('image_hash');
            });
        }

        // flag_reason on community_posts for moderation
        if (Schema::hasTable('community_posts') && !Schema::hasColumn('community_posts', 'flag_reason')) {
            Schema::table('community_posts', function (Blueprint $t) {
                $t->text('flag_reason')->nullable()->after('flagged');
            });
        }

        // parent_id on comments (nested replies)
        if (Schema::hasTable('comments') && !Schema::hasColumn('comments', 'parent_id')) {
            Schema::table('comments', function (Blueprint $t) {
                $t->unsignedBigInteger('parent_id')->nullable()->after('community_post_id');
                $t->foreign('parent_id')->references('id')->on('comments')->onDelete('cascade');
            });
        }

        // flagged + image_path on community_posts
        if (Schema::hasTable('community_posts') && !Schema::hasColumn('community_posts', 'flagged')) {
            Schema::table('community_posts', function (Blueprint $t) {
                $t->boolean('flagged')->default(false)->after('category');
            });
        }
        if (Schema::hasTable('community_posts') && !Schema::hasColumn('community_posts', 'image_path')) {
            Schema::table('community_posts', function (Blueprint $t) {
                $t->string('image_path')->nullable()->after('content');
            });
        }

        // soil_condition on diagnoses
        if (Schema::hasTable('diagnoses') && !Schema::hasColumn('diagnoses', 'soil_condition')) {
            Schema::table('diagnoses', function (Blueprint $t) {
                $t->string('soil_condition')->nullable()->after('image_path');
            });
        }

        // Product fields on recommendations
        if (Schema::hasTable('recommendations')) {
            $cols = ['price', 'buy_type', 'buy_target', 'category'];
            foreach ($cols as $col) {
                if (!Schema::hasColumn('recommendations', $col)) {
                    Schema::table('recommendations', function (Blueprint $t) use ($col) {
                        if ($col === 'price') $t->unsignedInteger('price')->nullable()->after('content');
                        elseif ($col === 'buy_type') $t->string('buy_type', 20)->default('wa')->after('price');
                        elseif ($col === 'buy_target') $t->string('buy_target', 500)->nullable()->after('buy_type');
                        elseif ($col === 'category') $t->string('category', 100)->nullable()->after('buy_target');
                    });
                }
            }
        }

        // type column on notification_logs
        if (Schema::hasTable('notification_logs') && !Schema::hasColumn('notification_logs', 'type')) {
            Schema::table('notification_logs', function (Blueprint $t) {
                $t->string('type', 50)->default('system')->after('message');
            });
        }
    }

    public function down(): void
    {
        // Reverse selectively — safe
        if (Schema::hasTable('diagnoses') && Schema::hasColumn('diagnoses', 'image_hash')) {
            Schema::table('diagnoses', fn($t) => $t->dropColumn('image_hash'));
        }
        if (Schema::hasTable('community_posts') && Schema::hasColumn('community_posts', 'flag_reason')) {
            Schema::table('community_posts', fn($t) => $t->dropColumn('flag_reason'));
        }
    }
};
