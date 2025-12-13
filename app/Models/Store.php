<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'is_verified',
        'is_umkm',
        'rating',
        'transactions_count',
        'response_time_label',
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
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
        $this->addMediaCollection('owner_id_card')->useDisk('public')->singleFile();
        $this->addMediaCollection('nib_document')->useDisk('public')->singleFile();
        $this->addMediaCollection('npwp_document')->useDisk('public')->singleFile();
        $this->addMediaCollection('business_license')->useDisk('public')->singleFile();
        $this->addMediaCollection('pkp_document')->useDisk('public')->singleFile();
    }
}
