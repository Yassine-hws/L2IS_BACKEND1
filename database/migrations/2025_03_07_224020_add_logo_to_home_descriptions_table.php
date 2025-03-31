<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLogoToHomeDescriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('home_descriptions', function (Blueprint $table) {
            $table->string('logo')->nullable();  // Ajout de la colonne logo après la colonne image
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
            $table->dropColumn('logo');
        });
    }
}
