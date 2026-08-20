<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the notifications table — in-app alerts for the Gym Owner.
     * Uses polymorphic morph columns (notifiable_type / notifiable_id)
     * so it can store notifications about Members, Trainers, or any future entity.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            // Notification type — e.g. 'membership_expiry', 'pending_fee', 'renewal_reminder'
            $table->string('type', 100);

            // Polymorphic morph — who is this notification about?
            $table->string('notifiable_type', 100);
            $table->unsignedBigInteger('notifiable_id');

            $table->string('title', 150);
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['notifiable_type', 'notifiable_id'], 'notif_morph_index');
            $table->index('is_read', 'notif_is_read_index'); // for unread count badge
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
