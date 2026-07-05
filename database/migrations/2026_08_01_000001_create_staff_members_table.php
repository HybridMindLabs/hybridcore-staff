<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_members', function (Blueprint $table) {
            $table->id();
            // Optional link to a site user — pulls their avatar/profile.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');                    // display name (or override for a linked user)
            $table->string('role_label');              // e.g. "Founder", "Head Admin"
            $table->string('group')->nullable();       // section heading, e.g. "Management"
            $table->text('bio')->nullable();
            $table->string('avatar')->nullable();       // used when not linked to a user
            $table->string('color', 20)->nullable();    // accent colour
            $table->string('discord')->nullable();
            $table->string('steam')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_members');
    }
};
