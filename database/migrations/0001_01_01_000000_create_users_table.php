<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('f_name', 50);
            $table->string('l_name', 50)->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->timestamp('last_activity_at')->nullable();

            $table->string('otp')->nullable();
            $table->boolean('is_otp_verified')->default(false);
            $table->timestamp('otp_expires_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();

            $table->string('reset_password_token')->nullable();
            $table->timestamp('reset_password_token_expire_at')->nullable();

            $table->enum('role', ['user', 'dj', 'promoter', 'artist', 'venue', 'admin'])->nullable();

            $table->string('profession', 255)->nullable();
            $table->string('gender', 50)->nullable();
            $table->string('age', 50)->nullable();

            $table->string('address')->nullable();
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('zip_code', 20)->nullable();
            $table->string('latitude', 200)->nullable();
            $table->string('longitude', 200)->nullable();

            $table->boolean('get_notification')->default(value: false);

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
