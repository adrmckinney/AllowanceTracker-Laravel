<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_chore', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table
                ->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->unsignedBigInteger('chore_id');
            $table
                ->foreign('chore_id')
                ->references('id')
                ->on('chores')
                ->onDelete('cascade');

            $table->boolean('approval_requested')->default(0);
            $table->timestamp('approval_request_date')->nullable();
            $table->integer('approval_status')->default(0);
            $table->timestamp('approval_date')->nullable();


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
        Schema::table('user_chore', function (Blueprint $table) {
            $table->dropForeign('user_id');
            $table->dropForeign('chore_id');
        });

        Schema::dropIfExists('user_chore');
    }
};
