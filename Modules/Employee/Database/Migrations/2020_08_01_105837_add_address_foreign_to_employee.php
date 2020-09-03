<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAddressForeignToEmployee extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('employees', function (Blueprint $table) {
            $table->unsignedBigInteger('address_id')->nullable()->after('phone_number');
            $table->foreign('address_id')->references('id')->on('addresses')->onDelete('cascade');
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            // $table->dropForeign(['address_id']);
            $table->dropColumn('address_id');
        });
    }
}
