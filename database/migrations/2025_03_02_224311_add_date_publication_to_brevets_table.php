<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDatePublicationToBrevetsTable extends Migration
{
   public function up(): void
    {
        Schema::table('brevets', function (Blueprint $table) {
            $table->date('date_publication')->nullable(); // Ajoute la colonne
        });
    }

    public function down(): void
    {
        Schema::table('brevets', function (Blueprint $table) {
            $table->dropColumn('date_publication'); // Supprime la colonne si rollback
        });
    }
}
