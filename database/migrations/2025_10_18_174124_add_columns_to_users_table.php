<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string("username")->nullable();
            $table->string("profile_picture")->nullable();
            $table->enum('role', ["admin", 'author', 'user'])->default("user");
            $table->integer('phone_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn(['first_name', 'last_name', 'username', 'profile_picture', 'role', 'phone_number']);
        });
    }
};
