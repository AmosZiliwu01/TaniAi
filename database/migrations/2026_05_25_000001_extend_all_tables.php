<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Extended diagnosis fields
        if (Schema::hasTable('diagnoses')) {
            Schema::table('diagnoses', function (Blueprint $t) {
                if (!Schema::hasColumn('diagnoses', 'plant_part')) {
                    $t->string('plant_part', 50)->nullable()->after('soil_condition');
                }
                if (!Schema::hasColumn('diagnoses', 'causes')) {
                    $t->json('causes')->nullable()->after('description');
                }
                if (!Schema::hasColumn('diagnoses', 'solutions')) {
                    $t->json('solutions')->nullable()->after('causes');
                }
                if (!Schema::hasColumn('diagnoses', 'prevention')) {
                    $t->json('prevention')->nullable()->after('solutions');
                }
                if (!Schema::hasColumn('diagnoses', 'health_status')) {
                    $t->string('health_status', 30)->nullable()->after('prevention');
                }
                if (!Schema::hasColumn('diagnoses', 'image_hash')) {
                    $t->string('image_hash', 32)->nullable()->after('image_path')->index();
                }
                if (!Schema::hasColumn('diagnoses', 'soil_condition')) {
                    $t->string('soil_condition', 50)->nullable()->after('image_hash');
                }
            });
        }

        // User avatar color (for letter avatars)
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'avatar_color')) {
            Schema::table('users', function (Blueprint $t) {
                $t->string('avatar_color', 20)->nullable()->after('avatar');
            });
        }

        // Community posts
        if (Schema::hasTable('community_posts')) {
            foreach (['flagged' => 'boolean', 'flag_reason' => 'text', 'image_path' => 'string'] as $col => $type) {
                if (!Schema::hasColumn('community_posts', $col)) {
                    Schema::table('community_posts', function (Blueprint $t) use ($col, $type) {
                        if ($type === 'boolean') $t->boolean($col)->default(false);
                        elseif ($type === 'text') $t->text($col)->nullable();
                        else $t->string($col)->nullable();
                    });
                }
            }
        }

        // Comments parent_id
        if (Schema::hasTable('comments') && !Schema::hasColumn('comments', 'parent_id')) {
            Schema::table('comments', function (Blueprint $t) {
                $t->unsignedBigInteger('parent_id')->nullable()->after('community_post_id');
                $t->foreign('parent_id')->references('id')->on('comments')->onDelete('cascade');
            });
        }

        // Notification type
        if (Schema::hasTable('notification_logs') && !Schema::hasColumn('notification_logs', 'type')) {
            Schema::table('notification_logs', function (Blueprint $t) {
                $t->string('type', 50)->default('system')->after('message');
            });
        }
    }

    public function down(): void {}
};
