<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageToHomeDescriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('home_descriptions', function (Blueprint $table) {
            $table->string('image')->nullable(); // Add 'image' column, nullable for optional
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('home_descriptions', function (Blueprint $table) {
            $table->dropColumn('image'); // Drop 'image' column if rolling back
        });
    }
}
