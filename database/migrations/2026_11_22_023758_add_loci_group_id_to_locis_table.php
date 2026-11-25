<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLociGroupIdToLocisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('locis', function (Blueprint $table) {
            $table->dropColumn('chromosome');
            $table->foreignId('loci_group_id')->nullable()->constrained()->before('sort');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('locis', function (Blueprint $table) {
            $table->unsignedTinyInteger('chromosome')->nullable()->default(null);
            $table->dropForeign('locis_loci_group_id_foreign');
        });
    }
}
