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
        Schema::dropIfExists('model_user_roles');
        Schema::create('model_user_roles', function (Blueprint $table) {
            $table->id();
            $table->string('model_type', 191);
            $table->integer('model_id');
            $table->integer('user_id');
            $table->string('role', 191);
            $table->timestamps();

            $table->index(['model_type', 'model_id', 'role'], 'model_role_key');
            $table->index(['user_id', 'role', 'model_type'], 'user_role_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('model_user_roles');
    }
};
