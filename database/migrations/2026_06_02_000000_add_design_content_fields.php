<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crew', function (Blueprint $table) {
            $table->string('tagline')->nullable()->after('role');
            $table->string('joined')->nullable()->after('tagline');
            $table->string('discipline')->nullable()->after('joined');
            $table->string('city')->nullable()->after('discipline');
            $table->json('skills')->nullable()->after('bio');
            $table->json('credits')->nullable()->after('skills');
            $table->string('photo_url')->nullable()->after('credits');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->string('hero_headline')->nullable()->after('hero_copy');
            $table->json('hero_meta')->nullable()->after('hero_headline');
            $table->json('proof')->nullable()->after('hero_meta');
            $table->json('pillars')->nullable()->after('proof');
            $table->json('phases')->nullable()->after('pillars');
            $table->json('kit')->nullable()->after('phases');
            $table->json('faqs')->nullable()->after('kit');
            $table->json('cta')->nullable()->after('faqs');
            $table->json('tags')->nullable()->after('cta');
            $table->json('gallery_urls')->nullable()->after('tags');
            $table->string('hero_url')->nullable()->after('gallery_urls');
            $table->string('featured_slug')->nullable()->after('hero_url');
        });

        Schema::table('industries', function (Blueprint $table) {
            $table->string('image_url')->nullable()->after('summary');
        });

        Schema::table('portfolios', function (Blueprint $table) {
            $table->string('location')->nullable()->after('client');
            $table->longText('approach')->nullable()->after('body');
            $table->json('credits')->nullable()->after('approach');
            $table->string('cover_url')->nullable()->after('credits');
            $table->json('gallery_urls')->nullable()->after('cover_url');
            $table->string('hero_html')->nullable()->after('gallery_urls');
        });
    }

    public function down(): void
    {
        Schema::table('crew', function (Blueprint $table) {
            $table->dropColumn(['tagline', 'joined', 'discipline', 'city', 'skills', 'credits', 'photo_url']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['hero_headline', 'hero_meta', 'proof', 'pillars', 'phases', 'kit', 'faqs', 'cta', 'tags', 'gallery_urls', 'hero_url', 'featured_slug']);
        });

        Schema::table('portfolios', function (Blueprint $table) {
            $table->dropColumn(['location', 'approach', 'credits', 'cover_url', 'gallery_urls', 'hero_html']);
        });

        Schema::table('industries', function (Blueprint $table) {
            $table->dropColumn('image_url');
        });
    }
};
