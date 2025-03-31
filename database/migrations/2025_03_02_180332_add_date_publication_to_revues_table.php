<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDatePublicationToRevuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('revues', function (Blueprint $table) {
            $table->date('date_publication')->nullable(); // Ajout de la colonne
        });
    }

    public function down(): void
    {
        Schema::table('revues', function (Blueprint $table) {
            $table->dropColumn('date_publication'); // Suppression en cas de rollback
        });
    }
}
