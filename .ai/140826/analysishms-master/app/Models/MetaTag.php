<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetaTag extends Model
{
    protected $table = 'meta_tags';
    
    protected $fillable = [
        'page_name',
        'title',
        'description',
        'keywords',
        'author',
        'robots',
        'canonical_url',
        'theme_color',
        'og_type',
        'og_title',
        'og_description',
        'og_url',
        'og_site_name',
        'og_image',
        'og_locale',
        'twitter_card',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'twitter_site',
        'schema_json'
    ];

    protected $casts = [
        'schema_json' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Get meta by page name
    public static function getByPageName($pageName)
    {
        return self::where('page_name', $pageName)->first();
    }

    // Get all pages
    public static function getAllPages()
    {
        return self::orderBy('page_name')->get();
    }
}
