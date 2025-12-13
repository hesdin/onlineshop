<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('superadmin') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $productId = $this->route('product')?->id;
        $isUpdate = (bool) $productId;
        $storeId = $this->input('store_id') ?: $this->route('product')?->store_id;
        $provinceTable = config('laravolt.indonesia.table_prefix').'provinces';
        $cityTable = config('laravolt.indonesia.table_prefix').'cities';
        $districtTable = config('laravolt.indonesia.table_prefix').'districts';
        $visibilityValues = Product::visibilityScopeValues();

        return [
            'store_id' => ['required', 'integer', Rule::exists('stores', 'id')],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'slug')
                    ->ignore($productId)
                    ->where(fn ($query) => $storeId ? $query->where('store_id', $storeId) : $query)
                    ->whereNull('deleted_at'), // abaikan produk yang sudah soft delete
            ],
            'brand' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'integer', 'min:0'],
            'sale_price' => ['nullable', 'integer', 'min:0', 'lte:price'],
            'min_order' => ['required', 'integer', 'min:1'],
            'stock' => ['required', 'integer', 'min:0'],
            'weight' => ['required', 'integer', 'min:0'],
            'length' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'item_type' => ['required', Rule::in([
                Product::ITEM_TYPE_PRODUCT,
                Product::ITEM_TYPE_SERVICE,
            ])],
            'status' => ['required', Rule::in([
                Product::STATUS_READY,
                Product::STATUS_PRE_ORDER,
                Product::STATUS_INACTIVE,
            ])],
            'visibility_scope' => ['nullable', Rule::in($visibilityValues)],
            'location_province_id' => ['nullable', 'integer', Rule::exists($provinceTable, 'id')],
            'location_city_id' => ['nullable', 'integer', Rule::exists($cityTable, 'id')],
            'location_district_id' => ['nullable', 'integer', Rule::exists($districtTable, 'id')],
            'location_postal_code' => ['nullable', 'string', 'max:10'],
            'is_pdn' => ['sometimes', 'boolean'],
            'is_pkp' => ['sometimes', 'boolean'],
            'is_tkdn' => ['sometimes', 'boolean'],
            'images' => [$isUpdate ? 'sometimes' : 'required', 'array', 'min:1'],
            'images.*' => ['file', 'image', 'max:5120'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (empty($data['slug']) && ! empty($data['name'])) {
            $data['slug'] = str()->slug($data['name']);
        }

        $nullableFields = [
            'category_id',
            'sale_price',
            'length',
            'width',
            'height',
            'description',
            'location_province_id',
            'location_city_id',
            'location_district_id',
            'location_postal_code',
        ];

        foreach ($nullableFields as $field) {
            if (array_key_exists($field, $data) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        $integerFields = [
            'store_id',
            'category_id',
            'price',
            'sale_price',
            'min_order',
            'stock',
            'weight',
            'location_province_id',
            'location_city_id',
            'location_district_id',
        ];

        foreach ($integerFields as $field) {
            if (array_key_exists($field, $data) && $data[$field] !== null) {
                $data[$field] = (int) $data[$field];
            }
        }

        $decimalFields = ['length', 'width', 'height'];

        foreach ($decimalFields as $field) {
            if (array_key_exists($field, $data) && $data[$field] !== null) {
                $data[$field] = (float) $data[$field];
            }
        }

        $booleanFields = ['is_pdn', 'is_pkp', 'is_tkdn'];

        foreach ($booleanFields as $field) {
            $data[$field] = (bool) ($data[$field] ?? false);
        }

        $data['visibility_scope'] = $data['visibility_scope'] ?? Product::VISIBILITY_GLOBAL;

        return $data;
    }
}
