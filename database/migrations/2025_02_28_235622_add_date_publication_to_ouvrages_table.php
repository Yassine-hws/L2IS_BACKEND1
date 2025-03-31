<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDatePublicationToOuvragesTable extends Migration
{
    public function up()
    {
        Schema::table('ouvrages', function (Blueprint $table) {
            if (!Schema::hasColumn('ouvrages', 'date_publication')) { 
                $table->date('date_publication')->nullable()->after('title'); 
            }
        });
    }

    public function down()
    {
        Schema::table('ouvrages', function (Blueprint $table) {
            if (Schema::hasColumn('ouvrages', 'date_publication')) { 
                $table->dropColumn('date_publication');
            }
        });
    }
}
