<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory;
    use SoftDeletes;
    use InteractsWithMedia;

    public const STATUS_READY = 'ready';
    public const STATUS_PRE_ORDER = 'pre_order';
    public const STATUS_INACTIVE = 'inactive';

    public const ITEM_TYPE_PRODUCT = 'product';
    public const ITEM_TYPE_SERVICE = 'service';

    public const VISIBILITY_GLOBAL = 'global';
    public const VISIBILITY_LOCAL = 'local';

    protected $fillable = [
        'store_id',
        'category_id',
        'name',
        'slug',
        'brand',
        'description',
        'price',
        'sale_price',
        'min_order',
        'stock',
        'weight',
        'length',
        'width',
        'height',
        'item_type',
        'status',
        'visibility_scope',
        'location_province_id',
        'location_city_id',
        'location_district_id',
        'location_postal_code',
        'is_pdn',
        'is_pkp',
        'is_tkdn',
    ];

    protected $casts = [
        'store_id' => 'integer',
        'category_id' => 'integer',
        'location_province_id' => 'integer',
        'location_city_id' => 'integer',
        'location_district_id' => 'integer',
        'price' => 'integer',
        'sale_price' => 'integer',
        'min_order' => 'integer',
        'stock' => 'integer',
        'weight' => 'integer',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'visibility_scope' => 'string',
        'is_pdn' => 'boolean',
        'is_pkp' => 'boolean',
        'is_tkdn' => 'boolean',
    ];

    protected $appends = [
        'location_city',
        'location_province',
        'location_district',
        'image_url',
    ];

    public static function statuses(): array
    {
        return [
            ['value' => self::STATUS_READY, 'label' => 'Ready Stock'],
            ['value' => self::STATUS_PRE_ORDER, 'label' => 'Pre Order'],
            ['value' => self::STATUS_INACTIVE, 'label' => 'Tidak Aktif'],
        ];
    }

    public static function itemTypes(): array
    {
        return [
            ['value' => self::ITEM_TYPE_PRODUCT, 'label' => 'Produk Fisik'],
            ['value' => self::ITEM_TYPE_SERVICE, 'label' => 'Jasa / Layanan'],
        ];
    }

    public static function visibilityScopes(): array
    {
        return [
            [
                'value' => self::VISIBILITY_GLOBAL,
                'label' => 'Semua Lokasi',
                'description' => 'Produk tampil untuk semua customer di mana saja.',
            ],
            [
                'value' => self::VISIBILITY_LOCAL,
                'label' => 'Sesuai Lokasi',
                'description' => 'Produk hanya tampil untuk customer dengan kota yang sama dengan toko/produk.',
            ],
        ];
    }

    public static function visibilityScopeValues(): array
    {
        return [
            self::VISIBILITY_GLOBAL,
            self::VISIBILITY_LOCAL,
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function scopeVisibleForCity(Builder $query, ?int $cityId): Builder
    {
        return $query->where(function (Builder $q) use ($cityId) {
            $q->where('visibility_scope', self::VISIBILITY_GLOBAL)
                ->orWhere(function (Builder $localQuery) use ($cityId) {
                    $localQuery->where('visibility_scope', self::VISIBILITY_LOCAL);

                    if ($cityId) {
                        $localQuery->where(function (Builder $matchQuery) use ($cityId) {
                            $matchQuery->where('location_city_id', $cityId)
                                ->orWhereHas('store', fn (Builder $storeQuery) => $storeQuery->where('city_id', $cityId));
                        });
                    } else {
                        $localQuery->whereRaw('1 = 0');
                    }
                });
        });
    }

    public function scopeVisibleForCityAuth(Builder $query, ?int $cityId, bool $isAuthenticated): Builder
    {
        // Jika user belum login, tampilkan semua produk (global + local)
        if (! $isAuthenticated) {
            return $query;
        }

        // Jika user sudah login tapi tidak punya alamat, tampilkan semua produk
        // karena sudah ada flag alert visibility_scope di frontend
        if (! $cityId) {
            return $query;
        }

        // Jika user sudah login dan punya alamat, filter berdasarkan visibility scope dan city
        return $query->where(function (Builder $q) use ($cityId) {
            // Produk global selalu tampil
            $q->where('visibility_scope', self::VISIBILITY_GLOBAL)
                // Produk local tampil jika city cocok
                ->orWhere(function (Builder $localQuery) use ($cityId) {
                    $localQuery->where('visibility_scope', self::VISIBILITY_LOCAL)
                        ->where(function (Builder $matchQuery) use ($cityId) {
                            $matchQuery->where('location_city_id', $cityId)
                                ->orWhereHas('store', fn (Builder $storeQuery) => $storeQuery->where('city_id', $cityId));
                        });
                });
        });
    }

    public function isVisibleForCity(?int $cityId): bool
    {
        if ($this->visibility_scope === self::VISIBILITY_GLOBAL) {
            return true;
        }

        if (! $cityId) {
            return false;
        }

        $productCity = $this->location_city_id;
        $storeCity = $this->store?->city_id;

        return (int) $productCity === (int) $cityId || (int) $storeCity === (int) $cityId;
    }

    public function isVisibleForCityAuth(?int $cityId, bool $isAuthenticated): bool
    {
        // Jika user belum login, tampilkan semua produk (global + local)
        if (! $isAuthenticated) {
            return true;
        }

        // Produk global selalu tampil
        if ($this->visibility_scope === self::VISIBILITY_GLOBAL) {
            return true;
        }

        // Jika user login tapi tidak punya alamat, tetap tampilkan
        // karena sudah ada flag alert visibility_scope di frontend
        if (! $cityId) {
            return true;
        }

        // Produk local tampil jika city cocok
        $productCity = $this->location_city_id;
        $storeCity = $this->store?->city_id;

        return (int) $productCity === (int) $cityId || (int) $storeCity === (int) $cityId;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function locationProvince(): BelongsTo
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\Province::class, 'location_province_id');
    }

    public function locationCity(): BelongsTo
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\City::class, 'location_city_id');
    }

    public function locationDistrict(): BelongsTo
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\District::class, 'location_district_id');
    }

    public function getLocationProvinceAttribute(): ?string
    {
        return $this->getRelationValue('locationProvince')?->name;
    }

    public function getLocationCityAttribute(): ?string
    {
        return $this->getRelationValue('locationCity')?->name;
    }

    public function getLocationDistrictAttribute(): ?string
    {
        return $this->getRelationValue('locationDistrict')?->name;
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->normalizeMediaUrl($this->getFirstMediaUrl('product_image')) ?: null;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('product_image')
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->withResponsiveImages();
    }

    public function normalizeMediaUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $parts = parse_url($url);

        if ($parts === false) {
            return $url;
        }

        $path = ($parts['path'] ?? '').
            (isset($parts['query']) ? '?'.$parts['query'] : '');

        return $path ?: $url;
    }

    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        $normalized = trim($term);
        $slugTerm = Str::slug($normalized);
        $wildcardName = '%'.str_replace(' ', '%', $normalized).'%';
        $wildcardSlug = '%'.str_replace('-', '%', $slugTerm).'%';

        return $query->where(function ($q) use ($wildcardName, $wildcardSlug) {
            $q->where('name', 'like', $wildcardName)
                ->orWhere('brand', 'like', $wildcardName)
                ->orWhere('slug', 'like', $wildcardSlug);
        });
    }
}
