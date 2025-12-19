<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Province;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Store extends Model implements HasMedia
{
    use HasFactory;
    use SoftDeletes;
    use InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'tagline',
        'description',
        'type',
        'tax_status',
        'bumn_partner',
        'province_id',
        'city_id',
        'district_id',
        'postal_code',
        'address_line',
        'phone',
        'is_verified',
        'is_umkm',
        'rating',
        'transactions_count',
        'response_time_label',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_umkm' => 'boolean',
        'rating' => 'decimal:2',
        'province_id' => 'integer',
        'city_id' => 'integer',
        'district_id' => 'integer',
    ];

    protected $appends = [
        'city',
        'province',
        'district',
        'logo_url',
        'banner_url',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sellerDocument(): HasOne
    {
        return $this->hasOne(SellerDocument::class);
    }

    public function provinceRegion(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function cityRegion(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function districtRegion(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function getProvinceAttribute(): ?string
    {
        return $this->getRelationValue('provinceRegion')?->name;
    }

    public function getCityAttribute(): ?string
    {
        return $this->getRelationValue('cityRegion')?->name;
    }

    public function getDistrictAttribute(): ?string
    {
        return $this->getRelationValue('districtRegion')?->name;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('store_logo')
            ->useDisk('public')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('store_banner')
            ->useDisk('public')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('owner_id_card')->useDisk('public')->singleFile();
        $this->addMediaCollection('nib_document')->useDisk('public')->singleFile();
        $this->addMediaCollection('npwp_document')->useDisk('public')->singleFile();
        $this->addMediaCollection('business_license')->useDisk('public')->singleFile();
        $this->addMediaCollection('pkp_document')->useDisk('public')->singleFile();
    }

    public function getLogoUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('store_logo');
        if (!$media) {
            return null;
        }
        return '/storage/' . $media->id . '/' . $media->file_name;
    }

    public function getBannerUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('store_banner');
        if (!$media) {
            return null;
        }
        return '/storage/' . $media->id . '/' . $media->file_name;
    }
}
