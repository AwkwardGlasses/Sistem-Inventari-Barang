<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPositionForeignToEmployee extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->unsignedBigInteger('position_id');
            $table->foreign('position_id')->references('id')->on('positions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            // $table->dropForeign('employees_position_id_foreign');
            $table->dropForeign(['position_id']);
            $table->dropColumn('position_id');
        });
    }
}
