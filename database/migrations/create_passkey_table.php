<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
       {
           Schema::create('passkeys', function (Blueprint $table) {
               $table->id();
               $table->foreignId('user_id')->constrained()->onDelete('cascade');
               $table->string('device_name');
               $table->string('credential_id')->unique();
               $table->boolean('is_enabled')->default(true);
               $table->json('data');
               $table->timestamps();
           });
       }

       public function down(): void
       {
           Schema::dropIfExists('passkeys');
       }
};
