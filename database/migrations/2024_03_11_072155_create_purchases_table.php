<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->nullable();
            $table->foreignId('categories_id')->nullable();
            $table->foreignId('properties_id')->nullable();
            $table->foreignId('office_id')->nullable();
            $table->foreignId('item_id')->nullable();
            $table->string('item_descrip')->nullable();
            $table->string('item_model')->nullable();
            $table->text('description')->nullable();
            $table->string('item_number')->nullable();
            $table->string('serial_number')->nullable();
            $table->foreignId('unit_id')->nullable();
            $table->decimal('item_cost', 10, 2)->nullable();
            $table->integer('qty')->nullable();
            $table->decimal('total_cost', 10, 2)->nullable();
            $table->string('property_no_generated')->nullable();
            $table->foreignId('selected_account_id')->nullable();
            $table->string('status')->nullable();
            $table->text('remarks')->nullable();
            $table->date('date_acquired')->nullable();
            $table->date('date_stat')->nullable();
            $table->string('price_stat')->nullable();
            $table->string('person_accnt')->nullable();
            $table->string('person_accnt_name')->nullable();
            $table->string('print_stat')->nullable();
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
        Schema::dropIfExists('purchases');
    }
}
