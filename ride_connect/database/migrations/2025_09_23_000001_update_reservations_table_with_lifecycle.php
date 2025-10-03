<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (! Schema::hasColumn('reservations', 'sharing_period')) {
                $table->string('sharing_period')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('reservations', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('sharing_period');
            }
            if (! Schema::hasColumn('reservations', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('approved_at');
            }
            if (! Schema::hasColumn('reservations', 'chat_enabled_at')) {
                $table->timestamp('chat_enabled_at')->nullable()->after('rejected_at');
            }
            if (! Schema::hasColumn('reservations', 'pre_check_completed_at')) {
                $table->timestamp('pre_check_completed_at')->nullable()->after('chat_enabled_at');
            }
            if (! Schema::hasColumn('reservations', 'pre_check_items')) {
                $table->json('pre_check_items')->nullable()->after('pre_check_completed_at');
            }
            if (! Schema::hasColumn('reservations', 'ride_started_at')) {
                $table->timestamp('ride_started_at')->nullable()->after('pre_check_items');
            }
            if (! Schema::hasColumn('reservations', 'ride_ended_at')) {
                $table->timestamp('ride_ended_at')->nullable()->after('ride_started_at');
            }
            if (! Schema::hasColumn('reservations', 'owner_return_status')) {
                $table->enum('owner_return_status', ['pending', 'waiting', 'confirmed'])->default('pending')->after('ride_ended_at');
            }
            if (! Schema::hasColumn('reservations', 'return_confirmed_at')) {
                $table->timestamp('return_confirmed_at')->nullable()->after('owner_return_status');
            }
            if (! Schema::hasColumn('reservations', 'post_check_completed_at')) {
                $table->timestamp('post_check_completed_at')->nullable()->after('return_confirmed_at');
            }
            if (! Schema::hasColumn('reservations', 'post_check_items')) {
                $table->json('post_check_items')->nullable()->after('post_check_completed_at');
            }
            if (! Schema::hasColumn('reservations', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('post_check_items');
            }
            if (! Schema::hasColumn('reservations', 'canceled_at')) {
                $table->timestamp('canceled_at')->nullable()->after('completed_at');
            }
            if (! Schema::hasColumn('reservations', 'cancel_reason')) {
                $table->string('cancel_reason')->nullable()->after('canceled_at');
            }
            if (! Schema::hasColumn('reservations', 'reject_reason')) {
                $table->string('reject_reason')->nullable()->after('cancel_reason');
            }
        });

        $newStatuses = [
            'pending',
            'approved',
            'pre_check_completed',
            'active',
            'ending_requested',
            'return_confirmed',
            'post_check_completed',
            'completed',
            'rejected',
            'canceled',
        ];

        if (DB::getDriverName() === 'sqlite') {
            Schema::table('reservations', function (Blueprint $table) {
                $table->string('status_tmp')->default('pending')->after('status');
            });

            DB::statement('UPDATE reservations SET status_tmp = status');

            Schema::table('reservations', function (Blueprint $table) {
                $table->dropColumn('status');
            });

            Schema::table('reservations', function (Blueprint $table) use ($newStatuses) {
                $table->enum('status', $newStatuses)->default('pending')->after('end_date');
            });

            DB::statement('UPDATE reservations SET status = status_tmp');

            Schema::table('reservations', function (Blueprint $table) {
                $table->dropColumn('status_tmp');
            });
        } else {
            DB::statement(
                "ALTER TABLE reservations MODIFY COLUMN status ENUM('" . implode("','", $newStatuses) . "') DEFAULT 'pending' NOT NULL"
            );
        }
    }

    public function down(): void
    {
        $originalStatuses = ['pending', 'approved', 'rejected', 'canceled'];

        if (DB::getDriverName() === 'sqlite') {
            Schema::table('reservations', function (Blueprint $table) {
                $table->string('status_tmp')->default('pending')->after('status');
            });

            DB::statement('UPDATE reservations SET status_tmp = status');

            Schema::table('reservations', function (Blueprint $table) {
                $table->dropColumn('status');
            });

            Schema::table('reservations', function (Blueprint $table) use ($originalStatuses) {
                $table->enum('status', $originalStatuses)->default('pending')->after('end_date');
            });

            DB::statement('UPDATE reservations SET status = status_tmp');

            Schema::table('reservations', function (Blueprint $table) {
                $table->dropColumn('status_tmp');
            });
        } else {
            DB::statement(
                "ALTER TABLE reservations MODIFY COLUMN status ENUM('" . implode("','", $originalStatuses) . "') DEFAULT 'pending' NOT NULL"
            );
        }

        Schema::table('reservations', function (Blueprint $table) {
            $dropColumns = [
                'sharing_period',
                'approved_at',
                'rejected_at',
                'chat_enabled_at',
                'pre_check_completed_at',
                'pre_check_items',
                'ride_started_at',
                'ride_ended_at',
                'owner_return_status',
                'return_confirmed_at',
                'post_check_completed_at',
                'post_check_items',
                'completed_at',
                'canceled_at',
                'cancel_reason',
                'reject_reason',
            ];

            $existing = array_filter($dropColumns, fn ($column) => Schema::hasColumn('reservations', $column));

            if (! empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
};
