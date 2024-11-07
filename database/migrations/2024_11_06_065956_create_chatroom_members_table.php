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
        Schema::create('chatroom_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chatroom_id')->constrained('chatrooms');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamp('joined_at')->useCurrent();
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
        Schema::dropIfExists('chatroom_members');
    }
};
