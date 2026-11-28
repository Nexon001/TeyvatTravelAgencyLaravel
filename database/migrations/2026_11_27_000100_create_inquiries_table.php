<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // INQUIRIES in the concept paper: messages sent through the contact form (FR-30, FR-31, FR-43).
        // Differences from the paper: no customer_id yet (the CUSTOMERS table does not exist until the booking
        // model is decided), and consented_at is added as proof of the Data Privacy Act consent (NFR-03).
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nation_id')->nullable()->constrained('nations')->nullOnDelete();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 100);
            $table->string('email', 100);
            $table->string('subject', 150)->nullable();
            $table->text('message');
            $table->string('status', 30)->default('pending'); // pending | in progress | resolved
            $table->timestamp('consented_at');
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
