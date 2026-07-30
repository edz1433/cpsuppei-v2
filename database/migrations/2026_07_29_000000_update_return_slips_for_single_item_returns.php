<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('return_slips', function (Blueprint $table) {
            if (!Schema::hasColumn('return_slips', 'returned_by_id')) {
                $table->unsignedBigInteger('returned_by_id')->nullable()->after('campus_id');
            }

            if (!Schema::hasColumn('return_slips', 'returned_by_name')) {
                $table->string('returned_by_name')->nullable()->after('returned_by_id');
            }

            if (!Schema::hasColumn('return_slips', 'received_by')) {
                $table->unsignedBigInteger('received_by')->nullable()->after('returned_by_name');
            }

            if (!Schema::hasColumn('return_slips', 'returned_at')) {
                $table->timestamp('returned_at')->nullable()->after('received_by');
            }
        });

        // A return is now recorded first and dispositioned later, so the type is no
        // longer chosen up front. Widen the column and default it to "Return".
        DB::statement("ALTER TABLE `return_slips` MODIFY `request_type` VARCHAR(50) NOT NULL DEFAULT 'Return'");
        DB::statement("ALTER TABLE `return_slips` MODIFY `status` VARCHAR(50) NOT NULL DEFAULT 'Returned'");

        Schema::table('return_slip_items', function (Blueprint $table) {
            if (!Schema::hasColumn('return_slip_items', 'previous_remarks')) {
                $table->string('previous_remarks')->nullable()->after('current_status');
            }

            if (!Schema::hasColumn('return_slip_items', 'action_type')) {
                $table->string('action_type', 50)->nullable()->after('status');
            }

            if (!Schema::hasColumn('return_slip_items', 'transferred_to_enduser_id')) {
                $table->unsignedBigInteger('transferred_to_enduser_id')->nullable()->after('action_type');
            }

            if (!Schema::hasColumn('return_slip_items', 'transferred_to_enduser_name')) {
                $table->string('transferred_to_enduser_name')->nullable()->after('transferred_to_enduser_id');
            }

            if (!Schema::hasColumn('return_slip_items', 'transferred_to_office_id')) {
                $table->unsignedBigInteger('transferred_to_office_id')->nullable()->after('transferred_to_enduser_name');
            }

            if (!Schema::hasColumn('return_slip_items', 'transferred_to_location_id')) {
                $table->unsignedBigInteger('transferred_to_location_id')->nullable()->after('transferred_to_office_id');
            }

            if (!Schema::hasColumn('return_slip_items', 'actioned_by')) {
                $table->unsignedBigInteger('actioned_by')->nullable()->after('transferred_to_location_id');
            }

            if (!Schema::hasColumn('return_slip_items', 'actioned_at')) {
                $table->timestamp('actioned_at')->nullable()->after('actioned_by');
            }
        });

        DB::statement("ALTER TABLE `return_slip_items` MODIFY `status` VARCHAR(50) NOT NULL DEFAULT 'Returned'");

        // Legacy rows: awaiting-action records become "Returned", already-actioned
        // unserviceable confirmations keep their final disposition.
        DB::table('return_slip_items')->whereIn('status', ['Pending', ''])->orWhereNull('status')
            ->update(['status' => 'Returned']);
        DB::table('return_slips')->whereIn('status', ['Pending', ''])->orWhereNull('status')
            ->update(['status' => 'Returned']);
        DB::table('return_slips')->whereNull('returned_by_name')
            ->update(['returned_by_name' => DB::raw('`requested_by`')]);
        DB::table('return_slips')->whereNull('returned_at')
            ->update(['returned_at' => DB::raw('`created_at`')]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('return_slips', function (Blueprint $table) {
            foreach (['returned_by_id', 'returned_by_name', 'received_by', 'returned_at'] as $column) {
                if (Schema::hasColumn('return_slips', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('return_slip_items', function (Blueprint $table) {
            $columns = [
                'previous_remarks',
                'action_type',
                'transferred_to_enduser_id',
                'transferred_to_enduser_name',
                'transferred_to_office_id',
                'transferred_to_location_id',
                'actioned_by',
                'actioned_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('return_slip_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        DB::statement("ALTER TABLE `return_slips` MODIFY `request_type` ENUM('Unserviceable','Transfer') NOT NULL");
        DB::statement("ALTER TABLE `return_slips` MODIFY `status` VARCHAR(50) NOT NULL DEFAULT 'Pending'");
        DB::statement("ALTER TABLE `return_slip_items` MODIFY `status` VARCHAR(50) NOT NULL DEFAULT 'Pending'");
    }
};
