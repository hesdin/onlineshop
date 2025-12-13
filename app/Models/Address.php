<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'label',
        'recipient_name',
        'phone',
        'province_id',
        'city_id',
        'district_id',
        'postal_code',
        'address_line',
        'latitude',
        'longitude',
        'is_default',
        'note',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    protected $appends = [
        'province',
        'city',
        'district',
    ];

    public function provinceRegion()
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\Province::class, 'province_id');
    }

    public function cityRegion()
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\City::class, 'city_id');
    }

    public function districtRegion()
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\District::class, 'district_id');
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
}
