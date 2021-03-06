<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePagesRelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages_relations', function (Blueprint $table) {
            $table->integer('page_id')
                ->unsigned()
                ->references('id')
                ->on('pages')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->integer('related_page_id')
                ->unsigned()
                ->references('id')
                ->on('pages')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table
                ->integer('created_by')
                ->unsigned()
                ->references('id')
                ->on('people')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table
                ->integer('created_at')
                ->unsigned()
                ->nullable();

            $table->primary(['page_id', 'related_page_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('pages_relations');
    }
}
