<?php

use App\Models\Work;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_items', function (Blueprint $table) {
            $table->id();
            $table->string('mediable_type');
            $table->unsignedBigInteger('mediable_id');
            $table->string('type')->default('image');
            $table->string('youtube_url')->nullable();
            $table->string('caption')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->index(['mediable_type', 'mediable_id', 'order']);
        });

        // Carry over any existing rows from the Work-only table.
        if (Schema::hasTable('work_media')) {
            DB::table('work_media')->orderBy('id')->each(function (object $row) {
                DB::table('media_items')->insert([
                    'id' => $row->id,
                    'mediable_type' => Work::class,
                    'mediable_id' => $row->work_id,
                    'type' => $row->type,
                    'youtube_url' => $row->youtube_url,
                    'caption' => $row->caption,
                    'order' => $row->order,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            });

            Schema::drop('work_media');
        }

        // medialibrary rows point at the old model class — repoint them, or every
        // uploaded file silently detaches from its (renamed) owner.
        DB::table('media')
            ->where('model_type', 'App\\Models\\WorkMedia')
            ->update(['model_type' => 'App\\Models\\MediaItem']);
    }

    public function down(): void
    {
        // Only Work-owned rows are being copied back into work_media below —
        // repointing every MediaItem, including Industry-owned rows that get
        // discarded, would detach their uploaded files from their owner.
        $workMediaItemIds = DB::table('media_items')->where('mediable_type', Work::class)->pluck('id');

        DB::table('media')
            ->where('model_type', 'App\\Models\\MediaItem')
            ->whereIn('model_id', $workMediaItemIds)
            ->update(['model_type' => 'App\\Models\\WorkMedia']);

        Schema::create('work_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('image');
            $table->string('youtube_url')->nullable();
            $table->string('caption')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        DB::table('media_items')->where('mediable_type', Work::class)->orderBy('id')
            ->each(function (object $row) {
                DB::table('work_media')->insert([
                    'id' => $row->id,
                    'work_id' => $row->mediable_id,
                    'type' => $row->type,
                    'youtube_url' => $row->youtube_url,
                    'caption' => $row->caption,
                    'order' => $row->order,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            });

        Schema::dropIfExists('media_items');
    }
};
