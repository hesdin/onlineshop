<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Banner extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'title',
        'alt_text',
        'url',
        'type',
        'position',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'position' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }

    public function scopeHeroSlider(Builder $query): Builder
    {
        return $query->where('type', 'hero_slider');
    }

    public function scopeHeroPromo(Builder $query): Builder
    {
        return $query->where('type', 'hero_promo');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderByDesc('created_at');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('banner_image')
            ->useDisk('public')
            ->singleFile();
    }

    public function getImageUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('banner_image');

        if (! $url) {
            return null;
        }

        return parse_url($url, PHP_URL_PATH) ?: $url;
    }
}
