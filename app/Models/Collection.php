<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Collection extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const DISPLAY_MODE_SLIDER = 'slider';
    public const DISPLAY_MODE_IMAGE_ONLY = 'image_only';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'color_theme',
        'is_active',
        'display_mode',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_mode' => 'string',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'collection_product');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('home_image')
            ->useDisk('public')
            ->singleFile();

        $this->addMediaCollection('cover_image')
            ->useDisk('public')
            ->singleFile();
    }
}
