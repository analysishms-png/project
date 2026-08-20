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
        Schema::create('meta_tags', function (Blueprint $table) {
            $table->id();
            $table->string('page_name')->unique(); // about, home, contact, pricing, services etc
            $table->string('title')->nullable(); // Page Title
            $table->text('description')->nullable(); // Meta Description
            $table->text('keywords')->nullable(); // Meta Keywords
            $table->string('author')->nullable(); // Meta Author
            $table->string('robots')->default('index, follow'); // Meta Robots
            $table->string('canonical_url')->nullable(); // Canonical URL
            $table->string('theme_color')->nullable(); // Theme Color
            
            // OpenGraph Tags
            $table->string('og_type')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_url')->nullable();
            $table->string('og_site_name')->nullable();
            $table->string('og_image')->nullable();
            $table->string('og_locale')->nullable();
            
            // Twitter Tags
            $table->string('twitter_card')->nullable();
            $table->string('twitter_title')->nullable();
            $table->text('twitter_description')->nullable();
            $table->string('twitter_image')->nullable();
            $table->string('twitter_site')->nullable();
            
            // Schema.org JSON-LD
            $table->longText('schema_json')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meta_tags');
    }
};
