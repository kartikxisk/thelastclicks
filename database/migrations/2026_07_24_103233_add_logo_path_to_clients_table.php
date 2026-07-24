<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A logo that lives somewhere other than the media library: a bundled file under
 * public/ or an absolute URL. Lets the strip be managed from the admin without
 * depending on an S3 upload, and gives every client a working logo today.
 * An uploaded logo always wins over this.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('url');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('logo_path');
        });
    }
};
