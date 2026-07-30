<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        if (Schema::hasTable('return_slip_logs')) {
            return;
        }

        Schema::create('return_slip_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('return_slip_id')->nullable()->index();
            $table->unsignedBigInteger('return_slip_item_id')->nullable()->index();
            $table->unsignedBigInteger('enduser_property_id')->nullable()->index();
            $table->string('property_no_generated')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_role', 100)->nullable();
            $table->string('action', 100);
            $table->text('description')->nullable();
            $table->string('from_value')->nullable();
            $table->string('to_value')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('return_slip_logs');
    }
};
