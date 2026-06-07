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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('location');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('status')->default('baru'); // baru, diverifikasi, diproses, ditangani, selesai, ditolak
            $table->timestamp('reported_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->nullable()->constrained('reports')->onDelete('set null');
            $table->string('title');
            $table->text('description');
            $table->string('category'); // pencurian, kebakaran, kehilangan, keributan, bencana alam, lainnya
            $table->string('location');
            $table->timestamp('incident_date');
            $table->string('severity')->default('sedang'); // rendah, sedang, tinggi
            $table->string('status')->default('baru'); // baru, diverifikasi, diproses, ditangani, selesai, ditolak
            $table->timestamps();
        });

        Schema::create('handling_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('incidents')->onDelete('cascade');
            $table->foreignId('handler_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('action_taken');
            $table->text('result')->nullable();
            $table->timestamp('handled_at')->useCurrent();
            $table->string('status_after')->default('diproses');
            $table->timestamps();
        });

        Schema::create('patrol_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // satpam
            $table->string('shift'); // pagi, siang, malam
            $table->time('start_time');
            $table->time('end_time');
            $table->date('patrol_date');
            $table->string('area');
            $table->text('notes')->nullable();
            $table->string('status')->default('scheduled'); // scheduled, completed, missed
            $table->timestamps();
        });

        Schema::create('patrol_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patrol_schedule_id')->constrained('patrol_schedules')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('logged_at')->useCurrent();
            $table->string('location_checked');
            $table->string('condition'); // aman, mencurigakan, bahaya
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->string('attachable_type');
            $table->unsignedBigInteger('attachable_id');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->timestamps();
            $table->index(['attachable_type', 'attachable_id']);
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->string('link')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('activity');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('patrol_logs');
        Schema::dropIfExists('patrol_schedules');
        Schema::dropIfExists('handling_records');
        Schema::dropIfExists('incidents');
        Schema::dropIfExists('reports');
    }
};
